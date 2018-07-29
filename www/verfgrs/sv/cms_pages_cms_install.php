<?php

header('Content-type: text/html; charset=UTF-8');
$need_exts = array('libxml', 'dom', 'gd', 'mysql', 'xsl', 'zip', 'Zend Optimizer');
$exts = get_loaded_extensions();
$not_instaled_exts = array_diff($need_exts, get_loaded_extensions());

function CheckPost($Name, $Default) {
	if (isset($_POST[$Name]) && $_POST[$Name]!='') $_SESSION[$Name] = $_POST[$Name];
	else if (!isset($_SESSION[$Name]) || isset($_POST[$Name])) $_SESSION[$Name] = $Default;
}

function Update() {
	$up = file_get_contents('http://update43.artgk-cms.ru/');
	if (!$up) return false;
	file_put_contents('update.zip', $up);
	$zip = new ZipArchive();
	$zip->open('update.zip');
	for ($i=0; $name = $zip->getNameIndex($i); $i++) {
		$extractToName = $name;
		$extractPath = './';
		if (preg_match('/^cms\//', $name, $matches)) {
			$extractToName = str_replace($matches[0], '', $name);
			if ($extractToName=='') continue;
			$zip->renameName($name, $extractToName);
			$extractPath = DIR_ROOT.'/cms';	
		}
		$zip->extractTo($extractPath, $extractToName);
	}
	$zip->close();
	unlink('update.zip');
	return true;
}

CheckPost('ADMIN_NAME',		'');
CheckPost('ADMIN_PASS',		'');
CheckPost('DB_TYPE',		'mysql');
CheckPost('DB_HOST',		'localhost');
CheckPost('DB_USER',		'root');
CheckPost('DB_PASS',		'');
CheckPost('DB_NAME',		'');
CheckPost('DB_FILE',		'.xml');
CheckPost('AUTH_SERIAL',	'');

$dir_templates_exists = file_exists(DIR_ROOT.'/templates');
$xml_root_exists = file_exists(DIR_ROOT.'/templates/root.xml');
$db_connect = false;
$db_select_db = false;

if ($_SESSION['DB_TYPE']=='mysql' && !in_array('mysql', $not_instaled_exts)) {
	if (mysql_connect($_SESSION['DB_HOST'], $_SESSION['DB_USER'], $_SESSION['DB_PASS'])) {
		$db_connect = true;
		if (mysql_select_db($_SESSION['DB_NAME'])) {
			$db_select_db = true;
		}
	}
}

