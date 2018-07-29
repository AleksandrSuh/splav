<?php


class CTag extends CCoreObject
{

	public $Element;
	public $Vars;
	public $Params;

	public static function replace(DOMElement &$Element, array &$Vars, array &$Params)
	{
		if (COMPATIBILITY_MODE=='true')
		{
			if ($Element->hasAttribute('src')
				&& strpos($Element->getAttribute('template'), '@')===false)
				$Element->setAttribute('template', $Element->getAttribute('template')
					.'@'.$Element->getAttribute('src'));
			if ($Element->hasAttribute('parent_id'))
				$Element->setAttribute('id', $Element->getAttribute('parent_id'));
			if ($Element->hasAttribute('parent_link'))
				$Element->setAttribute('link', $Element->getAttribute('parent_link'));
				
			if ($Element->hasAttribute('asText'))
				$Element->setAttribute('astext', $Element->getAttribute('asText'));
			if ($Element->hasAttribute('toValue'))
				$Element->setAttribute('tovalue', $Element->getAttribute('toValue'));
			if ($Element->hasAttribute('checkVersion'))
				$Element->setAttribute('checkversion', $Element->getAttribute('checkVersion'));
		}
		
		$Params['template'] = preg_replace('/^file:\/\/\/(\w)%3A/', '$1:',
			$Element->ownerDocument->documentURI);
		
		$name = strtolower($Element->localName);
		if (in_array($name, $GLOBALS['TAG_FUNCTION']))
			$tag = new CTagFunction($Element, $Vars, $Params);
		else if (in_array($name, $GLOBALS['TAG_SELECT']))
			$tag = new CTagSelect($Element, $Vars, $Params);
		else
			$tag = new CTagHTML($Element, $Vars, $Params);

		if (!isset($tag))
		{
			$Element->parentNode->replaceChild(new DOMText(''), $Element);
			return false;
		}

		$tag->init();
		
		$replacement = null;
		if ((is_a($tag, 'CTagSelect') || $name=='main') && $name!='childrenscount' && !$GLOBALS['VIEW_STRICT'])
		{
			if ($Element->getAttribute('view_strict')=='yes')
			{
				$old_strict = $GLOBALS['VIEW_STRICT'];
				$GLOBALS['VIEW_STRICT'] = true;
			}
			if ($Element->getAttribute('show_hiddens')=='yes')
				$old_upv = $GLOBALS['DBC']->usePublicVersion(FALSE);
			if ($Element->getAttribute('async')=='yes')
				$replacement = new DOMText($tag->asyncBlock(true));
			else if (!$GLOBALS['BACKEND'] && ($tag->USER->checkRights(CUser::MODERATOR) || $name=='basket'))
				$replacement = new DOMText($tag->asyncBlock(false));
		}
		if (is_null($replacement))
			$replacement = $tag->_replacement();
		
		if (isset($old_strict))
			$GLOBALS['VIEW_STRICT'] = $old_strict;
		if (isset($old_upv))
			$GLOBALS['DBC']->usePublicVersion($old_upv);
		
		$Element->parentNode->replaceChild($replacement, $Element);
	}

	public function __construct(DOMElement &$Element, array &$Vars, array &$Params)
	{
		$this->Element = $Element;
		$this->Vars = $Vars;
		$this->Params = $Params;
		$this->checkAttributes();
	}

	private function checkAttributes()
	{
		foreach ($this->Element->attributes as $attribute)
			$this->Element->setAttribute($attribute->name, $this->checkExpr($attribute->value));
//			$this->Element->setAttribute(
//				$attribute->name,
//				str_replace('"', '&quot;', $this->checkExpr($attribute->value))
//			);
	}

	protected function _replacement()
	{
		return null;
	}

