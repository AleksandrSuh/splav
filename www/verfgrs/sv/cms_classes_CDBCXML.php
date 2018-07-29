<?php

class CDBCXML extends CDBC implements IDBC {

	private $Document;
	private $XPath;

	public function connect($Host = DB_HOST, $User = DB_USER, $Pass = DB_Pass, $Name = DB_NAME, $File = DB_FILE) {
		$this->Document = new DOMDocument('1.0', 'UTF-8');
		$this->Document->formatOutput = false;
		$this->Document->load(DIR_ROOT.'/'.$File);
		$this->XPath = new DOMXPath($this->Document);
		return true;
        }
	
	
	private function XPathCheckVersion() {
		if ($this->UsePublicVersion)
			return './back/@version="0000-00-00 00:00:00" and translate(./back/@publish_time, ":- ", "")<="'
				.date('YmdHis')
				.'" and (string(./back/@expire_time)="" or ./back/@expire_time="0000-00-00 00:00:00" or translate(./back/@expire_time, ":- ", "")>="'
				.date('YmdHis').'")';
		else return '1=1';
	}
	
	private function checkOrderby($Orderby) {
		$dataType = '';
		switch ($Orderby) {
			case '': $Orderby = '<xsl:sort select="@n" data-type="number" order="descending" />
<xsl:sort select="@create_time" order="descending" />';
				break;
			//case '?': $Orderby = '`?`'; break;
			default:
				preg_match_all('/\`?(\w+)\`?(\s+((ASC)?(DESC)?))?/', $Orderby, $matches);
				$Orderby = '';
				for ($i=0; $i<count($matches[0]); $i++)  {
					switch ($matches[1][$i]) {
						case 'id':
						case 'n':
						case 'owner_id':
				/* !!! */	case 'year':
				/* !!! */	case 'month':
				/* !!! */	case 'sum':
							$dataType = ' data-type="number"';
						case 'version':
						case 'create_time':
						case 'publish_time':
						case 'cash':
						case 'data_only':
						case 'external_class':
						case 'template_src':
						case 'childrens_template_src':
						case 'link':
						case 'use_content_in_head':
							$matches[1][$i] = '@'.$matches[1][$i];
							break;
						case 'title': 
						case 'keywords':
						case 'description':
						case 'content':
							break;
						default:
					switch ($GLOBALS['EXTERNAL_CLASS'][$matches[1][$i]]['property']['type']) {
								case 'int':
								case 'float':
									$dataType = ' data-type="number"';
									break;
							}
							break;
					}
					$Orderby .= '<xsl:sort select="'.$matches[1][$i].'"'.$dataType.' order="'
						.($matches[3][$i]=='DESC' ? 'descending' : 'ascending').'" />';
				}
				break;
		}
		return $Orderby;
	}
	
