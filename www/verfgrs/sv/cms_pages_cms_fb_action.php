<?php

if (!file_exists(DIR_ROOT.DIR_FB)) mkdir(DIR_ROOT.DIR_FB);

if (isset($_POST['link'])) {
	$y = date('Y');
	$m = date('m');
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast')) mkdir(DIR_ROOT.DIR_FB.'/_fast');
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast/'.$y)) mkdir(DIR_ROOT.DIR_FB.'/_fast/'.$y);
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m)) mkdir(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m);
	$_POST['d'] = DIR_FB.'/_fast/'.$y.'/'.$m;
}

if (isset($_GET['d']) && !isset($_POST['d'])) $_POST['d'] = $_GET['d'];

define('DIR_BROWSE', (isset($_POST['d']) && preg_match('/^'.str_replace('/', '\/', DIR_FB).'/', $_POST['d'])
	&& !preg_match('/\/\.\./', $_POST['d'])
	? $_POST['d'] : DIR_FB));


function DeleteSubDir($Dirname) {
	foreach (glob($Dirname.'/*') as $filename) if (is_dir($filename)) {
		DeleteSubDir($filename);
		rmdir($filename);
	} else unlink($filename);
}

function CopyDir($OldName, $DirName) {
	if (!file_exists($DirName)) mkdir($DirName);
	foreach (glob($OldName.'/*') as $filename)
		if (is_dir($filename)) CopyDir($filename, $DirName.'/'.basename($filename));
		else copy($filename, $DirName.'/'.basename($filename));
}

function ZipAddDir(&$Zip, $DirName) {
	foreach (glob($DirName.'/*') as $filename)
		if (is_dir($filename)) ZipAddDir($Zip, $filename);
		else $Zip->addFile($filename, str_replace(DIR_ROOT.DIR_BROWSE.'/', '', $filename));
}

