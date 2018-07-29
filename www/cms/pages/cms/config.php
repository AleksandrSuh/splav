<?php

$DOMConfig = new DOMDocument();
$DOMConfig->load(DIR_ROOT.'/config.xml');

if (isset($_POST['SaveConfig'])) {
	$attrs = array('pathFb', 'dbVersionsNum', 'dbType', 'dbHost', 'dbUser', 'dbPass', 'dbName', 'dbFile', 
		'defaultText', 'timeZone', 'defaultParentId', 'userParentId', 'cmsLog', 'serial', 
		'updateCheckBeta', 'cmsMapLength', 'cmsCache', 'imgWatermark', 'debugMode', 'formExpire', 'jpegQuality');
	for ($i=0; $i<count($attrs); $i++)
		if ($_POST[$attrs[$i]]!='') $DOMConfig->documentElement->setAttribute($attrs[$i], $_POST[$attrs[$i]]);
		else $DOMConfig->documentElement->removeAttribute($attrs[$i]);
	$DOMConfig->save(DIR_ROOT.'/config.xml');
	header('location: '.$_SERVER['HTTP_REFERER']);
	exit;
}

if (isset($_POST['RemoveClass'])) {
	foreach ($DOMConfig->getElementsByTagName('class') as $class)
		if ($class->getAttribute('name')==$_POST['className']) {
			$class->parentNode->removeChild($class);
			$DOMConfig->save(DIR_ROOT.'/config.xml');
			$GLOBALS['DBC']->classRemove($_POST['className']);
			header('location: '.$_SERVER['HTTP_REFERER']);
			exit;
		}
}

if (isset($_POST['RemoveProperty'])) {
	foreach ($DOMConfig->getElementsByTagName('class') as $class)
		if ($class->getAttribute('name')==$_POST['className']) {
			foreach ($class->getElementsByTagName('property') as $property)
				if ($property->getAttribute('name')==$_POST['propertyName']) {
					$property->parentNode->removeChild($property);
					$DOMConfig->save(DIR_ROOT.'/config.xml');
					$GLOBALS['DBC']->classPropertyRemove($_POST['className'], $_POST['propertyName']);
					header('location: '.$_SERVER['HTTP_REFERER']);
					exit;
				}
		}
}

if (isset($_POST['AddProperty'])) {
	foreach ($DOMConfig->getElementsByTagName('class') as $class)
		if ($class->getAttribute('name')==$_POST['className']) {
			$p = $DOMConfig->createElement('property');
			$p->setAttribute('name', $_POST['propertyName']);
			$p->setAttribute('type', $_POST['propertyType']);
			$class->appendChild($p);
			$DOMConfig->save(DIR_ROOT.'/config.xml');
			$GLOBALS['DBC']->classPropertyAdd($_POST['className'], $_POST['propertyName'], $_POST['propertyType']);
			header('location: '.$_SERVER['HTTP_REFERER']);
			exit;
		}
}

if (isset($_POST['AddClass'])) {
	foreach ($DOMConfig->getElementsByTagName('external') as $external) {
		$c = $DOMConfig->createElement('class');
		$c->setAttribute('name', $_POST['className']);
		$external->appendChild($c);
		$DOMConfig->save(DIR_ROOT.'/config.xml');
		$GLOBALS['DBC']->classAdd($_POST['className']);
		header('location: '.$_SERVER['HTTP_REFERER']);
		exit;
	}
}

if (isset($_POST['SaveProperty'])) {
	foreach ($DOMConfig->getElementsByTagName('class') as $class)
		if ($class->getAttribute('name')==$_POST['className']) {
			foreach ($class->getElementsByTagName('property') as $property)
				if ($property->getAttribute('name')==$_POST['propertyName']) {
					$property->setAttribute('name', $_POST['newPropertyName']);
					$property->setAttribute('type', $_POST['propertyType']);
					$DOMConfig->save(DIR_ROOT.'/config.xml');
					$GLOBALS['DBC']->classPropertySave($_POST['className'], $_POST['propertyName'], $_POST['newPropertyName'], $_POST['propertyType']);
					header('location: '.$_SERVER['HTTP_REFERER']);
					exit;
				}
		}
}