	public static function install($AdminName = 'artgk', $AdminPass = '', $DBFile = '.xml') {
		file_put_contents($DBFile, '<?xml version="1.0" encoding="utf-8"?>
<db>
<group id="1" name="default">
	<user id="1" name="guest" password="" expire="2030-01-01 00:00:00" /></group>
<group id="2" name="administrators">
	<user id="2" name="'.$AdminName.'" password="'.$AdminPass.'" expire="2030-01-01 00:00:00" /></group>
<group id="3" name="moderators"></group>
<rights>
	<right id="1" ug_id="1" is_group="false" link="" allow="4" disallow="0" />
	<right id="2" ug_id="2" is_group="true" link="" allow="1" disallow="0" />
	<right id="3" ug_id="3" is_group="true" link="" allow="2" disallow="0" />
	<right id="4" ug_id="1" is_group="false" link="/cms/form" allow="24" disallow="0" />
</rights>
<root><page id="1" version="0000-00-00 00:00:00" create_time="0000-00-00 00:00:00" publish_time="0000-00-00 00:00:00"
	owner_id="0" n="0" link="/" use_content_in_head="path"><title>'.$_SERVER['HTTP_HOST'].'</title></page></root>
</db>');
	}
	
	protected function _query($Query, $Orderby = '', $Document = null) {
		if (empty($Query)) return false;
		$xsl ='<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="utf-8" /><xsl:template match="/"><root><xsl:apply-templates select="'
.htmlspecialchars($Query).'">';
		$xsl .= $this->checkOrderby($Orderby);
		$xsl .= '</xsl:apply-templates></root></xsl:template>
<xsl:template match="*"><xsl:copy><xsl:for-each select="@*"><xsl:copy /></xsl:for-each><xsl:apply-templates />
</xsl:copy></xsl:template></xsl:stylesheet>';
		$proc = new XSLTProcessor();
		$proc->importStyleSheet(DOMDocument::loadXML($xsl));
		$xpath = new DOMXPath(DOMDocument::loadXML($proc->transformToXML(is_null($Document)
			? $this->Document : $Document)));
		return $xpath->query('/root/*');
	}
	
	public function authenticate($Name, $Password) {
		$r = $this->query('/db/group/user[@name="'.$Name.'" and @password="'.$Password.'"]');
		$userVars = $this->GetRow($r);
		$r = $this->query('/db/group/user[@name="'.$Name.'" and @password="'.$Password.'"]/..');
		$groupVars = $this->GetRow($r);
		$userVars = array('user.id'=>$userVars['id'],
			'user.group_id'=>$groupVars['id'],
			'user.name'=>$userVars['name'],
			'user.password'=>$userVars['password'],
			'user.expire'=>$userVars['expire'],
			'user.email'=>(isset($userVars['email']) ? $userVars['email'] : ''),
			'group.id'=>$groupVars['id'],
			'group.name'=>$groupVars['name']);
		return $userVars;
	}
	
	public function getUserRights() {
		$r = $this->query('/db/root//page[@link="'.$_SERVER['REQUEST_URI'].'"]');
		if ($r->length>0) $ownerId = $r->item(0)->getAttribute('owner_id');
		else $ownerId = '0';
		$r = $this->query('/db/group/user[@id="'.$ownerId.'"]/..');
		if ($r->length>0) $ownerGroupId = $r->item(0)->getAttribute('id');
		else $ownerGroupId = '0';
		$r = $this->query('/db/rights/right[
			(	(	(	@ug_id="'.$this->USER->getVar('user.id').'"
						or (@ug_id="0" and "'.$this->USER->getVar('user.id').'"="'.$ownerId.'")
					)
					and @is_group="false"
				)
				or ((	@ug_id="'.$this->USER->getVar('group.id').'"
					or (@ug_id="0" and "'.$this->USER->getVar('group.id').'"="'.$ownerGroupId.'")
					)
					and @is_group="true"
				)
			)
			and (@link="'.$_SERVER['REQUEST_URI'].'" or @link="")
		]');
		$allow = 0;
		$disallow = 0;
		foreach ($r as $e) {
			$allow |= (int)$e->getAttribute('allow');
			$disallow |= (int)$e->getAttribute('disallow');
		} 
		return $allow & ~$disallow;
	}
	
	public function getVars($Link, $RequiredVars = null) {
		return $this->getRow($this->query('/db/root//page[@link="'.$Link.'" and '
			.$this->XPathCheckVersion().']'), 0, 0, $RequiredVars);
	}

	public function getVarsById($Id, $RequiredVars = null) {
		return $this->getRow($this->query('/db/root//page[@id="'.$Id.'" and '
			.$this->XPathCheckVersion().']'), 0, 0, $RequiredVars);
	}

	public function getRow($NodeList, $Item = 0, $F = 0, &$RequiredVars = null) {
		if ($e = $NodeList->item($Item)) {
			$xpath = new DOMXPath($NodeList->item($Item)->ownerDocument);
			if ($e->tagName=='page' && $this->UsePublicVersion) {
				$r = $xpath->query('back[@version="0000-00-00 00:00:00"]', $e);
				if ($r->length>0) $e = $r->item(0);
				else return null;
			}
			$result = array();
			foreach ($e->attributes as $attribute) {
				$result[$attribute->name] = $attribute->value;
				if (substr($attribute->name, 0, 6)=='assoc.') $e->removeAttribute($attribute->name);
				if ($e->tagName=='user') $result['user.'.$attribute->name] = $attribute->value;
			}
			$result['head'] = null;
			$result['title'] = null;
			$result['keywords'] = null;
			$result['description'] = null;
			$result['image'] = null;
			$result['content'] = null;
			foreach ($xpath->query('*', $e) as $child)
				switch ($child->tagName) {
					case 'assoc':
					case 'page':
					case 'back':
						break;
					case 'head':
					case 'title':
					case 'keywords':
					case 'description':
					case 'image':
					case 'content':
						$result[$child->tagName] = DOMElementInnerXML($child);
						break;
					default:
						$result['external.'.$child->tagName] = DOMElementInnerXML($child);
						break;
				}
			if (!empty($RequiredVars['parent'])) {
				$rv = array('vars'=>array(), 'parent'=>array(), 'external'=>array());
				list($r, $f, $l, $c) = $this->getParent($result, $rv);
				if ($parentVars = $this->getRow($r))
					foreach ($parentVars as $key=>$var) $result['parent.'.$key] = $var;
                        }
			return $result;
		} else return null;
	}

	public function getMaxN($ParentId) {
		$r = $this->query('/db/root'.($ParentId!='' ? '//page[@id="'.$ParentId.'"]' : '').'/page');
		if ($vars = $this->getRow($r)) return $vars['n'];
		else return 0;
	}

	public function checkLink($Link, $Id) {
		$r = $this->XPath->query('/db/root//page['
			.(!is_null($Id) ? '@id!="'.$Id.'" and ' : '')
			.'@link="'.$Link.'"]');
		if ($r->length==0) return true;
		else return false;
	}

	public function checkUserName($UserId, $UserName) {
		$r = $this->XPath->query('/db/group/user['
			.($UserId!='' ? '@id!="'.$UserId.'" and ' : '')
			.'@name="'.$UserName.'"]');
		if ($r->length==0) return true;
		else return false;
	}

	public function checkId($Id) {
		$r = $this->XPath->query('/db/root//page[@id="'.$Id.'"]');
		return $r->length==0;
	}

	public function getParentTemplateSrc(&$Vars) {
		if (!isset($Vars['id'])) return array('', 0);
		$vars = array('id'=>$Vars['id']);
		for ($k=1; $vars = $this->getRow($this->query('/db/root//page[@id="'
			.$vars['id'].'"]/parent::page')); $k++)
			if ($vars['childrens_template_src']!='') return array($vars['childrens_template_src'], $k);
		return array(isset($Vars['childrens_template_src']) ? $Vars['childrens_template_src'] : '', 0);
	}

	public function getLastPublishTime() {
		$r = $this->query('/db/root//page', '`publish_time` DESC');
		if ($vars = $this->getRow($r)) return $vars['publish_time'];
		else return 0;
        }
	
	public function deleteOldVersions($Id, $VersionsNum = DB_VERSIONS_NUM) {
		$r = $this->query('/db/root//back[@id="'.$Id.'" and @version!="0000-00-00 00:00:00"]',
			'`version` DESC');
		$query = '';
		for ($i=$VersionsNum; $i<$r->length; $i++) {
			$row = $this->getRow($r, $i);
			$query .= ($i>$VersionsNum ? ' or ' : '').'@version="'.$row['version'].'"';
		}
		if ($query!='') {
			$r = $this->XPath->query('/db/root//back[@id="'.$Id.'" and ('.$query.')]');
			foreach ($r as $e) $e->parentNode->removeChild($e);
		}
	}
	
	private function setNewPublishTime1($Id, $Time, $First) {
		if (!isset($GLOBALS['SPANPT'])) $GLOBALS['SPANPT'] = array();
		if ($First) {
			$r = $this->XPath->query('/db/root//back[@id="'.$Id.'" and @version="0000-00-00 00:00:00"]');
			foreach ($r as $e) $e->setAttribute('publish_time', $Time);
			$GLOBALS['SPANPT'][] = $Id;
		}
		$r = $this->XPath->query('/db/root//page[@id="'
			.$Id.'"]/parent::page/back[@version="0000-00-00 00:00:00"]');
		foreach ($r as $e) {
			$e->setAttribute('publish_time', $Time);
			$GLOBALS['SPANPT'][] = $e->getAttribute('id');
			$this->setNewPublishTime1($e->getAttribute('id'), $Time, false);
		}
		$r = $this->XPath->query('/db/root//page[@id="'.$Id.'"]/assoc');
		foreach ($r as $e) {
			$foreign_id = $e->getAttribute('id');
			if (!in_array($foreign_id, $GLOBALS['SPANPT'])) {
				$r = $this->XPath->query('/db/root//back[@id="'
					.$foreign_id.'" and @version="0000-00-00 00:00:00"]');
				foreach ($r as $e) $e->setAttribute('publish_time', $Time);
				$GLOBALS['SPANPT'][] = $foreign_id;
				$this->setNewPublishTime1($foreign_id, $Time, false);
			}
		}
		$r = $this->XPath->query('/db/root//page[assoc/@id="'.$Id.'"]');
		foreach ($r as $e) {
			$id = $e->getAttribute('id');
			if (!in_array($id, $GLOBALS['SPANPT'])) {
				$r = $this->XPath->query('/db/root//back[@id="'
					.$id.'" and @version="0000-00-00 00:00:00"]');
				foreach ($r as $e) $e->setAttribute('publish_time', $Time);
				$GLOBALS['SPANPT'][] = $id;
				$this->setNewPublishTime1($id, $Time, false);
			}
		}
	}

	private function setNewPublishTime($Id, $First) {
		$this->setNewPublishTime1($Id, date('Y-m-d H:i:s'), $First);
	}
	


	/*
         * Queries
         */


	public function getSiblings(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $ReturnCount = false) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if ($r = $this->query((is_null($Vars['id']) ? '/db/root/page' : '/db/root//page[@id="'
			.$Vars['id'].'"]/../page').'['.$this->XPathCheckVersion().']', $Orderby)) {
			$c = $r->length;
			if ($ReturnCount) return $c;
			if ($Length!='') {
				$f = ($Page-1)*$Length;
				$l = min($c-1, $f+$Length-1);
			}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}

	public function getChilds(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $Deep = 1, $ReturnCount = false) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if ($r = $this->query((is_null($Vars['id']) ? '/db/root/page' : '/db/root//page[@id="'
			.$Vars['id'].'"]/page').'['.$this->XPathCheckVersion().']', $Orderby)) {
			$c = $r->length;
			if ($ReturnCount) return $c;
			if ($Length!='') {
				$f = ($Page-1)*$Length;
				$l = min($c-1, $f+$Length-1);
			}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}
	
	public function getParent(&$Vars, &$RequiredVars, $Deep = 1) {
		if (!isset($Vars['id'])) $Vars['id'] = '';
		$query = '/db/root//page[@id="'.$Vars['id'].'"]';
		for ($i=0; $i<$Deep; $i++) $query .= '/parent::page';
		$query .= '['.$this->XPathCheckVersion().']';
		$r = $this->query($query);
		return array($r, 0, 0, $r->length);
	}
	
	public function getAssoc(&$Vars, &$RequiredVars, $Orderby, $Length, $Page) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$r = false;
		$query = '';
		if (!isset($Vars['assoc.id'])) {
			$r = $this->query('/db/root//page[@id="'.$Vars['id'].'"]/parent::page/assoc');
			for ($i=0; $i<$r->length; $i++) {
				$row = $this->GetRow($r, $i);
				$query .= ($i>0 ? ' or ' : '').'@id="'.$row['id'].'"';
			}
			if ($query!='') {
				$r = $this->query('/db/root//page['.$query.']', $Orderby);
				for ($i=0; $i<$r->length; $i++) {
					$r->item($i)->setAttribute('assoc.id', $Vars['id']);
					$r->item($i)->setAttribute('assoc.parent_id', $Vars['id']);
				}
			}
		} else {
			$r = $this->query('/db/root//page[@id="'.$Vars['assoc.id'].'"]/assoc');
			for ($i=0; $i<$r->length; $i++) {
				$row = $this->GetRow($r, $i);
				$query .= ($i>0 ? ' or ' : '').'@id="'.$row['id'].'"';
			}
			if ($query!='') $r = $this->query('/db/root//page[@id="'.$Vars['id'].'"]/page['.$query.']',
				$Orderby);
		}
		if ($r) {
			$c = $r->length;
			if ($Length!='') {$f = ($Page-1)*$Length; $l = min($c-1, $f+$Length-1);}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}

	public function getRAssoc(&$Vars, &$RequiredVars, $ForeignId, $Orderby, $Length, $Page) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if ($r = $this->query('/db/root//'
				.(!is_null($Vars) ? 'page[@id="'.$Vars['id'].'"]/' : '')
				.'page[assoc/@id="'.$ForeignId.'" and '.$this->XPathCheckVersion().']', $Orderby)) {
			for ($i=0; $i<$r->length; $i++) $r->item($i)->setAttribute('assoc.foreign_id', $ForeignId);
			$c = $r->length;
			if ($Length!='') {$f = ($Page-1)*$Length; $l = min($c-1, $f+$Length-1);}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}

	public function getDAssoc(&$Vars, &$RequiredVars, $Id, $Orderby, $Length, $Page) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$query = '';
		$r = $this->query('/db/root//page[@id="'.$Id.'"]/assoc');
		for ($i=0; $i<$r->length; $i++) {
			$row = $this->GetRow($r, $i);
			$query .= ($i>0 ? ' or ' : '').'@id="'.$row['id'].'"';
		}
		if ($query!='' && ($r = $this->query('/db/root//'
				.(!is_null($Vars) ? 'page[@id="'.$Vars['id'].'"]/' : '')
				.'page['.$query.' and '.$this->XPathCheckVersion().']', $Orderby))) {
			for ($i=0; $i<$r->length; $i++) $r->item($i)->setAttribute('assoc.id', $Id);
			$c = $r->length;
			if ($Length!='') {$f = ($Page-1)*$Length; $l = min($c-1, $f+$Length-1);}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}

	public function select($Sql, $XPath, $Orderby, $Length, $Page, $Element, $DbFile = null) {
		if (!is_null($DbFile) && $DbFile!='' && file_exists($DbFile)) {
			$Document = new DOMDocument('1.0', 'UTF-8');
			@$Document->load($DbFile);
                } else $Document = null;
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if ($r = $this->query($XPath, $Orderby, $Document)) {
			$c = $r->length;
			if ($Length!='') {$f = ($Page-1)*$Length; $l = min($c-1, $f+$Length-1);}
			else {$f = 0; $l = $c-1;}
			return array($r, $f, $l, $c);
		} else return array(false, 0, 0, 0);
	}

	public function rawQuery($Sql, $XPath, $Orderby, $DbFile = null) {
		if (!is_null($DbFile) && $DbFile!='' && file_exists($DbFile)) {
			$Document = new DOMDocument('1.0', 'UTF-8');
			@$Document->load($DbFile);
                } else $Document = null;
		$r = $this->query($XPath, $Orderby, $Document);
		$c = $r ? $r->length : 0;
		return array($r, 0, $c-1, $c);
	}

	public function search(&$RequiredVars, $Query, $Length, $Page, $Element) {
		if ($Query=='') return array(false, 0, 0, 0);
		return $this->select(null, '/db/root//page[contains(title, "'.$Query.'") or contains(keywords, "'
			.$Query.'") or contains(description, "'.$Query.'") or contains(content, "'.$Query.'") and '
			.$this->XPathCheckVersion().']',
			null, $Length, $Page, $Element);
	}

	public function getUsers($Id, $GroupId, $Orderby, $Length, $Page, $Element) {
		return $this->select(null, '/db/group'.($GroupId!='' ? '[@id="'.$GroupId.'"]' : '')
			.'/user'.($Id!='' ? '[@id="'.$Id.'"]' : ''), $Orderby, $Length,
			$Page, $Element);

	}
	


	/*
         * Actions
         */

	public function save(&$A) {
		if (isset($A['id']) && $A['id']!='') {
			$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]');
			if ($r->length>0) {
				$e = $r->item(0);
				$oldVars = $this->getVarsById($A['id']);
			}
		}
		if (!isset($oldVars)) {
			$e = $this->Document->createElement('page');
			$r = $this->XPath->query('/db/root'.(isset($A['parent_id']) && $A['parent_id']!=''
				? '//page[@id="'.$A['parent_id'].'"]' : ''));
			$r->item(0)->appendChild($e);
			$oldVars = array();
		}
		$newVars = array_merge($oldVars, $A['vars']);
		if ($oldVars==$newVars) return true;
		
		if (!isset($newVars['id']) && (!isset($A['id']) || $A['id']=='')) $newVars['id'] = uniqid('id');
		$newVars['version'] = date('Y-m-d H:i:s');
		if (!isset($newVars['use_content_in_head'])) $newVars['use_content_in_head'] = 'path';
		
		foreach ($newVars as $key=>$var)
			if ($var=='') unset($newVars[$key]);
			else if (array_key_exists($key, $A['vars'])) $newVars[$key] = str_replace('&', '&amp;', $var);
			else $newVars[$key] = $var;
		
		$ase = array();
		if (isset($A['id']) && $A['id']!='') {
			$be = $this->Document->createElement('back');
			$e->appendChild($be);
			foreach ($e->attributes as $attr) $be->setAttribute($attr->name, $attr->value);
			foreach ($this->XPath->query('*', $e) as $child)
				switch ($child->tagName) {
					case 'assoc':
					case 'page':
					case 'back':
						break;
					case 'head':
					case 'title':
					case 'keywords':
					case 'description':
					case 'image':
					case 'content':
					default: 
						$ase[$child->tagName] = $child;
						$se = $this->Document->createElement($child->tagName);
						$be->appendChild($se);
						$f = $this->Document->createDocumentFragment();
						$f->appendXML(DOMElementInnerXML($child));
						$se->appendChild($f);
						break;
				}
			$this->deleteOldVersions($A['id'], DB_VERSIONS_NUM-1);
		}
		
		foreach ($newVars as $key=>$var)
			switch ($key) {
				case 'assoc':
				case 'page':
				case 'back':
					break;
				case 'id':
				case 'version':
				case 'create_time':
				case 'publish_time':
				case 'cash':
				case 'data_only':
				case 'owner_id':
				case 'external_class':
				case 'n':
				case 'template_src':
				case 'childrens_template_src':
				case 'link':
				case 'use_content_in_head':
					$e->setAttribute($key, $var);
					break;
				case 'head':
				case 'title':
				case 'keywords':
				case 'description':
				case 'image':
				case 'content':
				default:
					if (substr($key, 0, 9)=='external.') $key = substr($key, 9);
					if (array_key_exists($key, $ase)) $e->removeChild($ase[$key]);
					$se = $this->Document->createElement($key);
					$e->appendChild($se);
					if ($var!='') {
						$f = $this->Document->createDocumentFragment();
						$f->appendXML($var);
						$se->appendChild($f);
					}
					break;
			}
		if (!isset($A['id']) || $A['id']=='') $A['id'] = $newVars['id'];
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function delete(&$A) {
		$this->setNewPublishTime($A['id'], false);
		$delVars = $this->getVarsById($A['id'], array(
			'vars'=>array('id', 'version'), 
			'parent'=>array('external_class', 'link'), 
			'external'=>array()));
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]');
		if ($r->length>0) $e = $r->item(0);
		else return false;
		$e->parentNode->removeChild($e);
		$this->Document->save($this->Document->documentURI);
		return $delVars['parent.link'];
	}