$result = '';
$error = false;
$return_type = 'xml';
switch ($_GET['a']) {
	case 'flashupload':
		if (!preg_match('/^'.str_replace('/', '\/', DIR_FB).'/', $_GET['fn'])) break;
		if (preg_match('/\/\.\./', $_GET['fn'])) break;
		foreach ($_FILES as $file) {
			$dirs = explode('/', $_GET['fn']);
			$dir = DIR_ROOT;
			for ($i=1; $i<count($dirs)-1; $i++) {
				$dir .= '/'.$dirs[$i];
				if (!file_exists($dir)) mkdir($dir);
			}
			//file_put_contents('temp.txt', file_get_contents('temp.txt').sprintf("\nflashupload: %s %s\n", $file['tmp_name'], DIR_ROOT.$_GET['fn']));
			move_uploaded_file($file['tmp_name'], DIR_ROOT.$_GET['fn']);
			chmod(DIR_ROOT.$_GET['fn'], 0644);
                }
		break;
	case 'upload':
		foreach ($_FILES as $file) {
			if (isset($_POST['link'])) $filename = DIR_BROWSE.'/'.uniqid().'_'.basename($file['name']);
			else $filename = DIR_BROWSE.'/'.uniqid().'_'.basename($file['name']);
			move_uploaded_file($file['tmp_name'], DIR_ROOT.$filename);
			chmod(DIR_ROOT.$filename, 0644);
		}
		if (isset($_POST['alt'])) $result = '<script type="text/javascript">window.opener.InsertImg("'
			.$filename.'", "'.$_POST['alt'].'"); window.close();</script>';
		else if (isset($_POST['link'])) $result = '<script type="text/javascript">window.opener.CreateLink("'
			.$filename.'"); window.close();</script>';
		else $result = 'Загружено файлов: '.count($_FILES);
		$return_type = 'html';
		break;
	case 'newdir':
		if (isset($_POST['name']) && $_POST['name']!='') {
			mkdir(DIR_ROOT.DIR_BROWSE.'/'.$_POST['name']);
			$result = 'Папка "'.$_POST['name'].'" создана';
		} else {
			$error = true;
			$result = 'Папка не создана. Возможно имя файла было введено некорректно.';
		}
		$return_type = 'html';
		break;
	case 'delete':
		foreach ($_POST['names'] as $name)
			if (is_dir(DIR_ROOT.$name)) {
				DeleteSubDir(DIR_ROOT.$name);
				rmdir(DIR_ROOT.$name);
			} else unlink(DIR_ROOT.$name);
		break;
	case 'paste':
		if (isset($_COOKIE['FB_BUF_CUT'])) foreach ($_COOKIE['FB_BUF_CUT'] as $i=>$name) {
			if (file_exists(DIR_ROOT.DIR_BROWSE.'/'.basename($name))) {
				if (is_dir(DIR_ROOT.DIR_BROWSE.'/'.basename($name))) {
					DeleteSubDir(DIR_ROOT.DIR_BROWSE.'/'.basename($name));
					rmdir(DIR_ROOT.DIR_BROWSE.'/'.basename($name));
				} else unlink(DIR_ROOT.DIR_BROWSE.'/'.basename($name));
			}
			rename(DIR_ROOT.$name, DIR_ROOT.DIR_BROWSE.'/'.basename($name));
			setcookie('FB_BUF_CUT['.$i.']', '', 0, '/');
		} else if (isset($_COOKIE['FB_BUF_COPY'])) foreach ($_COOKIE['FB_BUF_COPY'] as $i=>$name) {
			if (DIR_ROOT.$name==DIR_ROOT.DIR_BROWSE.'/'.basename($name)) {
				if (is_dir(DIR_ROOT.$name))
					CopyDir(DIR_ROOT.$name, DIR_ROOT.DIR_BROWSE.'/copy_'.basename($name));
				else copy(DIR_ROOT.$name, DIR_ROOT.DIR_BROWSE.'/copy_'.basename($name));
			} else {
				if (is_dir(DIR_ROOT.$name))
					CopyDir(DIR_ROOT.$name, DIR_ROOT.DIR_BROWSE.'/'.basename($name));
				else copy(DIR_ROOT.$name, DIR_ROOT.DIR_BROWSE.'/'.basename($name));
			}
			setcookie('FB_BUF_COPY['.$i.']', '', 0, '/');
		}
		break;
	case 'zip':
		if (isset($_POST['name']) && $_POST['name']!='') {
			if (!preg_match('/\.zip$/', $_POST['name'])) $_POST['name'] .= '.zip';
			$zip = new ZipArchive();
			$zip->open(DIR_ROOT.DIR_BROWSE.'/'.$_POST['name'], ZIPARCHIVE::CREATE);
			foreach ($_POST['names'] as $name)
				if (is_dir(DIR_ROOT.$name)) ZipAddDir($zip, DIR_ROOT.$name);
				else $zip->addFile(DIR_ROOT.$name,
					str_replace(DIR_ROOT.DIR_BROWSE.'/', '', DIR_ROOT.$name));
			$zip->close();
			$result = 'Архив "'.$_POST['name'].'" создан';
		} else {
			$error = true;
			$result = 'Архив не создан. Возможно имя файла было введено некорректно.';
		}
		$return_type = 'html';
		break;
	case 'unzip':
		if (isset($_POST['name']) && $_POST['name']!='') if (!is_dir(DIR_ROOT.$_POST['name'])) {
			$zip = new ZipArchive();
			if ($zip->open(DIR_ROOT.$_POST['name'])!==true) $result = 'Не удалось открыть архив';
			for ($i=0; $extractToName = $zip->getNameIndex($i); $i++)
				$zip->extractTo(DIR_ROOT.DIR_BROWSE, $extractToName);
			$zip->close();
		}
		break;
	case 'getprops':
		if (count($_POST['names'])>0) {
			$filename = basename($_POST['names'][0]);
			$filesize = 0;
			$chmod = fileperms(DIR_ROOT.$_POST['names'][0]);
			foreach ($_POST['names'] as $name) {
				$filesize += filesize(DIR_ROOT.$name);
				if ($chmod!='' && $chmod!=fileperms(DIR_ROOT.$name)) $chmod = '';
			}
			$chmod = substr(sprintf('%o', $chmod), -4);
			$result = '<response filename="'.(count($_POST['names'])==1 ? $filename : '').'" filesize="'
				.$filesize.'" chmod="'.$chmod.'" />';
		}
		break;
	case 'setprops':
		if (isset($_POST['name']) && $_POST['name']!='') {
			chmod(DIR_ROOT.$_POST['name'], $_POST['chmod']);
			rename(DIR_ROOT.$_POST['name'], DIR_ROOT.DIR_BROWSE.'/'.$_POST['newname']);
		} else foreach ($_POST['names'] as $name) chmod(DIR_ROOT.$name, $_POST['chmod']);
		$result = 'Свойства сохранены';
		$return_type = 'html';
		break;
}

switch ($return_type) {
	case 'html':
		echo '<html><head><title>'.$result.'</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /></head><body>'.$result;
		if (!$error) echo '<script type="text/javascript">window.opener.Refresh(); window.close();</script>';
		echo '</body></html>';
		break;
	case 'xml':
		header('Content-Type: application/xml; charset=UTF-8');
		echo $result;
		break;
}

exit;

?>