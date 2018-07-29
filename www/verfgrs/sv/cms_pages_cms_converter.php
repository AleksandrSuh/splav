<?php

if (isset($_POST['dbType'])) {
	$DOMConfig = new DOMDocument();
	$DOMConfig->load(DIR_ROOT.'/config.xml');

	switch ($_POST['dbType']) {
		case 'xml':
			CDBCMySQL::exportXML($_POST['dbFile']);
			break;
		case 'mysql':
			CDBCMySQL::importXML($_POST['dbHost'], $_POST['dbUser'], $_POST['dbPass'], $_POST['dbName']);
			break;
        }
	
	$attrs = array('dbType', 'dbHost', 'dbUser', 'dbPass', 'dbName', 'dbFile');
	for ($i=0; $i<count($attrs); $i++)
		if ($_POST[$attrs[$i]]!='') $DOMConfig->documentElement->setAttribute($attrs[$i], $_POST[$attrs[$i]]);
		else $DOMConfig->documentElement->removeAttribute($attrs[$i]);
	$DOMConfig->save(DIR_ROOT.'/config.xml');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS конвертор БД</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
</head>
<body>
<img src="/cms/img/cms/left.jpg" width="80" style="float: left;" />

<div class="tophelp" style="width: 240px;">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Конвертор БД</b><br />Текущий тип БД: <?= DB_TYPE ?></div>
</div>&nbsp;

<form method="post" id="FormConverter" style="clear: both;"><table cellpadding="0" cellspacing="3" width="100%">
<tr><td>Конвертировать в</td><td><select name="dbType" onchange="cmsConfigCangeDbType(this.value)">
	<?=DB_TYPE!='xml' ? '<option value="xml">xml</option>' : ''?>
	<?=DB_TYPE!='mysql' ? '<option value="mysql">mysql</option>' : ''?>
</select></td></tr>

<tbody id="mysqlParams"<?=DB_TYPE=='mysql' ? ' style="display: none;"' : ''?>>
	<tr><td>Хост базы данных (mysql)</td><td><input type="text" name="dbHost" value="<?= DB_HOST ?>" /></td></tr>
	<tr><td>Пользователь базы данных (mysql)</td><td><input type="text" name="dbUser" value="<?= DB_USER ?>" /></td></tr>
	<tr><td>Пароль к базе данных (mysql)</td><td><input type="text" name="dbPass" value="<?= DB_PASS ?>" /></td></tr>
	<tr><td>Имя базы данных (mysql)</td><td><input type="text" name="dbName" value="<?= DB_NAME ?>" /></td></tr>
</tbody>

<tbody id="xmlParams"<?=DB_TYPE=='xml' ? ' style="display: none;"' : ''?>>
	<tr><td>Файл базы данных (xml)</td><td><input type="text" name="dbFile" value="<?= DB_FILE ?>" /></td></tr>
</tbody>

<tr><td align="center" colspan="2">
	<div class="button" style="width: 110px; margin-right: 20px; text-align: center;" onclick="document.getElementById('FormConverter').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Конвертировать
	</div>
</td></tr>
</table>
</form>
</body>
</html>
<?php

exit;

?>