	public function publish(&$A) {
		$pubVars = $this->getVarsById($A['id']);
		$r = $this->XPath->query('/db/root//back[@id="'.$A['id'].'" and @version="0000-00-00 00:00:00"]');
		foreach ($r as $e) $e->parentNode->removeChild($e);
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]');
		if ($r->length>0) $e = $r->item(0);
		else return false;
		$e->setAttribute('publish_time', max($pubVars['publish_time'], date('Y-m-d H:i:s')));
		$be = $this->Document->createElement('back');
		$e->appendChild($be);
		foreach ($e->attributes as $attr) $be->setAttribute($attr->name, $attr->value);
		$be->setAttribute('version', '0000-00-00 00:00:00');
		foreach ($this->XPath->query('*', $e) as $child)
			switch ($child->tagName) {
				case 'assoc':
				case 'page':
				case 'back':
					break;
				case 'head':
				case 'title':
				case 'keywords':
				case 'description':
				case 'image':
				case 'content':
				default: 
					$se = $this->Document->createElement($child->tagName);
					$be->appendChild($se);
					$f = $this->Document->createDocumentFragment();
					$f->appendXML(DOMElementInnerXML($child));
					$se->appendChild($f);
					break;
			}
		$this->setNewPublishTime($A['id'], false);
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function hide(&$A) {
		$this->setNewPublishTime($A['id'], false);
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]');
		if ($r->length>0) $e = $r->item(0);
		else return false;
		$e->setAttribute('publish_time', '0000-00-00 00:00:00');
		$r = $this->XPath->query('back[@version="0000-00-00 00:00:00"]', $e);
		if ($r->length>0) $e = $r->item(0);
		else return false;
		$e->parentNode->removeChild($e);
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function addAssoc(&$A) {
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]');
		if ($r->length>0) $e = $r->item(0);
		else return false;
		$ae = $this->Document->createElement('assoc');
		$ae->setAttribute('id', $A['foreign_id']);
		$e->appendChild($ae);
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function removeAssoc(&$A) {
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]/assoc[@id="'.$A['foreign_id'].'"]');
		foreach ($r as $e) $e->parentNode->removeChild($e);
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function backup(&$A) {
		$r = $this->XPath->query('/db/root//page[@id="'.$A['id'].'"]/back[@version="'.$A['version'].'"]');
		if ($r->length>0) $e = $r->item(0);
		else return false;
		
		$A['vars'] = array();
		foreach ($e->attributes as $attribute) $A['vars'][$attribute->name] = $attribute->value;
		foreach ($this->XPath->query('*', $e) as $child)
			switch ($child->tagName) {
				case 'assoc':
				case 'page':
				case 'back':
					break;
				case 'head':
				case 'title':
				case 'keywords':
				case 'description':
				case 'image':
				case 'content':
					$A['vars'][$child->tagName] =
						html_entity_decode(DOMElementInnerXML($child), null, 'utf-8');
					break;
				default:
					$A['vars']['external.'.$child->tagName] =
						html_entity_decode(DOMElementInnerXML($child), NULL, 'utf-8');
					break;
			}
		$e->parentNode->removeChild($e);
		$this->save($A);
		return true;
	}

