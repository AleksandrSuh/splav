<?php

class CAction extends CCoreObject {

	private $A;
	private $ActionsDoc;
	private $ActionsXPath;

	public function __construct(&$A) {
		$this->A =& $A;
        }

	public function init() {
		parent::init();
		$this->loadActionsDoc();
        }

	private function loadActionsDoc() {
		$this->ActionsDoc = DOMDocument::load(DIR_ROOT.'/cms/actions.xml');
		$this->ActionsXPath = new DOMXPath($this->ActionsDoc);
        }

	public function check() {
		$r = $this->ActionsXPath->query("//*[@name='{$this->A['action']}']");
		if (!$r || $r->length==0) return false;
		$e = $r->item(0);
		if (!$this->USER->checkRights($e->getAttribute('rights'))) return false;
		if ($e->hasAttribute('required')) {
			$buf = explode(',', $e->getAttribute('required'));
			foreach ($buf as $v) if (!isset($this->A[$v])) return false;
		}
		return true;
        }

	public function exec($Action = null) {
		if (is_null($Action)) $Action = $this->A['action'];
		switch ($Action) {
			case 'save': case 'saveAndPublish':
				if ((!isset($this->A['id']) || $this->A['id']=='') 
					&& (!isset($this->A['vars']['title']) || $this->A['vars']['title']==''))
					return false;
				if (isset($this->A['vars']['title']) && $this->A['vars']['title']!=''
					&& isset($this->A['titleToLink']) && $this->A['titleToLink']) {
					if (isset($this->A['id']) && $this->A['id']!='') {
						$vars = $this->DBC->getVarsById($this->A['id'], array(
								'vars'=>array(), 'external'=>array(),
								'parent'=>array('id')
							));
						if (isset($vars['parent.id'])) {
							$parentVars = $this->DBC->getVarsById($vars['parent.id']);
							$parentLink = $parentVars['link'];
						} else $parentLink = '';
					} else if (isset($this->A['parent_id']) && $this->A['parent_id']!='') {
						$parentVars = $this->DBC->getVarsById($this->A['parent_id']);
						$parentLink = $parentVars['link'];
					} else $parentLink = '';
					$this->A['vars']['link'] = $parentLink.($parentLink=='/' ? '' : '/')
						.self::transliterate($this->A['vars']['title']);
				}
				if (isset($this->A['vars']['link'])) {
					$this->checkLink();
					
					if (preg_match('/^(http\:\/\/'.$_SERVER['HTTP_HOST'].')?\/cms/',
						$_SERVER['HTTP_REFERER']))
						$_SERVER['HTTP_REFERER'] = $this->A['vars']['link'];
                                }
				if (!isset($this->A['id']) || $this->A['id']=='') {
					$this->A['vars']['owner_id'] = $this->USER->Vars['user.id'];
					if (!isset($this->A['vars']['n'])) 
						$this->A['vars']['n'] = $this->DBC->getMaxN(isset($this->A['parent_id'])
							? $this->A['parent_id'] : '');
				}
				if (isset($this->A['parent_id']) && $this->A['parent_id']=='DEFAULT')
					$this->A['parent_id'] = DEFAULT_PARENT_ID;
				if (isset($this->A['vars']['create_time']) && $this->A['vars']['create_time']=='NOW')
					$this->A['vars']['create_time'] = date('Y-m-d H:i:s');

				if (isset($this->A['vars']['title']))
					$this->A['vars']['title'] = strip_tags($this->A['vars']['title']);

				if (isset($this->A['vars']['create_time']))
					$this->A['vars']['create_time'] = date('Y-m-d H:i:s',
						strtotime($this->A['vars']['create_time']));
				
				foreach ($this->A['vars'] as $i=>$v) {
					if (get_magic_quotes_gpc()) $v = stripslashes($v);
					$this->A['vars'][$i] = HtmlToXml($v);
                                }

				if (isset($this->A['params'])) {
					if (preg_match('/set_parent_id\=(\S+)/', $this->A['params'], $matches))
						$this->A['parent_id'] = $matches[1];

					if (preg_match('/set_external_class\=(\S+)/', $this->A['params'], $matches))
						$this->A['vars']['external_class'] = $matches[1];

					if (preg_match('/set_childrens_template_src\=(\S+)/', $this->A['params'], $matches))
						$this->A['vars']['childrens_template_src'] = $matches[1];
				}


				$old = $this->DBC->usePublicVersion(false);
				$this->DBC->save($this->A);
				$this->DBC->usePublicVersion($old);


				if (isset($this->A['params'])) {
					preg_match_all('/add_dassoc\=(\S+)/', $this->A['params'], $matches);
					for ($i=0; $i<count($matches[0]); $i++) {
						$v = array('id'=>$this->A['id'], 'foreign_id'=>$matches[1][$i]);
						$this->DBC->addAssoc($v);
					}
					preg_match_all('/add_rassoc\=(\S+)/', $this->A['params'], $matches);
					for ($i=0; $i<count($matches[0]); $i++) {
						$v = array('id'=>$matches[1][$i], 'foreign_id'=>$this->A['id']);
						$this->DBC->addAssoc($v);
					}
					preg_match_all('/remove_dassoc\=(\S+)/', $this->A['params'], $matches);
					for ($i=0; $i<count($matches[0]); $i++) {
						$v = array('id'=>$this->A['id'], 'foreign_id'=>$matches[1][$i]);
						$this->DBC->removeAssoc($v);
					}
					preg_match_all('/remove_rassoc\=(\S+)/', $this->A['params'], $matches);
					for ($i=0; $i<count($matches[0]); $i++) {
						$v = array('id'=>$matches[1][$i], 'foreign_id'=>$this->A['id']);
						$this->DBC->removeAssoc($v);
					}
				}

				if ($Action=='saveAndPublish') {
					$this->CORE->cache_clear();
					$this->exec('publish');
                                }
				break;
			case 'publish':
				$old = $this->DBC->usePublicVersion(false);
				$this->DBC->publish($this->A);
				$this->DBC->usePublicVersion($old);
				break;
			case 'hide':
				$old = $this->DBC->usePublicVersion(false);
				$this->DBC->hide($this->A);
				$this->DBC->usePublicVersion($old);
				break;
			case 'delete': $_SERVER['HTTP_REFERER'] = $this->DBC->delete($this->A); break;
			case 'moveTo': $this->DBC->moveTo($this->A); break;
			case 'moveUp': $this->DBC->moveUp($this->A); break;
			case 'moveDown': $this->DBC->moveDown($this->A); break;
			case 'moveToFirst': $this->DBC->moveToFirst($this->A); break;
			case 'moveToLast': $this->DBC->moveToLast($this->A); break;
			case 'backup': $_SERVER['HTTP_REFERER'] = $this->DBC->backup($this->A); break;
			case 'addAssoc': $this->DBC->addAssoc($this->A); break;
			case 'removeAssoc': $this->DBC->removeAssoc($this->A); break;
			
			case 'userSave':
				if ((!isset($this->A['id']) || $this->A['id']=='') 
					&& (!isset($this->A['name']) || $this->A['name']=='')) return false;
				if (isset($this->A['name']) && $this->A['name']!=''
					&& !$this->DBC->checkUserName(isset($this->A['id']) ? $this->A['id'] : '',
						$this->A['name'])) return false;
				$this->DBC->userSave($this->A);
				if (USER_PARENT_ID!='') {
					if (isset($this->A['id']) && $this->A['id']!='') {
						$d = new DOMDocument();
						$e = $d->createElement('temp');
						list($r, $f, $l, $c) =
							$this->DBC->getUsers($this->A['id'], '', '', '', '', $e);
						for ($i=$f; $i<=$l && $c>0; $i++) {
							$vars = $this->DBC->getRow($r, $i);
							$this->A['id'] = $vars['id'];
						}
					} else $this->A['parent_id'] = USER_PARENT_ID;
					if (isset($this->A['name']) && $this->A['name']!='') {
						$this->A['vars']['title'] = $this->A['name'];
						if (empty($this->A['vars']['link']))
							$this->A['vars']['link'] = '/users/'.$this->A['name'];
					}
					
					$buf = preg_match('/\/cms\/rights$/', $_SERVER['HTTP_REFERER']);
					$this->exec('saveAndPublish');
					if ($buf) $_SERVER['HTTP_REFERER'] = '/cms/rights';
				}
				$_SESSION['userdata'] = NULL;
				break;
			case 'userDelete': $this->DBC->userDelete($this->A); break;
			case 'groupSave': $this->DBC->groupSave($this->A); break;
			case 'groupDelete': $this->DBC->groupDelete($this->A); break;
			case 'rightsSave': $this->DBC->rightsSave($this->A); break;
			case 'rightsDelete': $this->DBC->rightsDelete($this->A); break;
				
			case 'saveToChilds': $this->execToChilds('save'); break;
			case 'publishChilds': $this->execToChilds('publish'); break;
			case 'deleteChilds': $this->execToChilds('delete'); break;
			
			case 'publishAll': $this->execToAll('publish'); break;
			
			case 'dbcClearCache': $this->CORE->cache_clear(); break;
			case 'authenticate':
				if (!isset($_COOKIE[session_name()])) session_start();
				$_SESSION['userdata'] = NULL;
				$_SESSION['user'] = $this->A['user'];
				$_SESSION['password'] = @$this->A['raw_password']
					? $this->A['password'] : md5($this->A['password']);
				$this->USER->authenticate();
				break;
                }
		//$this->CORE->cache_clear();
        }