	/*private function getAV($aname, $vname1, $vname2 = NULL)
	{
		$a = NULL;

		switch ($aname)
		{
			case 'GET': $a =& $_GET; break;
			case 'POST': $a =& $_POST; break;
			case 'COOKIE': $a =& $_COOKIE; break;
			case 'SERVER': $a =& $_SERVER; break;
			case 'SESSION': $a =& $_SESSION; break;
			case 'USER_VARS': $a =& $GLOBALS['UserVars']; break;
			case 'VARS': $a =& $GLOBALS['Vars']; break;
			case 'USER': $a =& $GLOBALS['USER']->Vars; break;
		}

		if (is_null($a))
			return '';

		if (empty($vname2))
			return $a[$vname1];
		else
			return $a[$vname1][$vname2];
	}

	private function checkExprReplace($Find, &$Src, &$Expr) {
		//$Expr =  @preg_replace("/\{$Find((\[[\'\"][^\'\"]+[\'\"]\])+)\}/eui", '$Src$1', $Expr);
		$Expr =  @preg_replace("/\{$Find\[[\'\"]([^\'\"]+)[\'\"]\]\[[\'\"]([^\'\"]+)[\'\"]\]\}/eui",
			'$Src[\'$1\'][\'$2\']', $Expr);
		$Expr =  @preg_replace("/\{$Find\[[\'\"]([^\'\"]+)[\'\"]\]\}/eui", '$Src[\'$1\']', $Expr);
	}
	
	protected function checkExpr($Expr) {
		if (strpos($Expr, '{')===FALSE && strpos($Expr, 'VARS')===FALSE)
			return $Expr;

//		$Expr =  @preg_replace("/\{(\w+)\[[\'\"]([^\'\"]+)[\'\"]\](\[[\'\"]([^\'\"]+)[\'\"]\])?\}/eui",
//			'$this->getAV(\'$1\', \'$2\', \'$4\')', $Expr);
		
		$this->checkExprReplace('GET', $_GET, $Expr);
		$this->checkExprReplace('POST', $_POST, $Expr);
		$this->checkExprReplace('COOKIE', $_COOKIE, $Expr);
		$this->checkExprReplace('SERVER', $_SERVER, $Expr);
		$this->checkExprReplace('SESSION', $_SESSION, $Expr);
		$this->checkExprReplace('USER_VARS', $GLOBALS['UserVars'], $Expr);
		$this->checkExprReplace('VARS', $GLOBALS['Vars'], $Expr);
		$this->checkExprReplace('USER', $GLOBALS['USER']->Vars, $Expr);

		$Expr = str_replace('USER_VARS', '$GLOBALS[\'UserVars\']', $Expr);
		$Expr = str_replace('VARS', '$GLOBALS[\'Vars\']', $Expr);
		$Expr = str_replace('{UID}', uniqid(), $Expr);
		$Expr = str_replace('{PAGE}', $GLOBALS['REQUEST_PAGE'], $Expr);
		if (isset($GLOBALS['PARENT_TEMPLATE_SRC']))
			$Expr = str_replace('{PARENT_TEMPLATE_SRC}', $GLOBALS['PARENT_TEMPLATE_SRC'], $Expr);
		if (isset($GLOBALS['PARENT_TEMPLATE_SRC_DEEP']))
			$Expr = str_replace('{PARENT_TEMPLATE_SRC_DEEP}', $GLOBALS['PARENT_TEMPLATE_SRC_DEEP'], $Expr);
		
		if (COMPATIBILITY_MODE=='true') {
			$Expr = str_replace('UID', uniqid(), $Expr);
			$Expr = str_replace('{DB_PUBLIC_VERSION}', '0000-00-00 00:00:00', $Expr);
                }
		
		$Expr = str_replace('{TEMPLATE}', $this->Params['template'], $Expr);
		if (isset($this->Params['i'])) $Expr = str_replace('{N}', $this->Params['i'], $Expr);
		if (isset($this->Params['i'])) $Expr = str_replace('{I}', $this->Params['i'], $Expr);
		if (isset($this->Params['f'])) $Expr = str_replace('{FIRST}', $this->Params['f'], $Expr);
		if (isset($this->Params['l'])) $Expr = str_replace('{LAST}', $this->Params['l'], $Expr);
		if (isset($this->Params['c'])) $Expr = str_replace('{COUNT}', $this->Params['c'], $Expr);

		preg_match_all('/\{([\w\d\.]+)\}/u', $Expr, $matches);
			for ($i=0; $i<count($matches[0]); $i++) if (defined($matches[1][$i]))
				$Expr = str_replace($matches[0][$i], constant($matches[1][$i]), $Expr);
		
		return $Expr;
	}*/

	protected function checkExpr($expr)
	{
//		tstart();
//		echo 'before: ', $expr, '<br />';
		$r = '';
		$null = NULL;

		for ( $lps = 0; ($ps = strpos($expr, '{', $lps)) !== FALSE; $lps = $pe )
		{
			$pe = strpos($expr, '}', $ps);
			if ($pe === FALSE)
			{
				$pe = $ps+1;
				continue;
			}
			else
				++$pe;
//			echo $lps, ' ', $ps, ' ', $pe, '<br />';

			$buf = substr($expr, $ps+1, $pe-$ps-2);
//			echo $buf, '<br />';
			$r .= substr($expr, $lps, $ps-$lps);
			$no = false;

			switch ($buf)
			{
				case 'TEMPLATE': $r .= @$this->Params['template']; break;
				case 'N': $r .= @$this->Params['i']; break;
				case 'I': $r .= @$this->Params['i']; break;
				case 'FIRST': $r .= @$this->Params['f']; break;
				case 'LAST': $r .= @$this->Params['l']; break;
				case 'COUNT': $r .= @$this->Params['c']; break;
				case 'DB_PUBLIC_VERSION': $r .= '0000-00-00 00:00:00'; break;
				case 'PARENT_TEMPLATE_SRC': @$r .= $GLOBALS['PARENT_TEMPLATE_SRC']; break;
				case 'PARENT_TEMPLATE_SRC_DEEP': @$r .= $GLOBALS['PARENT_TEMPLATE_SRC_DEEP']; break;
				case 'PAGE': $r .= @$GLOBALS['REQUEST_PAGE']; break;
				case 'UID': $r .= uniqid(); break;
				default:
					if (defined($buf))
					{
						$r .= constant($buf);
						break;
					}

					$pbs1 = strpos($buf, '[');
					if ($pbs1 !== FALSE)
					{
//						echo substr($buf, 0, $pbs1), '<br />';
						switch (substr($buf, 0, $pbs1))
						{
							case 'VARS': $a =& $GLOBALS['Vars']; break;
							case 'USER_VARS': $a =& $GLOBALS['UserVars']; break;
							case 'USER': $a =& $GLOBALS['USER']->Vars; break;
							case 'GET': $a =& $_GET; break;
							case 'POST': $a =& $_POST; break;
							case 'COOKIE': $a =& $_COOKIE; break;
							case 'SERVER': $a =& $_SERVER; break;
							case 'SESSION': $a =& $_SESSION; break;
							default: $a =& $null; break;
						}

						if (!is_null($a))
						{
							$pbe1 = strpos($buf, ']', $pbs1);
//							echo $pbs1, ' ', $pbe1, '<br />';
							if ($pbe1 !== FALSE)
							{
								$pbs2 = strpos($buf, '[', $pbe1);
								if ($pbs2 !== FALSE)
								{
									$pbe2 = strpos($buf, ']', $pbs2);
									if ($pbe2 !== FALSE)
									{
										$r .= @$a[substr($buf, $pbs1+2, $pbe1-$pbs1-3)]
											[substr($buf, $pbs2+2, $pbe2-$pbs2-3)];
										break;
									}
								}
								else
								{
//									echo substr($buf, $pbs1+2, $pbe1-$pbs1-3), '<br />';
									$r .= @$a[substr($buf, $pbs1+2, $pbe1-$pbs1-3)];
									break;
								}
							}
						}
					}

					$r .= '{';
					$pe = $ps+1;
					break;
			}
		}

		$r .= substr($expr, $lps, strlen($expr)-$lps);

		$r = str_replace('USER_VARS', '$GLOBALS[\'UserVars\']', $r);
		$r = str_replace('VARS', '$GLOBALS[\'Vars\']', $r);
//		echo 'after: ', $r, '<br /><br />';
//		tstop1();
		return $r;
	}
	