	public function moveTo(&$A) {//!!!!!!!!!!! Change Parent !!!!!!!!!!!!
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('n', 'create_time'), 
			'parent'=>array('id'), 
			'external'=>array()));
		$varsTo = $this->getVarsById($A['moveto_id'], array(
			'vars'=>array('n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		
		$up = $varsTo['n']>$vars['n'] 
			|| ($varsTo['n']==$vars['n'] && $varsTo['create_time']>$vars['create_time'])
			? 'true' : 'false';

		if (isset($vars['parent.id']) && $vars['parent.id']!='') {
			$query = '/db/root//page[@id="'.$vars['parent.id'].'"]/page';
		} else $query = '/db/root/page';
		
		foreach ($this->XPath->query($query) as $e) {
			$id = $e->getAttribute('id');
			$n = (int)$e->getAttribute('n');
			$create_time = $e->getAttribute('create_time');
			if ($n>$varsTo['n'] 
				|| ($n==$varsTo['n'] && $create_time>$varsTo['create_time'])
				|| ($id==$A['moveto_id'] && !$up))
				$e->setAttribute('n', $n + 1);
			if ($n<$varsTo['n'] 
				|| ($n==$varsTo['n'] && $create_time<$varsTo['create_time'])
				|| ($id==$A['moveto_id'] && $up))
				$e->setAttribute('n', $n - 1);
			if ($id==$A['id']) $e->setAttribute('n', $varsTo['n']);
		}
		$this->setNewPublishTime($A['id'], true);
		$this->Document->save($this->Document->documentURI);
		return true;
	}

	public function moveUp(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		$r = $this->query('/db/root//page[@id="'.$A['id'].'"]/../page[@n>"'
			.$vars['n'].'" or (@n="'.$vars['n'].'" and translate(@create_time, ":- ", "")>translate("'
			.$vars['create_time'].'", ":- ", ""))]', '`n` ASC, `create_time` ASC');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			$this->moveTo($A);
                }
		return true;
	}

	public function moveDown(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		$r = $this->query('/db/root//page[@id="'.$A['id'].'"]/../page[@n<"'
			.$vars['n'].'" or (@n="'.$vars['n'].'" and translate(@create_time, ":- ", "")<translate("'
			.$vars['create_time'].'", ":- ", ""))]', '`n` DESC, `create_time` DESC');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			$this->moveTo($A);
                }
		return true;
	}

	public function moveToFirst(&$A) {
		$r = $this->query('/db/root//page[@id="'.$A['id'].'"]/../page', '`n` DESC, `create_time` DESC');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			$this->moveTo($A);
                }
		return true;
	}

	public function moveToLast(&$A) {
		$r = $this->query('/db/root//page[@id="'.$A['id'].'"]/../page', '`n` ASC, `create_time` ASC');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			$this->moveTo($A);
                }
		return true;
	}
	
	public function groupSave(&$A) {
		if (isset($A['name']) && $A['name']!='') {
			if (!isset($A['id']) || $A['id']=='') {
				$ge = $this->Document->createElement('group');
				$this->Document->documentElement->appendChild($ge);
				$ge->setAttribute('id', uniqid('g'));
			} else {
				$r = $this->XPath->query('/db/group[@id="'.$A['id'].'"]');
				if ($r->length>0) $ge = $r->item(0);
				else return false;
			}
			$ge->setAttribute('name', $A['name']);
			$this->Document->save($this->Document->documentURI);
			return true;
		}
		return false;
	}
	public function GroupDelete(&$A) {
		if (isset($A['id']) && $A['id']!='') {
			$r = $this->XPath->query('/db/group[@id="'.$A['id'].'"]');
			foreach ($r as $e) $e->parentNode->removeChild($e);
			$this->Document->save($this->Document->documentURI);
			return true;
		}
		return false;
	}
	public function userSave(&$A) {
		if (!isset($A['id']) || $A['id']=='') {
			$r = $this->XPath->query('/db/group[@id="'
				.(isset($A['group_id']) && $A['group_id']!='' ? $A['group_id'] : '1').'"]');
			if ($r->length>0) $e = $r->item(0);
			else return false;
			$ue = $this->Document->createElement('user');
			$e->appendChild($ue);
			$ue->setAttribute('id', uniqid('u'));
		} else {
			$r = $this->XPath->query('/db/group/user[@id="'.$A['id'].'"]');
			if ($r->length>0) $ue = $r->item(0);
			else return false;
		}
		
		if (isset($A['group_id']) && $A['group_id']!='') $ue->setAttribute('group_id', $A['group_id']);
		if (isset($A['name']) && $A['name']!='') $ue->setAttribute('name', $A['name']);
		if (isset($A['password']) && $A['password']!='') $ue->setAttribute('password', md5($A['password']));
		if (isset($A['expire']) && $A['expire']!='') $ue->setAttribute('expire', $A['expire']);
		else $ue->setAttribute('expire', date('Y-m-d H:i:s'));
		if (isset($A['email']) && $A['email']!='') $ue->setAttribute('email', $A['email']);
		$this->Document->save($this->Document->documentURI);
		return true;
	}
	public function userDelete(&$A) {
		if (isset($A['id']) && $A['id']!='') {
			$r = $this->XPath->query('/db/group/user[@id="'.$A['id'].'"]');
			foreach ($r as $e) $e->parentNode->removeChild($e);
			$this->Document->save($this->Document->documentURI);
			return true;
		}
		return false;
	}
	public function rightsSave(&$A) {
		if (!isset($A['id']) || $A['id']=='') {
			$r = $this->XPath->query('/db/rights');
			if ($r->length>0) $e = $r->item(0);
			else return false;
			$re = $this->Document->createElement('right');
			$e->appendChild($re);
			$re->setAttribute('id', uniqid('r'));
		} else {
			$r = $this->XPath->query('/db/rights/right[@id="'.$A['id'].'"]');
			if ($r->length>0) $re = $r->item(0);
			else return false;
		}
		if (isset($A['ug_id']) && $A['ug_id']!='') $re->setAttribute('ug_id', $A['ug_id']);
		if (isset($A['is_group']) && $A['is_group']!='') $re->setAttribute('is_group', $A['is_group']);
		if (isset($A['link']) && $A['link']!='') $re->setAttribute('link', $A['link']);
		else $re->setAttribute('link', '');
		if (isset($A['allow']) && $A['allow']!='') $re->setAttribute('allow', $A['allow']);
		if (isset($A['disallow']) && $A['disallow']!='') $re->setAttribute('disallow', $A['disallow']);
		$this->Document->save($this->Document->documentURI);
		return true;
	}
	public function rightsDelete(&$A) {
		if (isset($A['id']) && $A['id']!='') {
			$r = $this->XPath->query('/db/rights/right[@id="'.$A['id'].'"]');
			foreach ($r as $e) $e->parentNode->removeChild($e);
			$this->Document->save($this->Document->documentURI);
			return true;
		}
		return false;
	}
		
	
	
	/*
         * Form
         */

	public function formAdd($Id, $Code, $Redirect, $CheckForm, $Code2) {
		if (!file_exists(DIR_ROOT.'/form.xml'))
			file_put_contents(DIR_ROOT.'/form.xml', '<?xml version="1.0" encoding="utf-8"?><form />');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/form.xml');
		$e = $d->createElement('form');
		$e->setAttribute('id', $Id);
		$e->setAttribute('time', time());
		$e->setAttribute('code', $Code);
		$e->setAttribute('redirect', $Redirect);
		$e->setAttribute('code2', $Code2);
		$e->setAttribute('checkform', $CheckForm);
		$d->documentElement->appendChild($e);
		$d->save(DIR_ROOT.'/form.xml');
	}
	
	public function formGet($Id) {
		$res = array();
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/form.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/form/form[@id="'.$Id.'"]');
		if ($r->length>0) {
			$e = $r->item(0);
			$res['code'] = $e->getAttribute('code');
			$res['redirect'] = $e->getAttribute('redirect');
			$res['checkform'] = $e->getAttribute('checkform');
			$res['code2'] = $e->getAttribute('code2');
			$res['post'] = $e->getAttribute('post');
			return $res;
		} else return array('code'=>null, 'redirect'=>null, 'checkform'=>null, 'code2'=>null, 'post'=>null);
	}

	public function formClear($Id = null) {
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/form.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/form/form[@time<'.(time()-86400).']');
		foreach ($r as $e) $e->parentNode->removeChild($e);
	}

	public function formSavePost($Id, $Post) {
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/form.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/form/form[@id="'.$Id.'"]');
		$e->setAttribute('post', $Post);
		$d->save(DIR_ROOT.'/form.xml');
	}
	
	
	
	/*
         * Statistic
         */
	
	public function statUnique() {
		if (!file_exists(DIR_ROOT.'/stat.xml')) return true;
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/stat.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/stat/stat[@year="'.date('Y').'" and @month="'.date('m')
			.'" and contains(@unique_list, "'.$_SERVER['REMOTE_ADDR'].'")]');
		return $r->length==0;
        }

	public function statAdd($Unique, $External, $Google, $Yandex, $Rambler) {
		if (!file_exists(DIR_ROOT.'/stat.xml'))
			file_put_contents(DIR_ROOT.'/stat.xml', '<?xml version="1.0" encoding="utf-8"?><stat />');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/stat.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/stat/stat[@year="'.date('Y').'" and @month="'.date('m').'"]');
		
		if ($r->length==0) {
			$e = $d->createElement('stat');
			$e->setAttribute('year', date('Y'));
			$e->setAttribute('month', date('m'));
			$e->setAttribute('click', '1');
			$e->setAttribute('unique', '1');
			$e->setAttribute('external', ($External ? '1' : '0'));
			$e->setAttribute('google', ($Google ? '1' : '0'));
			$e->setAttribute('yandex', ($Yandex ? '1' : '0'));
			$e->setAttribute('rambler', ($Rambler ? '1' : '0'));
			$e->setAttribute('unique_list', $_SERVER['REMOTE_ADDR']);
			$d->documentElement->appendChild($e);
                } else {
			$e = $r->item(0);
			$e->setAttribute('click', 1+(int)$e->getAttribute('click'));
			if ($Unique) {
				$e->setAttribute('unique', 1+(int)$e->getAttribute('unique'));
				$e->setAttribute('unique_list',
					$e->getAttribute('unique_list').' '.$_SERVER['REMOTE_ADDR']);
                        }
			if ($External) $e->setAttribute('external', 1+(int)$e->getAttribute('external'));
			if ($Google) $e->setAttribute('google', 1+(int)$e->getAttribute('google'));
			if ($Yandex) $e->setAttribute('yandex', 1+(int)$e->getAttribute('yandex'));
			if ($Rambler) $e->setAttribute('rambler', 1+(int)$e->getAttribute('rambler'));
		}
		$d->save(DIR_ROOT.'/stat.xml');
        }

	public function statSearchAdd($Search, $Google, $Yandex, $Rambler) {
		if (!file_exists(DIR_ROOT.'/stat.xml'))
			file_put_contents(DIR_ROOT.'/stat.xml', '<?xml version="1.0" encoding="utf-8"?><stat />');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/stat.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('/stat/search[@year="'.date('Y').'" and @month="'.date('m')
			.'" and @search="'.$Search.'"]');
		
		if ($r->length==0) {
			$e = $d->createElement('search');
			$e->setAttribute('year', date('Y'));
			$e->setAttribute('month', date('m'));
			$e->setAttribute('search', $Search);
			$e->setAttribute('google', ($Google ? '1' : '0'));
			$e->setAttribute('yandex', ($Yandex ? '1' : '0'));
			$e->setAttribute('rambler', ($Rambler ? '1' : '0'));
			$e->setAttribute('sum', '1');
			$d->documentElement->appendChild($e);
                } else {
			$e = $r->item(0);
			$e->setAttribute('sum', 1+(int)$e->getAttribute('sum'));
			if ($External) $e->setAttribute('external', 1+(int)$e->getAttribute('external'));
			if ($Google) $e->setAttribute('google', 1+(int)$e->getAttribute('google'));
			if ($Yandex) $e->setAttribute('yandex', 1+(int)$e->getAttribute('yandex'));
			if ($Rambler) $e->setAttribute('rambler', 1+(int)$e->getAttribute('rambler'));
		}
		$d->save(DIR_ROOT.'/stat.xml');
        }
	
	public function logAdd($Ip, $User, $Link, $Post) {
		if (!file_exists(DIR_ROOT.'/log.xml'))
			file_put_contents(DIR_ROOT.'/log.xml', '<?xml version="1.0" encoding="utf-8"?><log />');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/log.xml');
		$e = $d->createElement('log');
		$e->setAttribute('id', uniqid('id'));
		$e->setAttribute('time', date('Y-m-d H:i:s'));
		$e->setAttribute('ip', $Ip);
		$e->setAttribute('user', $User);
		$e->setAttribute('link', $Link);
		$e->setAttribute('post', $Post);
		$d->documentElement->appendChild($e);
		$d->save(DIR_ROOT.'/log.xml');
        }

	public function counterAdd($Id) {
		if (!file_exists(DIR_ROOT.'/counter.xml'))
			file_put_contents(DIR_ROOT.'/counter.xml', '<?xml version="1.0" encoding="utf-8"?><counter />');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/counter.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('//counter[@id="'.$Id.'"]');
		if ($r->length==0) {
			$e = $d->createElement('counter');
			$e->setAttribute('id', $Id);
			$e->setAttribute('count', '0');
			$d->documentElement->appendChild($e);
		} else {
			$e = $r->item(0);
			$e->setAttribute('count', (int)$e->getAttribute('count') + 1);
		}
		$d->save(DIR_ROOT.'/counter.xml');
	}

	public function counterGet($Id) {
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/counter.xml');
		$xpath = new DOMXpath($d);
		$r = $xpath->query('//counter[@id="'.$Id.'"]');
		if ($r->length>0) {
			$e = $r->item(0);
			return (int)$e->getAttribute('count');
		} else return 0;
	}





	/*
	 * External Classes
	 */

	public function classAdd($ClassName) {;}
	
	public function classSave($ClassName, $NewClassName) {;}

	public function classRemove($ClassName) {;}

	public function classPropertyAdd($ClassName, $PropertyName, $PropertyType) {;}

	public function classPropertySave($ClassName, $PropertyName, $NewPropertyName, $PropertyType) {;}

	public function classPropertyRemove($ClassName, $PropertyName) {;}

}

?>
