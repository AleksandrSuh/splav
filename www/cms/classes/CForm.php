<?php

class CForm extends CCoreObject {
	
	public function image() {
		if (!isset($_GET['id'])) $this->CORE->HTTPReturn(404);
		
		$id = $_GET['id'];
		$buf = $this->DBC->formGet($id);
		$code = $buf['code'];
		
		header('Content-type: image/jpeg');
		$im = imagecreate(64, 26);
		$bgcolor = imagecolorallocate($im, rand(200, 255), rand(200, 255), rand(200, 255));
		$bordercolor = imagecolorallocate($im, rand(0, 200), rand(0, 200), rand(0, 200));
		$textcolor = imagecolorallocate($im, rand(0, 127), rand(0, 127), rand(0, 127));
		imagerectangle($im, 0, 0, 63, 25, $bordercolor);
		imagestring($im, 5, 5, 5, $code, $textcolor);
		imagejpeg($im, null, 15);
		imagedestroy($im);
		exit;
	}

	public function result($Result) {
		if ($Result!==0) $_POST = array();
		
		if (COMPATIBILITY_MODE=='true' && !isset($_POST['_a'])) {
			$newPOST = array('_a'=>array());
			$buf = array();
			foreach ($_POST as $i=>$v) if ($i!='Vars' && $i!='ExternalVars' && $i!='Rec') $buf[$i] = $v;
			
			if	(isset($_POST['Save']))			$buf['action'] = 'save';
			else if (isset($_POST['SaveAndPublish']))	$buf['action'] = 'saveAndPublish';
			else if (isset($_POST['Delete']))		$buf['action'] = 'delete';
			else if (isset($_POST['MoveTo']))		$buf['action'] = 'moveTo';
			else if (isset($_POST['MoveUp']))		$buf['action'] = 'moveUp';
			else if (isset($_POST['MoveDown']))		$buf['action'] = 'moveDown';
			else if (isset($_POST['MoveToFirst']))		$buf['action'] = 'moveToFirst';
			else if (isset($_POST['MoveToLast']))		$buf['action'] = 'moveToLast';
			else if (isset($_POST['Publish']))		$buf['action'] = 'publish';
			else if (isset($_POST['PublishChilds']))	$buf['action'] = 'publishChilds';
			else if (isset($_POST['DeleteChilds']))		$buf['action'] = 'deleteChilds';
			else if (isset($_POST['Hide']))			$buf['action'] = 'hide';
			else if (isset($_POST['BackUp']))		$buf['action'] = 'backup';
			else if (isset($_POST['AddAssoc']))		$buf['action'] = 'addAssoc';
			else if (isset($_POST['RemoveAssoc']))		$buf['action'] = 'removeAssoc';
			else if (isset($_POST['PublishAll']))		$buf['action'] = 'publishAll';
			else if (isset($_POST['UserSave']))		$buf['action'] = 'userSave';
			
			if (isset($_POST['Vars'])) {
				$newPOST['_a'][0] = $buf;
				if (isset($_POST['Vars']['id'])) {
					$newPOST['_a'][0]['id'] = $_POST['Vars']['id'];
					unset($_POST['Vars']['id']);
                                }
				$newPOST['_a'][0]['vars'] = $_POST['Vars'];
				if (isset($_POST['ExternalVars'])) foreach ($_POST['ExternalVars'] as $i2=>$v2)
					$newPOST['_a'][0]['vars']['external.'.$i2] = $v2;
                        } else if (isset($_POST['Rec'])) {
				foreach ($_POST['Rec'] as $i=>$v) {
					$newPOST['_a'][$i] = $buf;
					if (isset($v['vars']['id'])) {
						$newPOST['_a'][$i]['id'] = $v['vars']['id'];
						unset($v['vars']['id']);
					}
					$newPOST['_a'][$i]['vars'] = $v['vars'];
					if (isset($v['externalVars'])) foreach ($v['externalVars'] as $i2=>$v2)
						$newPOST['_a'][$i]['vars']['external.'.$i2] = $v2;
                                }
			}
			$_POST = $newPOST;
                }

		$_SERVER['HTTP_REFERER'] = preg_replace('/[\?\&]result\=[^\&]*/', '', $_SERVER['HTTP_REFERER']);
		$_SERVER['HTTP_REFERER'] = $_SERVER['HTTP_REFERER']
			.(strpos($_SERVER['HTTP_REFERER'], '?')===false ? '?' : '&').'result='.$Result;
		
		CCore::checkPost(isset($_GET['async']) && $_GET['async']=='yes'
			? CCore::CHECK_POST_RETURN : CCore::CHECK_POST_REDIRECT, $_SERVER['HTTP_REFERER']);
	}