	private function asyncBlock($Load) {
		list($name, $src, $xml) = $this->TEMPLATES->templateInfo($this->Element->getAttribute('template'));
		if (is_null($src)) list($name2, $src, $xml) = $this->TEMPLATES->templateInfo($this->Params['template']);
		$src = $this->TEMPLATES->getRootRelativeSrc($src);
		$this->Element->setAttribute('template', $name.'@'.$src);
		
		$this->Element->removeAttribute('async');
		switch (strtolower($this->Element->localName)) {
			case 'block': case 'siblings': case 'childrens': case 'main':
				if (!$this->Element->hasAttribute('id') && !$this->Element->hasAttribute('link'))
					$this->Element->setAttribute('id',
						isset($this->Vars['id']) ? $this->Vars['id'] : '');
				break;
		}
		$buf = 'xml:<T:'.$this->Element->localName.' xmlns:T="/templates/ns"';
		foreach ($this->Element->attributes as $attr) $buf .= ' '.$attr->name.'="'.$attr->value.'"';
		$buf .= ' />';
		$domId = uniqid('id');
		return '<div style="display: inline;" id="'.$domId.'" link="'
			.'/cms/ajax'
			//.(isset($this->Vars['link']) ? $this->Vars['link'] : '')
			.($GLOBALS['REQUEST_PAGE']>1 ? '/page/'.$GLOBALS['REQUEST_PAGE'] : '')
			.'" block_var_id="'.(isset($this->Vars['id']) ? $this->Vars['id'] : '')
			.'" template="'.(htmlspecialchars($buf)).'"> '
			.($Load ? '' : DOMElementInnerXML($this->_replacement())).'</div>'
			.($Load ? '<script type="text/javascript">Load("'.$domId.'");</script>' : '');
        }

}









class CTagHTML extends CTag
{

	protected function _replacement()
	{
		$buf = '<'.$this->Element->localName;
		foreach ($this->Element->attributes as $attribute)
			$buf .= ' '.$attribute->name.'="'.$attribute->value.'"';
		$buf .= '>'.CCore::innerXML($this->Element).'</'.$this->Element->localName.'>';
		$r = $this->Element->ownerDocument->createDocumentFragment();
		$r->appendXML($buf);
		return $r;
	}
	
}









class CTagSelect extends CTag {
	
	private function emptyBlock($Template, &$Vars, &$Params) {
		if ($this->USER->checkRights(CUser::MODERATOR) && !$GLOBALS['VIEW_STRICT'] && !$GLOBALS['BACKEND']) {
			$vars = array('link'=>$Vars['link'].'/'.uniqid('id'), 'create_time'=>'NOW',
				'title'=>'Новая Запись');
			if (isset($Vars['id'])) $vars['parent.id'] = $Vars['id'];
		} else $vars = array();
		$Params['f'] = 0;
		$Params['l'] = 0;
		$Params['c'] = 1;
		$Params['i'] = 0;
		return $this->CORE->build($Template, $vars, $Params);
	}
/*
	private function getBasket($ResultPriceEval, $Length) {
		if (!isset($_COOKIE['B'])) return array(false, 0, 0, 0);
		$result = array();
		$c = count($_COOKIE['B']);
		if ($ResultPriceEval!='') $result_price = EvalCode($ResultPriceEval);
		else {
			$result_price = 0;
			foreach ($_COOKIE['B'] as $b) {
				$a = explode(';', $b);
				$result_price += $a[0]*$a[1];
			}
		}
		foreach ($_COOKIE['B'] as $id=>$b) {
			$a = explode(';', $b);
			$o['price'] = $a[0];
			$o['num'] = $a[1];
			$o['param'] = $a[2];
			$o['result_num'] = $c;
			$o['result_price'] = $result_price;
			$vars = $this->DBC->getVarsById($id);
			$result[] = $vars + $o;
		}
		
		$f = $Length!='' ? ($Page-1)*$Length : 0;
		$l = $c-$f < $Length || $Length=='' ? $f+$c-$f-1 : $f+$Length-1;

		return array($result, $f, $l, $c);
	}
*/
	private function getBasket($ResultPriceEval, $Length) {
		if (!isset($_COOKIE['B']))
			return array(false, 0, 0, 0);

		$r = array();
		$c = count($_COOKIE['B']);
		if ($ResultPriceEval!='')
			$result_price = EvalCode($ResultPriceEval);
		else
		{
			$result_price = 0;
			foreach ($_COOKIE['B'] as $b)
			{
				$a = explode(';', $b);
				$result_price += $a[0]*$a[1];
			}
		}

		foreach ($_COOKIE['B'] as $id=>$b)
		{
			$a = explode(';', $b);
			$o['price'] = $a[0];
			$o['num'] = $a[1];
			$o['param'] = $a[2];
			$o['result_num'] = $c;
			$o['result_price'] = $result_price;
			$vars = $this->DBC->getVarsById($id);
			if (!empty($vars))
				$r[] = $vars + $o;
		}

		$Page = isset($GLOBALS['REQUEST_PAGE']) ? $GLOBALS['REQUEST_PAGE'] : 1;
		if ($Length!='')
		{
			$f = ($Page-1)*$Length;
			$l = min($c-1, $f+$Length-1);
			$r = array_slice($r, $f, $Length);
		}
		else
		{
			$f = 0;
			$l = $c-1;
		}
		
		return array($r, $f, $l, $c);
	}