	private function execToChilds($Action) {
		$vars = $this->DBC->getVarsById($this->A['id']);
		$rv = array('vars'=>$GLOBALS['CMS_REQUIRED_VARS'], 'parent'=>array(), 'external'=>array());
		list($r, $f, $l, $c) = $this->DBC->getChilds($vars, $rv, '', '', '');
		for ($i=$f; $i<=$l && $c>0; $i++) {
			$child = $this->DBC->getRow($r, $i);
			$this->A['id'] = $child['id'];
			$this->execToChilds($Action);
			$this->A['id'] = $child['id'];
			$this->exec($Action);
		}
        }
	
	private function execToAll($Action) {
		$vars = array('id'=>null);
		$rv = array('vars'=>$GLOBALS['CMS_REQUIRED_VARS'], 'parent'=>array(), 'external'=>array());
		list($r, $f, $l, $c) = $this->DBC->getChilds($vars, $rv, '', '', '');
		for ($i=$f; $i<=$l && $c>0; $i++) {
			$child = $this->DBC->getRow($r, $i);
			$this->A['id'] = $child['id'];
			$this->execToChilds($Action);
			$this->A['id'] = $child['id'];
			$this->exec($Action);
		}
        }
	
	public static function transliterate($Str) {
		$Str = trim(strip_tags($Str));
		$search1 = array(' ', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о',
			'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
		$search2 = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О',
			'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я');
		$replace = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'j', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o',
			'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya');
		$Str = str_replace($search1, $replace, $Str);
		$Str = str_replace($search2, $replace, $Str);
		$Str = preg_replace('/[^\w\d]+/iu', '_', $Str);
		$Str = strtolower($Str);
		if (preg_match('/^novaya_zapis$/u', $Str)) $Str = uniqid('id');
		$Str = trim($Str, '_');
		return $Str;
        }
	
	
	public function checkLink() {
		$this->A['vars']['link'] = urlencode($this->A['vars']['link']);
		$this->A['vars']['link'] = str_replace(array('%3A', '%2F'), array(':', '/'), $this->A['vars']['link']);
		$link = $this->A['vars']['link'];
		$i = 1;
		$id = isset($this->A['id']) && $this->A['id']!='' ? $this->A['id'] : null;
		while (!$this->DBC->checkLink($this->A['vars']['link'], $id)) {
			$this->A['vars']['link'] = $link.'_'.$i;
			$i++;
		}
	}

}

?>