	public function check() {
		if (!isset($_GET['id'])) return $this->result(-1);
		$id = $_GET['id'];
		$code = isset($_GET['code']) ? $_GET['code'] : '';

		$this->DBC->formClear();
		$buf = $this->DBC->formGet($id);
		$redirect = $buf['redirect'];
		$checkform = $buf['checkform'];
		$code2 = $buf['code2'];
		$post = $buf['post'];

		if ($redirect!='') $_SERVER['HTTP_REFERER'] = $redirect;
		if (is_null($checkform)) return $this->result(-1);
		if ($code==$code2) $this->DBC->formClear($id);
		if (!empty($post)) $_POST = unserialize($post);
		if ($code2!='' && $code==$code2) return $this->result(0);

		if (!isset($_COOKIE[session_name()])) session_start();
		$_SESSION['form'] = $_POST;

		$result = EvalCode($checkform);
		if (is_null($result)) $result = 0;
		
		if ($code!=$code2) {
			if (empty($post)) $this->DBC->formSavePost($id, serialize($_POST));
			$_POST = array();
                }
		
		return $this->result($result);
	}
	
	public static function checkForm($Form) {
		$r = '';
		
		$action = $Form->Element->getAttribute('action');
		if ($action=='save' || $action=='saveAndPublish' || $action=='userSave') {
			$r .= '$_POST[\'_a\'] = array(0=>array(\'vars\'=>array(\'create_time\'=>\'NOW\'))); '
				.'$_POST[\'_a\'][0][\'action\']=\''.$action
				.'\'; $_POST[\'_a\'][0][\'titleToLink\']=\'true\'; $_POST[\'_a\'][0][\'parent_id\']='
				.($Form->Element->hasAttribute('parent_id')
					? $Form->Element->getAttribute('parent_id') 
					: (isset($Form->Vars['id']) ? $Form->Vars['id'] : '\'\'')).";\n";
                }

		$ae = array();
		$es = $Form->Element->getElementsByTagNameNS('/templates/ns', 'input');
		foreach ($es as $e) $ae[] = $e;
		$es = $Form->Element->getElementsByTagNameNS('/templates/ns', 'textarea');
		foreach ($es as $e) $ae[] = $e;
		$es = $Form->Element->getElementsByTagNameNS('/templates/ns', 'select');
		foreach ($es as $e) if (!$e->hasAttribute('template')) $ae[] = $e;

		foreach ($ae as $e) {

		/*while (
			(($es = $Form->Element->getElementsByTagNameNS('/templates/ns', 'input')) && $es->length>0)
			|| (($es=$Form->Element->getElementsByTagNameNS('/templates/ns', 'textarea')) && $es->length>0)
			|| (($es=$Form->Element->getElementsByTagNameNS('/templates/ns', 'select')) && $es->length>0)
			) {
			$e = $es->item(0);
			if ($e->localName=='select' && $e->hasAttribute('template')) continue;*/
			
			$temptag = new CTag($e, $Form->Vars, $Form->Params);

			if ($e->getAttribute('required')=='yes') {
				$r .= 'if (preg_match(\'/^\s*$/\', $_POST[\''
					.$e->getAttribute('name').'\']) && !is_uploaded_file($_FILES[\''
						.$e->getAttribute('name')."']['tmp_name'])) return -2;\n";
				$e->removeAttribute('required');
			}
			if ($e->hasAttribute('max_file_size')) {
				$r .= 'if (filesize($_FILES[\''.$e->getAttribute('name')
					.'\'][\'tmp_name\'])>'.$e->getAttribute('max_file_size').") return -5;\n";
				$e->removeAttribute('required');
			}
			if ($e->localName=='textarea') {
				$r .= '$_POST[\''.$e->getAttribute('name')
					.'\'] = autoreplace(nl2br(stripslashes($_POST[\''
					.$e->getAttribute('name')."'])));\n";
			}
			switch ($e->getAttribute('type')) {
				case 'form_code':
					$r .= 'if ($_POST[\''.$e->getAttribute('name')
						."']!='{USER_VARS['form.code']}') return -3;\n";
					$e->setAttribute('type', 'text');
					break;
				case 'email':
				$r .= '$_POST[\''.$e->getAttribute('name').'\'] = trim($_POST[\''.$e->getAttribute('name').'\']); '
						.'if (!preg_match(\'/^[\w\d\-\_\.]+\@[\w\d\-\_\.]+\.[\w\d\-\_]+$/\', $_POST[\''
						.$e->getAttribute('name')."'])) return -4;\n";
					$e->setAttribute('type', 'text');
					break;
				case 'number':
					$r .= 'if (!preg_match(\'/^[\d\-]+$/\', $_POST[\''
						.$e->getAttribute('name')."'])) return -4;\n";
					$e->setAttribute('type', 'text');
					break;
				case 'upload_file': case 'upload_image':
					$ie = $e->ownerDocument->createElement('input');
					$ie->setAttribute('type', 'hidden');
					$ie->setAttribute('name', 'MAX_FILE_SIZE');
					$ie->setAttribute('value', $e->getAttribute('max_file_size'));
					$e->parentNode->insertBefore($ie, $e);
					$Form->Element->setAttribute('enctype', 'multipart/form-data');
					$r .= 'if (is_uploaded_file($_FILES[\''
						.$e->getAttribute('name').'\'][\'tmp_name\'])) {
$file =& $_FILES[\''.$e->getAttribute('name').'\'];
$fn = DIR_FB.\'/_fast/\'.date(\'Y/m/\').uniqid().\'_\'.basename($file[\'name\']);
$dirs = explode(\'/\', $fn);
$dir = DIR_ROOT;
for ($i=1; $i<count($dirs)-1; $i++) {$dir .= \'/\'.$dirs[$i]; if (!file_exists($dir)) mkdir($dir);}
move_uploaded_file($file[\'tmp_name\'], DIR_ROOT.$fn);
chmod(DIR_ROOT.$fn, 0644);';
					switch ($e->getAttribute('type')) {
					case 'upload_image': $r .= '$_POST[\''.$e->getAttribute('name')
						.'\']=\'<img src="\'.$fn.\'" alt="" />\';';
						break;
					case 'upload_file': $r .= '$_POST[\''.$e->getAttribute('name')
						.'\']=\'<a href="\'.$fn.\'">\'.basename($file[\'name\']).\'</a>\';';
						break;
                                        }
					$r .= "}\n";
					$e->setAttribute('type', 'file');
					break;
				case 'user_name':
					$r .= 'if ($_POST[\''.$e->getAttribute('name')
						.'\']!=\'\' && !$GLOBALS[\'DBC\']->checkUserName(
isset($_POST[\'_a\'][0][\'id\']) ? $_POST[\'_a\'][0][\'id\'] : \'\', $_POST[\''
						.$e->getAttribute('name')."'])) return -6;\n";
					break;
				case 'password':
					$r .= '$password = $_POST[\''.$e->getAttribute('name').'\'];';
					break;
				case 'password2':
					$r .= 'if ($_POST[\''.$e->getAttribute('name')
						.'\']!=$password) return -7;'."\n";
					$e->setAttribute('type', 'password');
					break;
                        }
			if ($e->hasAttribute('var_name')) {
				switch ($e->getAttribute('var_name')) {
					case 'id':
						$r .= '$_POST[\'_a\'][0][\''.$e->getAttribute('var_name').'\']=';
						break;
					case 'user.group_id':
					case 'user.name':
					case 'user.password':
					case 'user.expire':
					case 'user.email':
						$r .= '$_POST[\'_a\'][0][\''
							.substr($e->getAttribute('var_name'), 5).'\']=';
						break;
					default:
						$r .= '$_POST[\'_a\'][0][\'vars\'][\''
							.$e->getAttribute('var_name').'\']=';
						break;
				}
				if ($e->getAttribute('type')=='hidden') {
					$r .= "'".$e->getAttribute('value')."';\n";
					$e->parentNode->removeChild($e);
                                } else $r .= '$_POST[\''.$e->getAttribute('name')."'];\n";
				$e->removeAttribute('var_name');
			}
			if ($e->parentNode) {
				$ne = $e->ownerDocument->createElement($e->localName);
				foreach ($e->attributes as $a) $ne->setAttribute($a->name, $a->value);
				if ($ne->localName=='textarea') {
					$fr = $e->ownerDocument->createDocumentFragment();
					$fr->appendXML(CCore::innerXML($e));
					$ne->appendChild($fr);
					if (!$e->hasChildNodes())
						$ne->appendChild(new DOMText(htmlspecialchars(stripslashes(
							@$_SESSION['form'][$ne->getAttribute('name')]))));
				} else if ($ne->localName=='select') {
					$fr = $e->ownerDocument->createDocumentFragment();
					$fr->appendXML(CCore::innerXML($e));
					$ne->appendChild($fr);

					$os = $ne->getElementsByTagName('option');
					for ($os_i=0; $os_i<$os->length; $os_i++)
						if ($os->item($os_i)->hasAttribute('value') && $os->item($os_i)->getAttribute('value')==@$_SESSION['form'][$ne->getAttribute('name')] or $os->item($os_i)->textContent==@$_SESSION['form'][$ne->getAttribute('name')])
							$os->item($os_i)->setAttribute('selected', 'selected');

				} else if ($ne->localName=='input' and $ne->getAttribute('type')=='checkbox' || $ne->getAttribute('type')=='radio') {
					if ($ne->getAttribute('value')==@$_SESSION['form'][$ne->getAttribute('name')])
						$ne->setAttribute('checked', 'checked');
				} else if (!$ne->hasAttribute('value'))
					$ne->setAttribute('value', htmlspecialchars(stripslashes(
						@$_SESSION['form'][$ne->getAttribute('name')])));
				$e->parentNode->replaceChild($ne, $e);
			}
		}
		
		if ($e = $Form->Element->getElementsByTagNameNS('/templates/ns', 'checkform')->item(0)) {
			$r .= $e->textContent;
			$Form->Element->removeChild($e);
		}
		
		if ($e = $Form->Element->getElementsByTagNameNS('/templates/ns', 'form_code_img')->item(0)) {
			$e->parentNode->replaceChild(new DOMText('<img src="/cms/form/image?id='.
				$GLOBALS['UserVars']['form.id'].'" alt="" />'), $e);
                }

		return $r;
        }
	
}

?>