	protected function _replacement() {
		$result = '';
		
		if ($this->Element->hasAttribute('template')) {
			$Template = $this->Element->getAttribute('template');
			$template = $this->TEMPLATES->getTemplateElement($Template, $this->Params['template']);

			if ($this->Element->hasAttribute('required_vars')) {
				$templateRequiredVars = array('vars'=>array(), 'parent'=>array(), 'external'=>array());
				$rv = explode(',', str_replace(' ', '', $this->Element->getAttribute('required_vars')));
				foreach ($rv as $var) {
					$var = trim($var);
					if (substr($var, 0, 7)=='parent.')
						$templateRequiredVars['parent'][] = substr($var, 7);
					else if (substr($var, 0, 9)=='external.')
						$templateRequiredVars['external'][] = substr($var, 9);
					else $templateRequiredVars['vars'][] = $var;
				}
			} else $templateRequiredVars = $this->CORE->getRequiredVars($template);
			if ($templateRequiredVars) {
				foreach ($GLOBALS['CMS_REQUIRED_VARS'] as $var)
					if (!in_array($var, $templateRequiredVars['vars']))
						$templateRequiredVars['vars'][] = $var;
			} else {
				$templateRequiredVars = array('vars'=>$GLOBALS['CMS_REQUIRED_VARS'],
					'parent'=>array(), 'external'=>array());
			}
		} else {
			$Template = null;
			$templateRequiredVars = array('vars'=>array('id'), 'parent'=>array(), 'external'=>array());
		}
		$params = array('template'=>$Template, 'element'=>$this->Element);

		if ($this->Element->hasAttribute('id'))
			$vars = $this->DBC->getVarsById($this->Element->getAttribute('id'));
		else if ($this->Element->hasAttribute('link'))
			$vars = $this->DBC->getVars($this->Element->getAttribute('link'));
		else $vars =& $this->Vars;
		
		$parentBuf = array();
		$canBufParent = false;

		switch ($this->Element->localName) {
			case 'block':
				if (!$vars) {/////////////////!!!!!!!!!!!!!!!!!!!!!!
					$result = $this->emptyBlock($template, $vars, $params);
					break;
                                }
				if ($GLOBALS['BACKEND']) $templateRequiredVars = true;
				else $templateRequiredVars = $this->CORE->getRequiredVars($template, array_keys($vars));
				if ($templateRequiredVars) {
					$addVars = $this->DBC->getVarsById($vars['id'], $templateRequiredVars);
					if ($addVars) foreach ($addVars as $i=>$v) $vars[$i] = $v;
				}
				if (!$vars) $vars = array();
				$params['f'] = 0;
				$params['l'] = 0;
				$params['c'] = 1;
				$params['i'] = 0;
				$result = $this->CORE->build($template, $vars, $params);
				break;
			case 'siblings':
				$canBufParent = true;
				$r = $this->DBC->getSiblings($vars, $templateRequiredVars,
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'));
				break;
			case 'childrens':
				$canBufParent = true;
				if ($vars) foreach ($vars as $i=>$v) $parentBuf['parent.'.$i] = $v;
				$r = $this->DBC->getChilds($vars, $templateRequiredVars, 
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'), $this->Element->getAttribute('deep'),
					false);
				break;
			case 'childrenscount':
				$result = $this->DBC->getChilds($vars, $templateRequiredVars, null, null, null,
					$this->Element->getAttribute('deep'), true);
				break;
			case 'assoc':
				$r = $this->DBC->getAssoc($vars, $templateRequiredVars, 
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'));
				break;
			case 'rassoc':
				if ($vars==$this->Vars) {
					unset($vars);
					$vars = null;
				} else $canBufParent = true;
				if ($canBufParent && $vars) foreach ($vars as $i=>$v) $parentBuf['parent.'.$i] = $v;
				$r = $this->DBC->getRAssoc($vars, $templateRequiredVars,
					$this->Element->hasAttribute('foreign_id')
						? $this->Element->getAttribute('foreign_id')
						: (isset($this->Vars['id']) ? $this->Vars['id'] : null),
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'));
				break;
			case 'dassoc':
				if ($vars==$this->Vars) {
					unset($vars);
					$vars = null;
				} else $canBufParent = true;
				if ($canBufParent && $vars) foreach ($vars as $i=>$v) $parentBuf['parent.'.$i] = $v;
				$r = $this->DBC->getDAssoc($vars, $templateRequiredVars,
					$this->Element->hasAttribute('direct_id')
						? $this->Element->getAttribute('direct_id') 
						: (isset($this->Vars['id']) ? $this->Vars['id'] : null),
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'));
				break;
			case 'parent':
				$r = $this->DBC->getParent($vars, $templateRequiredVars,
					$this->Element->getAttribute('deep')=='' ? 1
					: $this->Element->getAttribute('deep'));
				break;
			case 'select':
				if ($this->Element->getAttribute('can_insert')=='yes') $canBufParent = true;
				$r = $this->DBC->select($this->Element->getAttribute('sql'),
					$this->Element->getAttribute('xpath'), 	$this->Element->getAttribute('orderby'),
					$this->Element->getAttribute('length'), $this->Element->getAttribute('page'),
					$this->Element, $this->Element->getAttribute('db_file'));
				break;
			case 'rawquery':
				$r = $this->DBC->rawQuery($this->Element->getAttribute('sql'),
					$this->Element->getAttribute('xpath'), $this->Element->getAttribute('orderby'),
					$this->Element->getAttribute('db_file'));
				break;
			case 'logquery':
				$r = $this->LOG->query($this->Element->getAttribute('sql'), 
					$this->Element->getAttribute('xpath'), $this->Element->getAttribute('orderby'));
				break;
			case 'search':
				$query = $this->Element->getAttribute('query');
				$words = array(2);
				$words[0] = explode(' ', $query);
				foreach ($words[0] as $i=>$word) {
					$words[0][$i] = '/('.$word.')/iu';
					$words[1][$i] = '<strong>$1</strong>';
				}
				$r = $this->DBC->search($templateRequiredVars, $query,
					$this->Element->getAttribute('length'), $this->Element->getAttribute('page'),
					$this->Element);
				break;
			case 'basket':
				$GLOBALS['HEAD_ADD'] .=
					'<script type="text/javascript" src="/cms/js/basket.js"> </script>';
				$r = $this->getBasket($this->checkExpr($this->Element->getAttribute('result_price')),
					$this->Element->getAttribute('length'));
				break;
			case 'user': case 'users':
				$r = $this->DBC->getUsers($this->Element->getAttribute('id'),
					$this->Element->getAttribute('group_id'),
					$this->Element->getAttribute('orderby'), $this->Element->getAttribute('length'),
					$this->Element->getAttribute('page'), $this->Element);
				break;
                }

		if (isset($r)) {
			list($r, $f, $l, $c) = $r;
			if ($c==0) $result = $this->emptyBlock($template, $vars, $params);
			else if ($f>=$c) $this->CORE->HTTPReturn(404);
			else for ($i=$f; $i<=$l && $c>0; $i++) {
				$t = $template->cloneNode(true);
				if (is_array($r)) $vars =& $r[$i-$f];
				else $vars = $this->DBC->getRow($r, $i, $f);
				if (!$vars) {
					if ($l+1 < $c) $l++;
					continue;
                                }
				if ($canBufParent && $vars) $vars = $parentBuf + $vars;
				if ($templateRequiredVars = $this->CORE->getRequiredVars($template, array_keys($vars))){
					if (isset($vars['id'])) {
						$buf = $this->DBC->getVarsById($vars['id'], $templateRequiredVars);
						if ($buf) $vars = $buf + $vars;
                                        }
					if ($canBufParent) foreach ($vars as $j=>$v) if (substr($j, 0, 7)=='parent.')
						$parentBuf[$j] = $v;
				}
				$params['f'] = $f;
				$params['l'] = $l;
				$params['c'] = $c;
				$params['i'] = $i;
				$result .= $this->CORE->build($t, $vars, $params);
			}
		}

		switch ($this->Element->localName) {
			case 'search':
				$result = preg_replace($words[0], $words[1], $result);
				break;
                }
		
		return new DOMText($result);
        }

}









class CTagFunction extends CTag {

	private function buildKeywordsFromTitle($Title) {
		$words = explode(' ', $Title);
		foreach ($words as $i=>$word) {
			$word = preg_replace('/[^А-Яа-я\w\d\-]/ui', '', $word);
			if (strlen($word)<=6) unset($words[$i]);
			else $words[$i] = $word;
		}
		return implode(', ', $words);
	}

	private function buildDescriptionFromDescription($Description) {
		$Description = trim(preg_replace('/\s+/u', ' ', strip_tags($Description)));
		if (preg_match('/^(.{150}).+$/u', $Description, $matches)) {
			$Description = $matches[1];
			$Description = preg_replace('/\s+[^\s]+$/u', '', $Description);
		}
		return htmlspecialchars($Description);
	}
	
	private function varImage(&$Str) {
		$Alt = $this->Element->getAttribute('alt');
		$Param = $this->Element->getAttribute('img');
		$d = new DOMDocument('1.0', 'UTF-8');
		$d->loadXML('<temp>'.$Str.'</temp>');
		$imgs = $d->getElementsByTagName('img');
		if ($imgs->length==0) return $Str;
		foreach ($imgs as $img) {
			$url = parse_url($img->getAttribute('src'), PHP_URL_PATH);
			$alt = $Alt!='' ? $Alt : $img->getAttribute('alt');
			$_params = explode(';', $Param);
			$params = explode(',', preg_replace('/\s/u', '', $_params[0]));
			list($w, $h) = explode('x', $params[0]);
			if (isset($params[1])) {$showBig = true; list($w2, $h2) = explode('x', $params[1]);}
			else $showBig = false;
			$newImg = $d->createElement('img');
			$newImg->setAttribute('src', '/cms/fb/vi?w='.$w.'&amp;h='.$h.(isset($_params[1])
				? '&amp;m='.$_params[1] : '').'&amp;url='.$url);
			$newImg->setAttribute('alt', str_replace(array('&', '"'), array('&amp;', ''), $alt));
			
			foreach ($img->attributes as $a)
				if ($a->name!='src' && $a->name!='alt' && $a->name!='style')
					$newImg->setAttribute($a->name, $a->value);

			if ($showBig) {
				$newImg->setAttribute('style', 'cursor: pointer;');
				$newImg->setAttribute('onclick',
					'window.open(\'/cms/includes/ImageSizing.html?/cms/fb/vi?w='.$w2.'&amp;h='.$h2
						.(isset($_params[1]) ? '&amp;m='.$_params[1] : '').'&amp;url='.$url
						.'\', \'_blank\', \'menubar=0,status=0,resizable=0,scrollbars=0'
						.($w2!='*' ? ',width='.($w2+100) : '')
						.($h2!='*' ? ',height='.($h2+30) : '').'\');');
			}
			$img->parentNode->replaceChild($newImg, $img);
		}
		$Str = DOMElementInnerXML($d->documentElement);
	}
	
	private function varDate(&$Str) {
		$Str = date($this->Element->getAttribute('date'), strtotime($Str));
	}
	
	private function checkAsyncHref($Url) {
		return $_SERVER['REQUEST_URI']=='/cms/ajax'
			? 'javascript:Load(\''.$_POST['DomId'].'\',\''.$Url.'\')' : $Url;
	}
	
	private function varContentEditable(&$Str) {
		$DefaultText = $this->Element->getAttribute('default');
		if ($DefaultText=='') switch ($this->Element->getAttribute('name')) {
			case 'title': $DefaultText = LNG_TITLE; break;
			case 'image': $DefaultText = LNG_IMAGE; break;
			case 'description': $DefaultText = LNG_DESCRIPTION; break;
			case 'content': $DefaultText = LNG_CONTENT; break;
			default: $DefaultText = DEFAULT_TEXT; break;
		}
		if ($Str=='') $Str = $DefaultText;
		$domId = uniqid('id');
		$Str = '<div class="cmsEditable" edit="no" id="'.$domId.'" var_default_text="'.$DefaultText.'" var_id="'
			.(isset($this->Vars['id']) ? $this->Vars['id'] : '').'" var_link="'
			.(isset($this->Vars['link']) ? $this->Vars['link'] : '').'" var_version="'
			.(isset($this->Vars['version']) ? $this->Vars['version'] : '').'" var_create_time="'
			.(isset($this->Vars['create_time']) ? $this->Vars['create_time'] : '').'" var_publish_time="'
			.(isset($this->Vars['publish_time']) ? $this->Vars['publish_time'] : '').'" server_time="'
			.date('Y-m-d H:i:s').'" var_n="'
			.(isset($this->Vars['n']) ? $this->Vars['n'] : '').'" var_parent_id="'
			.(isset($this->Vars['parent.id']) ? $this->Vars['parent.id'] 
				: (isset($this->Vars['parent_id']) ? $this->Vars['parent_id'] : '')).'" var_name="'
			.$this->Element->getAttribute('name').'" param_orderby="'
			.$this->Element->getAttribute('orderby').'"'
			.($this->Element->hasAttribute('img') ? ' param_img="'.$this->Element->getAttribute('img').'"'
				: '')
			.'>'.$Str.'</div>'
			.'<script type="text/javascript">cmsAddEditor("'.$domId.'");</script>';
        }

	private function varBackendEditable(&$Str) {
		if ($Str=='') $Str = ' ';
		$d = new DOMDocument('1.0', 'UTF-8');
		$e = $d->createElement('temp');
		$d->appendChild($e);
		$f = $d->createDocumentFragment();
		$f->appendXML($Str);
		$e->appendChild($f);
		
		while (($elements = $e->getElementsByTagNameNS('/templates/ns', '*')) && $elements->length>0) {
			$el = $elements->item(0);
			$buf = '<img class="cmsBackendBlock" src="/cms/img/toolicons/blockformat.gif" title="'
				.htmlspecialchars($d->saveXML($el)).'" />';
			$el->parentNode->replaceChild(new DOMText($buf), $el);
                }
		$Str = $d->saveXML($e);
        }

	private function getVar() {
		if ($this->Element->hasAttribute('name')) {
			$name = $this->Element->getAttribute('name');
			if (isset($this->Vars[$name])) return $this->Vars[$name];
			else if (isset($GLOBALS['UserVars'][$name])) return $GLOBALS['UserVars'][$name];
			else return null;
                } else if ($this->Element->hasAttribute('user')) {
			return $this->USER->getVar($this->Element->getAttribute('user'));
		} else if ($this->Element->hasAttribute('post')) {
			$name = $this->Element->getAttribute('post');
			return isset($_POST[$name]) ? $_POST[$name] : null;
		} else if ($this->Element->hasAttribute('get')) {
			$name = $this->Element->getAttribute('get');
			return isset($_GET[$name]) ? $_GET[$name] : null;
		} else if ($this->Element->hasAttribute('cookie')) {
			$name = $this->Element->getAttribute('cookie');
			return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
		} else if ($this->Element->hasAttribute('session')) {
			$name = $this->Element->getAttribute('session');
			return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
		}
	}

	protected function _replacement() {
		$r = new DOMText('');
		switch ($this->Element->localName) {
			case 'head':
				$d = new DOMDocument('1.0', 'UTF-8');
				$d->loadXML('<temp>'.(array_key_exists('head', $this->Vars) ? $this->Vars['head'] : '')
					.'</temp>');

				$eTitle = null;
				$eKeywords = null;
				$eDescription = null;
				$eScriptAjax = null;

				$buf = $d->getElementsByTagName('title');
				if ($buf->length>0) $eTitle = $buf->item(0);
				$buf = $d->getElementsByTagName('meta');
				for ($i=0; $i<$buf->length; $i++) switch ($buf->item($i)->getAttribute('name')) {
					case 'keywords': $eKeywords = $buf->item($i); break;
					case 'description': $eDescription = $buf->item($i); break;
				}
				$buf = $d->getElementsByTagName('script');
				for ($i=0; $i<$buf->length; $i++)
					if ($buf->item($i)->getAttribute('src')=='/cms/js/ajax.js')
						$eScriptAjax = $buf->item($i);
				if (is_null($eScriptAjax)) {
					$eScriptAjax = $d->createElement('script');
					$eScriptAjax->setAttribute('type', 'text/javascript');
					$eScriptAjax->setAttribute('src', '/cms/js/ajax.js');
					$d->documentElement->appendChild($eScriptAjax);
					$eScriptAjax->appendChild(new DOMText(' '));
				}

				if (array_key_exists('use_content_in_head', $this->Vars)
					&& ($this->Vars['use_content_in_head']=='path'
						|| $this->Vars['use_content_in_head']=='true')) {
					if (!is_null($eTitle)) $d->documentElement->removeChild($eTitle);
					$eTitle = $d->createElement('title');
					$d->documentElement->appendChild($eTitle);
					if (is_null($eKeywords)) {
						$eKeywords = $d->createElement('meta');
						$eKeywords->setAttribute('name', 'keywords');
						$d->documentElement->appendChild($eKeywords);
					}
					if (is_null($eDescription) && isset($this->Vars['description'])
						&& $this->Vars['description']!='') {
						$eDescription = $d->createElement('meta');
						$eDescription->setAttribute('name', 'description');
						$d->documentElement->appendChild($eDescription);
					}
					if ($this->Vars['use_content_in_head']=='true')
						$eTitle->appendChild(new DOMText($this->Vars['title']));
					else {
						$old = $GLOBALS['VIEW_STRICT'];
						$GLOBALS['VIEW_STRICT'] = true;
						$eTitle->appendChild(new DOMText(str_replace('&amp;', '&', Build(
							'title_path@cms/templates/default.xml', $this->Vars))));
						$GLOBALS['VIEW_STRICT'] = $old;
					}
					if (!is_null($eKeywords))
						$eKeywords->setAttribute('content',
							substr(isset($this->Vars['keywords']) 
								&& $this->Vars['keywords']!=''
								? trim(preg_replace('/\s+/u', ' ',
									strip_tags($this->Vars['keywords'])))
								: $this->buildKeywordsFromTitle($this->Vars['title']),
									0, 2000));
					if (!is_null($eDescription))
						$eDescription->setAttribute('content',
							$this->buildDescriptionFromDescription(
								$this->Vars['description']));
				}
				$r = $this->Element->ownerDocument->importNode($d->documentElement, true);
				break;
			case 'main':
				$mainCash = $this->CORE->checkCache('main', $this->Vars);
				if (!is_bool($mainCash)) return new DOMText($mainCash);
				
				if ($this->Element->hasAttribute('id') && (!isset($this->Vars['id'])
					|| $this->Element->getAttribute('id')!=$this->Vars['id']))
					$vars = $this->DBC->getVarsById($this->Element->getAttribute('id'));
				else $vars =& $this->Vars;

				list($GLOBALS['PARENT_TEMPLATE_SRC'], $GLOBALS['PARENT_TEMPLATE_SRC_DEEP'])
					= $this->DBC->getParentTemplateSrc($vars);
				$template = $this->TEMPLATES->getTemplateElement($GLOBALS['PARENT_TEMPLATE_SRC']);
				
				if ($templateRequiredVars = $this->CORE->getRequiredVars($template, array_keys($vars))) {
					$addVars = $this->DBC->getVarsById($vars['id'], $templateRequiredVars);
					foreach ($addVars as $i=>$v) $vars[$i] = $v;
				}
				
				$buf = $this->CORE->build($template, $vars, $this->Params);
				if ($mainCash===true)
					$this->CORE->saveCache($vars, html_entity_decode($buf, null, 'utf-8'));
				$r = new DOMText($buf);
				break;
			case 'first':
				if ($this->Params['i']==$this->Params['f']) {
					$r = $this->Element->ownerDocument->createDocumentFragment();
					$r->appendXML(CCore::innerXML($this->Element));
				}
				break;
			case 'inner':
				if ($this->Params['i']>$this->Params['f'] && $this->Params['i']<$this->Params['l']) {
					$r = $this->Element->ownerDocument->createDocumentFragment();
					$r->appendXML(CCore::innerXML($this->Element));
				}
				break;
			case 'last':
				if ($this->Params['i']==$this->Params['l']) {
					$r = $this->Element->ownerDocument->createDocumentFragment();
					$r->appendXML(CCore::innerXML($this->Element));
				}
				break;
			case 'even':
				if ($this->Params['i'] % 2 == 1) {
					$r = $this->Element->ownerDocument->createDocumentFragment();
					$r->appendXML(CCore::innerXML($this->Element));
				}
				break;
			case 'odd':
				if ($this->Params['i'] % 2 == 0) {
					$r = $this->Element->ownerDocument->createDocumentFragment();
					$r->appendXML(CCore::innerXML($this->Element));
				}
				break;
			case 'empty': break;
			case 'var':
				$r = $this->Element->ownerDocument->createDocumentFragment();
				$buf = $this->getVar();
				if ($this->Element->getAttribute('striptags')=='yes'
					|| $this->Element->getAttribute('astext')=='yes') $buf = strip_tags($buf);
				if ($this->Element->getAttribute('htmlspecialchars')=='yes'
					|| $this->Element->getAttribute('tovalue')=='yes'
					|| $this->Element->getAttribute('astext')=='yes')
					$buf = htmlspecialchars(htmlspecialchars(str_replace('&amp;amp;', '&', $buf)));
				if ($this->Element->getAttribute('nowrap')=='yes'
					|| $this->Element->getAttribute('tovalue')=='yes')
					$buf = preg_replace('/\s+/u', ' ', $buf);
				$buf = trim($buf);
				if ($this->Element->hasAttribute('img')) $this->varImage($buf);
				if ($this->Element->hasAttribute('date')) $this->varDate($buf);
				if ($this->Element->getAttribute('editable')=='yes'
					&& !$GLOBALS['VIEW_STRICT'] && !$GLOBALS['BACKEND']
					&& $this->USER->checkRights(CUser::MODERATOR)) $this->varContentEditable($buf);
				if ($this->Element->getAttribute('backend')=='yes') $this->varBackendEditable($buf);
				@$r->appendXML($buf);
				break;
			case 'eval':
				$ss = $this->Element->getElementsByTagNameNS('/templates/ns', 'statement');
				if ($ss->length==1) $eval = $ss->item(0)->textContent;
				else $eval = $this->Element->textContent;
				EvalCode($this->checkExpr($eval), $buf);
				$r = new DOMText($buf);
				break;
			case 'setvar':
				if (preg_match('/^(\w+)((\[.*\])+)$/u', $this->Element->getAttribute('name'), $matches))
					$name = '["'.$matches[1].'"]'.$matches[2];
				else $name = '["'.$this->Element->getAttribute('name').'"]';
				EvalCode('$GLOBALS["UserVars"]'.$name.' = "'
					.$this->Element->getAttribute('value').'";');
				break;
			case 'if':
				$r = $this->Element->ownerDocument->createDocumentFragment();
				$then = $this->Element->getElementsByTagName('then');
				if ($then->length==0) {
					if (EvalCode('return (bool)('.$this->Element->getAttribute('expr').');'))
						$r->appendXML(CCore::innerXML($this->Element));
				} else {
					if (EvalCode('return (bool)('.$this->Element->getAttribute('expr').');'))
						$r->appendXML(CCore::innerXML(
							$this->Element->getElementsByTagName('then')->item(0)));
					else $r->appendXML(CCore::innerXML(
						$this->Element->getElementsByTagName('else')->item(0)));
				}
				break;
			case 'rule':
				if (!$this->Params['element']->hasAttribute('length') ||
					$this->Params['element']->getAttribute('length')=='') break;
				else $length = (int)$this->Params['element']->getAttribute('length');
				$buf = '';
				$rule_length = !$this->Element->hasAttribute('length') ? 7 
					: (int)$this->Element->getAttribute('length');
				$count_pages = ceil($this->Params['c']/$length);

				if ($count_pages<=1) break;
				else if ($count_pages<=$rule_length) $first_page = 1;
				else if ($GLOBALS['REQUEST_PAGE']<=(int)($rule_length/2)) $first_page = 1;
				else if ($GLOBALS['REQUEST_PAGE']>=$count_pages-(int)($rule_length/2))
					$first_page = $count_pages-$rule_length+1;
				else $first_page = $GLOBALS['REQUEST_PAGE']-(int)($rule_length/2);
				$last_page = min($count_pages, $first_page+$rule_length);

				$root_link = $_SERVER['REQUEST_URI']!='/' ? $_SERVER['REQUEST_URI'] : '';
				
				$buf .= $GLOBALS['REQUEST_PAGE']>1 ? '<a href="'.$this->checkAsyncHref($root_link
					.'/page/'.($GLOBALS['REQUEST_PAGE']-1).'?'.$_SERVER['QUERY_STRING'])
					.'">&#8592;</a>' : '<a>&#8592;</a>';
				if ($first_page>1) $buf .= ' <a href="'.$this->checkAsyncHref($root_link.'?'
					.$_SERVER['QUERY_STRING']).'">1</a>';
				if ($first_page>2) $buf .= ' ...';
				for ($i=$first_page; $i<=$last_page; $i++)
					$buf .= $i==$GLOBALS['REQUEST_PAGE'] ? ' <b>'.$i.'</b>' : ' <a href="'
						.$this->checkAsyncHref($root_link.($i != 1 ? '/page/'.$i : '').'?'
						.$_SERVER['QUERY_STRING']).'">'.$i.'</a>';
				if ($last_page<$count_pages-1) $buf .= ' ...';
				if ($last_page<$count_pages) $buf .= ' <a href="'.$this->checkAsyncHref($root_link
					.'/page/'.$count_pages.'?'.$_SERVER['QUERY_STRING']).'">'.$count_pages.'</a>';
				$buf .= $GLOBALS['REQUEST_PAGE']<$count_pages ? ' <a href="'
					.$this->checkAsyncHref($root_link.'/page/'.($GLOBALS['REQUEST_PAGE']+1).'?'
					.$_SERVER['QUERY_STRING']).'">&#8594;</a>' : ' <a>&#8594;</a>';
				$r = new DOMText($buf);
				break;
			case 'tohead':
				$old = $GLOBALS['VIEW_STRICT'];
				$GLOBALS['VIEW_STRICT'] = TRUE;
				$GLOBALS['HEAD_ADD'] .= $this->CORE->build($this->Element, $this->Vars, $this->Params);
				$GLOBALS['VIEW_STRICT'] = $old;
				break;
			case 'form':
				$GLOBALS['UserVars']['form.id'] = uniqid('f');
				$GLOBALS['UserVars']['form.code'] = $this->Element->getAttribute('code')==''
					? GeneratePassword(6) : $this->Element->getAttribute('code');
				$GLOBALS['UserVars']['form.code2'] = $this->Element->getAttribute('code2')=='yes'
					? GeneratePassword(32) : '';
				
				$this->DBC->formAdd($GLOBALS['UserVars']['form.id'],
					$GLOBALS['UserVars']['form.code'],
					$this->Element->getAttribute('redirect'),
					$this->checkExpr(CForm::checkForm($this)),
					$GLOBALS['UserVars']['form.code2']);
				
				$r = $this->Element->ownerDocument->createDocumentFragment();
				$r->appendXML('<form id="'.$GLOBALS['UserVars']['form.id']
					.($this->Element->hasAttribute('action')
						&& $this->Element->getAttribute('action')!='save'
						&& $this->Element->getAttribute('action')!='saveAndPublish'
						&& $this->Element->getAttribute('action')!='userSave'
						? '" action="'.$this->Element->getAttribute('action').'"'
						: '" action="/cms/form?id='.$GLOBALS['UserVars']['form.id'].'"')
					.($this->Element->hasAttribute('enctype') 
						? ' enctype="'.$this->Element->getAttribute('enctype').'"' : '')
					.($this->Element->hasAttribute('method')
						? ' method="'.$this->Element->getAttribute('method').'">'
						: ' method="post">')
					.CCore::innerXML($this->Element).'</form>');
				break;
			case 'a':
				$buf = '<a';
				if (!$this->Element->hasAttribute('href')) $buf .= ' href="'.$this->Vars['link'].'"';
				foreach ($this->Element->attributes as $attribute)
					$buf .= ' '.$attribute->name.'="'.$attribute->value.'"';
				$buf .= '>'.CCore::innerXML($this->Element).'</a>';
				$r = $this->Element->ownerDocument->createDocumentFragment();
				$r->appendXML($buf);
				break;
			case 'counteradd':
				$this->DBC->counterAdd($this->Element->getAttribute('id'));
				break;
			case 'counterget':
				$r = new DOMText((string)$this->DBC->counterGet($this->Element->getAttribute('id')));
				break;
			case 'counter':
				$this->DBC->counterAdd($this->Element->getAttribute('id'));
				$r = new DOMText((string)$this->DBC->counterGet($this->Element->getAttribute('id')));
				break;
                }
		return $r;
        }

}

?>