if (isset($_POST['SaveClass'])) {
	foreach ($DOMConfig->getElementsByTagName('class') as $class)
		if ($class->getAttribute('name')==$_POST['className']) {
			$class->setAttribute('name', $_POST['newClassName']);
			$DOMConfig->save(DIR_ROOT.'/config.xml');
			$GLOBALS['DBC']->classSave($_POST['className'], $_POST['newClassName']);
			header('location: '.$_SERVER['HTTP_REFERER']);
			exit;
		}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS редактор конфигурации</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
</head>
<body style="background-image: url('/cms/img/cms/left.jpg'); background-repeat:no-repeat;">
<div class="tophelp">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Конфигурация</b></div>
</div>&nbsp;

<form id="FormConfig" method="post" style="border: 1px solid LightGrey; margin: 160px 20px 20px; padding: 10px;"><table cellpadding="0" cellspacing="3" width="100%">
<tr><td>Файл модифицирован</td><td><?=date('Y-m-d H:i:s', filemtime(DIR_ROOT.'/config.xml'))?></td></tr>
<tr><td>Папка файлового браузера</td><td><input type="text" name="pathFb" value="<?=DIR_FB?>" /></td></tr>
<tr><td>Количество сохраняемых версий страниц</td><td><input type="text" name="dbVersionsNum" value="<?=DB_VERSIONS_NUM?>" /></td></tr>
<tr><td>Тип базы данных</td><td><select name="dbType" onchange="cmsConfigCangeDbType(this.value)"><option value="mysql"<?=DB_TYPE=='mysql' ? ' selected="selected"' : ''?>>mysql</option><option value="xml"<?=DB_TYPE=='xml' ? ' selected="selected"' : ''?>>xml</option></select></td><td>
	<div class="button" style="width: 100px; float: left; margin-right: 20px;" onclick="location = '/cms/converter';">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Конвертор
	</div>
</td></tr>

<tbody id="mysqlParams"<?=DB_TYPE!='mysql' ? ' style="display: none;"' : ''?>>
	<tr><td>Хост базы данных (mysql)</td><td><input type="text" name="dbHost" value="<?=$DOMConfig->documentElement->getAttribute('dbHost')?>" /></td></tr>
	<tr><td>Пользователь базы данных (mysql)</td><td><input type="text" name="dbUser" value="<?=$DOMConfig->documentElement->getAttribute('dbUser')?>" /></td></tr>
	<tr><td>Пароль к базе данных (mysql)</td><td><input type="text" name="dbPass" value="<?=$DOMConfig->documentElement->getAttribute('dbPass')?>" /></td></tr>
	<tr><td>Имя базы данных (mysql)</td><td><input type="text" name="dbName" value="<?=$DOMConfig->documentElement->getAttribute('dbName')?>" /></td></tr>
</tbody>

<tbody id="xmlParams"<?=DB_TYPE!='xml' ? ' style="display: none;"' : ''?>>
	<tr><td>Файл базы данных (xml)</td><td><input type="text" name="dbFile" value="<?=$DOMConfig->documentElement->getAttribute('dbFile')?>" /></td></tr>
</tbody>

<tr><td>Текст по умолчанию</td><td><input type="text" name="defaultText" value="<?=DEFAULT_TEXT?>" /></td></tr>
<tr><td>Временная зона</td><td><input type="text" name="timeZone" value="<?=TIME_ZONE?>" /></td></tr>
<tr><td>Id раздела новых страниц</td><td><input id="defaultParentId" type="text" name="defaultParentId" value="<?=DEFAULT_PARENT_ID?>" /></td><td>
	<div class="button" style="width: 70px; float: left; margin-right: 20px;" onclick="Select('FormConfig', 'defaultParentId', 'id');">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Выбрать
	</div>
</td></tr>
<tr><td>Id раздела информации о пользователях</td><td><input id="userParentId" type="text" name="userParentId" value="<?=USER_PARENT_ID?>" /></td><td>
	<div class="button" style="width: 70px; float: left; margin-right: 20px;" onclick="Select('FormConfig', 'userParentId', 'id');">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Выбрать
	</div>
</td></tr>
<tr><td>Режим отладки</td><td><input type="radio" name="debugMode" value="true"<?=DEBUG_MODE=='true' ? ' checked="checked"' : ''?> /> Да <input type="radio" name="debugMode" value="false"<?=DEBUG_MODE=='false' ? ' checked="checked"' : ''?> /> Нет</td></tr>
<!--<tr><td>Режим совместимости</td><td><input type="radio" name="compatibilityMode" value="true"<?=COMPATIBILITY_MODE=='true' ? ' checked="checked"' : ''?> /> Да <input type="radio" name="compatibilityMode" value="false"<?=COMPATIBILITY_MODE=='false' ? ' checked="checked"' : ''?> /> Нет</td></tr>-->
<!--<tr><td>Вывод информации об изображении в файловом браузере</td><td><input type="radio" name="fbShowImgInfo" value="true"<?=FB_SHOW_IMG_INFO=='true' ? ' checked="checked"' : ''?> /> Да <input type="radio" name="fbShowImgInfo" value="false"<?=FB_SHOW_IMG_INFO=='false' ? ' checked="checked"' : ''?> /> Нет</td></tr>-->
<tr><td>Вести журнал посещений</td><td><input type="radio" name="cmsLog" value="true"<?=CMS_LOG=='true' ? ' checked="checked"' : ''?> /> Да <input type="radio" name="cmsLog" value="false"<?=CMS_LOG=='false' ? ' checked="checked"' : ''?> /> Нет</td></tr>
<tr><td>Проверять бета-версии при проверке обновлений (<b>не рекомендуется</b>)</td><td><input type="radio" name="updateCheckBeta" value="true"<?=UPDATE_CHECK_BETA=='true' ? ' checked="checked"' : ''?> /> Да <input type="radio" name="updateCheckBeta" value="false"<?=UPDATE_CHECK_BETA=='false' ? ' checked="checked"' : ''?> /> Нет</td></tr>
<tr><td>Серийный номер</td><td><input type="text" name="serial" value="<?=AUTH_SERIAL?>" /></td></tr>
<tr><td>Максимум записей в карте сайта в backend</td><td><input type="text" name="cmsMapLength" value="<?=CMS_MAP_LENGTH?>" /></td></tr>
<tr><td>Кэширование</td><td>
	<input type="radio" name="cmsCache" value="none"<?=CMS_CACHE=='none' ? ' checked="checked"' : ''?> /> Нет
	<input type="radio" name="cmsCache" value="page"<?=CMS_CACHE=='page' ? ' checked="checked"' : ''?> /> Постранично<br />
	<input type="radio" name="cmsCache" value="main"<?=CMS_CACHE=='main' ? ' checked="checked"' : ''?> /> Основная область
<?php if (DB_TYPE=='mysql') echo '<input type="radio" name="cmsCache" value="sql"',(CMS_CACHE=='sql'  ? ' checked="checked"' : ''),' /> SQL-кэш'; ?>

</td><td>
	<div class="button" style="width: 90px; float: left; margin-right: 20px;" onclick="location = '/cms/cache/clear';">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Очистить кэш
	</div>
</td></tr>
<tr><td>Водяной знак на изображениях</td><td><input id="watermark" title="[ссылка_на_изображение][ min:(\d+)x(\d+)][ pos:(\d+)x(\d+)][ size:(\d+)x(\d+)]" type="text" name="imgWatermark" value="<?=IMG_WATERMARK?>" /></td><td>
	<div class="button" style="width: 100px; float: left; margin-right: 20px;" onclick="var buf = showModalDialog('/cms/includes/watermark.html', null, 'dialogwidth: 450px; dialogheight: 250px;'); if (buf) document.getElementById('watermark').value = buf;">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Редактировать
	</div>
</td></tr>
<tr><td>Качество JPEG-изображений (в процентах)</td><td><input type="text" name="jpegQuality" value="<?=JPEG_QUALITY?>" /></td></tr>
<tr><td>Время жизни форм (в часах)</td><td><input type="text" name="formExpire" value="<?=FORM_EXPIRE?>" /></td></tr>
<tr><td>
	<input type="hidden" name="SaveConfig" value="SaveConfig" />
	<div class="button" style="width: 480px; float: left; margin-right: 20px; text-align: center;" onclick="document.getElementById('FormConfig').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Сохранить
	</div>
</td></tr></table></form>

<div class="vartitle" style="margin: 50px 20px 10px; cursor: pointer;" onclick="document.getElementById('externalClasses').style.display = '';">Внешние классы</div>
<div id="externalClasses" style="display: none; border: 1px solid LightGrey; margin: 10px 20px 20px; padding: 10px;">
<table cellpadding="0" cellspacing="3" width="100%">
<?php
foreach ($GLOBALS['EXTERNAL_CLASS'] as $className=>$class) {
	echo '<tr><td><form method="post"><input type="hidden" name="className" value="'.$className.'" /><input type="text" name="newClassName" value="'.$className.'" /><br /><input type="submit" name="SaveClass" value="Сохранить" /><input type="submit" name="RemoveClass" value="Удалить" /></form></td><td><table cellpadding="0" cellspacing="3">';
	if (array_key_exists('property', $GLOBALS['EXTERNAL_CLASS'][$className])) foreach ($GLOBALS['EXTERNAL_CLASS'][$className]['property'] as $i=>$property) {
		echo '<form method="post"><input type="hidden" name="className" value="'.$className.'" /><tr><td><input type="hidden" name="propertyName" value="'.$GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['name'].'" /><input type="text" name="newPropertyName" value="'.$GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['name'].'" /></td><td><select name="propertyType">';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='char' ? ' selected="selected"' : '').' value="char">Строка (255)</option>';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='date' ? ' selected="selected"' : '').' value="date">Время/Дата</option>';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='int' ? ' selected="selected"' : '').' value="int">Целое</option>';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='float' ? ' selected="selected"' : '').' value="float">Вещественное</option>';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='mediumtext' ? ' selected="selected"' : '').' value="mediumtext">Объемный текст (16Мб)</option>';
		echo '<option'.($GLOBALS['EXTERNAL_CLASS'][$className]['property'][$i]['type']=='text' ? ' selected="selected"' : '').' value="text">Текст (64Кб)</option>';
		echo '</select></td><td><input type="submit" name="SaveProperty" value="Сохранить" /><input type="submit" name="RemoveProperty" value="Удалить" /></td></tr></form>';
	}
	echo '<form method="post"><input type="hidden" name="className" value="'.$className.'" /><tr><td><input type="text" name="propertyName" value="" /></td><td><select name="propertyType"><option value="char">Строка (255)</option><option value="date">Время/Дата</option><option value="int">Целое</option><option value="float">Вещественное</option><option value="mediumtext">Объемный текст (16Мб)</option><option value="text">Текст (64Кб)</option></select>
</td><td><input type="submit" name="AddProperty" value="Добавить" /></td></tr></table></td></tr></form>';
}
?>
<form method="post"><tr><td><input type="text" name="className" value="" /></td><td><input type="submit" name="AddClass" value="Добавить" /></td></tr></form>
</table>
</div>


</body>
</html>
<?php

exit;

?>