if (!file_exists('.htaccess')) file_put_contents('.htaccess', 'AddDefaultCharset UTF-8
	RewriteEngine on
	RewriteBase /
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^.*$ /index.php');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Установка ArtGK CMS</title>
<script type="text/javascript" src="/cms/js/ajax.js"></script>
<script type="text/javascript" src="/cms/js/cms.js"></script>
<script type="text/javascript">
function InstallTemplate(sUrl) {
	var fOnLoad = function(text) {window.location = window.location.pathname+"?"+text;}
	var fOnError = function() {alert("Не удалось загрузить файл шаблона");}
	loader.SendRequest("/cms/templates/upload?url="+encodeURIComponent(sUrl), null, "GET", fOnLoad, fOnError);
}
function ShowGallery(o) {
	window.open(o.getAttribute("href"), "_blank", "menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=400");
	return false;
}
</script>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<style type="text/css">
.license {height: 540px;}
</style>
</head><body>';
if (count($not_instaled_exts)>0) {
	echo '<font color="#FF0000">На сервере не установлены необходимые библиотеки: '
		.implode(', ', $not_instaled_exts).'</font>';
}
echo '<form method="post"><table cellpadding="0" cellspacing="3">';
if (isset($_POST['install'])
	&& (($_SESSION['DB_TYPE']=='mysql' && $db_connect && $db_select_db)
		|| $_SESSION['DB_TYPE']=='xml')
) {
	echo '<tr><th>Процесс установки</th></tr>';



	if (!$dir_templates_exists) mkdir(DIR_ROOT.'/templates');
	echo '<tr><td>Проверена папка шаблонов</td></tr>';



	if (!$xml_root_exists) file_put_contents(DIR_ROOT.'/templates/root.xml', '<?xml version="1.0" encoding="utf-8"?>
<t:templates xmlns:t="/templates/ns" xmlns="http://www.w3.org/1999/xhtml">
<t:template id="index">
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml"&gt;
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<t:head />
</head>
<body>
<br /><br />
<t:main />
</body>
&lt;/html&gt;
</t:template>
</t:templates>');
	echo '<tr><td>Проверен корневой шаблон</td></tr>';



	switch ($_SESSION['DB_TYPE']) {
		case 'mysql':
			CDBCMySQL::install($_SESSION['ADMIN_NAME'], 
				$_SESSION['ADMIN_PASS']!='' ? md5($_SESSION['ADMIN_PASS']) : '');
			break;
		case 'xml':
			CDBCXML::install($_SESSION['ADMIN_NAME'], 
				$_SESSION['ADMIN_PASS']!='' ? md5($_SESSION['ADMIN_PASS']) : '',
				DIR_ROOT.'/'.$_SESSION['DB_FILE']);
			break;
	}
	$GLOBALS['DBC']->connect($_SESSION['DB_TYPE'],
				 $_SESSION['DB_HOST'], $_SESSION['DB_USER'], $_SESSION['DB_PASS'], $_SESSION['DB_NAME'],
				 $_SESSION['DB_FILE']);
	echo '<tr><td>База данных подготовлена</td></tr>';



	if (file_exists('import.xml')) {
		preImportXML(null, 'import.xml', true, $GLOBALS['CMS_VARS'], $GLOBALS['CMS_VARS'],true,false,true,'');
		$GLOBALS['DBC']->usePublicVersion(false);
		CCore::checkPost(CCore::CHECK_POST_NONE);
		echo '<tr><td>Начальные данные импортированы</td></tr>';
	}



	file_put_contents('config.xml', '<?xml version="1.0" encoding="utf-8"?>
<config
	dbType="'.$_SESSION['DB_TYPE'].'"
	dbHost="'.$_SESSION['DB_HOST'].'"
	dbUser="'.$_SESSION['DB_USER'].'"
	dbPass="'.$_SESSION['DB_PASS'].'"
	dbName="'.$_SESSION['DB_NAME'].'"
	dbFile="'.$_SESSION['DB_FILE'].'"
	serial="'.$_SESSION['AUTH_SERIAL'].'">
	<external></external>
</config>');
	echo '<tr><td>Файл конфигурации создан</td></tr>';



	file_put_contents('.htaccess', 'AddDefaultCharset UTF-8
RewriteEngine on
RewriteBase /
RewriteRule ^.htaccess$ - [F]
RewriteRule ^config.xml$ - [F]
RewriteRule ^templates/.*\.xml$ - [F]
RewriteRule ^cms/pages/ - [F]'.($_SESSION['DB_TYPE']=='xml' ? '
RewriteRule ^'.$_SESSION['DB_FILE'].'$ - [F]
RewriteRule ^stat.xml$ - [F]
RewriteRule ^form.xml$ - [F]
RewriteRule ^log.xml$ - [F]
RewriteRule ^counter.xml$ - [F]
' : '').'
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^.*$ /index.php');
	echo '<tr><td>Файл ".htaccess" создан</td></tr>';



	echo '<tr><th>Установка завершена</th></tr>';
	echo '<tr><td>Вход в CMS по адресу: &quot;<a href="/login">http://'.$_SERVER['HTTP_HOST']
		.'/login</a>&quot;</td></tr>';
} else {
	echo '<tr><th colspan="2">Настройка базовых параметров</th><td rowspan="999"><div class="license">'
		.file_get_contents(DIR_ROOT.'/cms/license.txt').'</div></td></tr>';
	echo '<tr><th colspan="2">Корневой шаблон*</th></tr>';
	echo '<tr><td>'.($xml_root_exists ? '<span style="color: Green;">установлен</span>'
		: '<span style="color: Red;">не установлен</span>').'</td><td>
		<a href="#tp" onclick="TPDlg(\'root.xml\'); return false;">[свойства шаблона]</a>
		<br /><a href="/cms/templates/gallery?c=root" target="_blank" onclick="return ShowGallery(this);">
			[загрузить из галереи]</a></td></tr>';
	echo '<tr><td></td><td><button onclick="this.form.submit();">Проверить</button></td></tr>';
	echo '<tr><td colspan="2">* Если не существует, <br />то будет создан стандартный</td></tr>';
	echo '<tr><th colspan="2">Администратор</th></tr>';
	echo '<tr><td'.($_SESSION['ADMIN_NAME']!='' ? ' style="color: Green;"' : ' style="color: Red;"')
		.'>Имя</td><td><input type="text" name="ADMIN_NAME" value="'.$_SESSION['ADMIN_NAME'].'" /></td></tr>';
	echo '<tr><td>Пароль</td><td><input type="text" name="ADMIN_PASS" value="'.$_SESSION['ADMIN_PASS']
		.'" /></td></tr>';
	echo '<tr><th colspan="2">База данных**</th></tr>';
	echo '<tr><td>Тип</td><td><select name="DB_TYPE" onChange="this.form.submit();"><option value="mysql"'
		.($_SESSION['DB_TYPE']=='mysql' ? ' selected="selected"' : '').'>mysql</option><option value="xml"'
		.($_SESSION['DB_TYPE']=='xml' ? ' selected="selected"' : '').'>xml</option></select></td></tr>';
	echo '<tr'.($_SESSION['DB_TYPE']!='mysql' ? ' style="display: none;"' : '').'><td'
		.($db_connect ? ' style="color: Green;"' : ' style="color: Red;"')
		.'>Хост</td><td><input type="text" name="DB_HOST" value="'
		.$_SESSION['DB_HOST'].'" /></td></tr>';
	echo '<tr'.($_SESSION['DB_TYPE']!='mysql' ? ' style="display: none;"' : '').'><td'
		.($db_connect ? ' style="color: Green;"' : ' style="color: Red;"')
		.'>Пользователь</td><td><input type="text" name="DB_USER" value="'
		.$_SESSION['DB_USER'].'" /></td></tr>';
	echo '<tr'.($_SESSION['DB_TYPE']!='mysql' ? ' style="display: none;"' : '').'><td'
		.($db_connect ? ' style="color: Green;"' : ' style="color: Red;"')
		.'>Пароль</td><td><input type="text" name="DB_PASS" value="'.$_SESSION['DB_PASS'].'" /></td></tr>';
	echo '<tr'.($_SESSION['DB_TYPE']!='mysql' ? ' style="display: none;"' : '').'><td'
		.($db_select_db ? ' style="color: Green;"' : ' style="color: Red;"')
		.'>Имя</td><td><input type="text" name="DB_NAME" value="'.$_SESSION['DB_NAME'].'" /></td></tr>';
	echo '<tr'.($_SESSION['DB_TYPE']!='xml' ? ' style="display: none;"' : '')
		.'><td>Файл</td><td><input type="text" name="DB_FILE" value="'.$_SESSION['DB_FILE'].'" /></td></tr>';
	echo '<tr><td></td><td><button onclick="this.form.submit();">Проверить</button></td></tr>';
	echo '<tr><td colspan="2">** База данных будет очищена</td></tr>';
	echo '<tr><th colspan="2">Авторизация</th></tr>';
	echo '<tr><td>Серийный номер</td><td><input type="text" name="AUTH_SERIAL" value="'.$_SESSION['AUTH_SERIAL']
		.'" /></td></tr>';
	echo '<tr><th colspan="2">Лицензионное соглашение</th></tr>';
	echo '<tr><td colspan="2">
<input type="checkbox" onclick="this.form.elements[\'install\'].disabled = !this.checked" /> Принимаю</td></tr>';
	echo '<tr><th colspan="2"><input type="submit" disabled="disabled" name="install" value="Установить" />
		</td></tr>';
}
echo '</table></form></body></html>';

exit;

?>