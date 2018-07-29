<?php

$GLOBALS['BACKEND'] = true;

$vars = $GLOBALS['DBC']->getVars(empty($_SERVER['QUERY_STRING']) ? '/' : $_SERVER['QUERY_STRING']);
$title = $vars['title'];

if (isset($_POST['type'])) {

	$rv = trim(implode(',', $_POST['field']), ',');

	switch ($_POST['format']) {
		case 'html': $format_tpl = 'html@cms/pages/cms/export.xml'; break;
		case 'xml':  $format_tpl = 'xml@cms/pages/cms/export.xml'; break;
		case 'csv_utf-8': case 'csv_windows-1251': $format_tpl = 'csv@cms/pages/cms/export.xml'; break;
	}

	switch ($_POST['type']) {
		case 'childrens': $template = 'xml:<t:childrens id="'.$vars['id'].'" template="'.$format_tpl.'"'.($_POST['deep']==1 ? '' : ' deep="'.$_POST['deep'].'"').' required_vars="'.$rv.'" />'; break;
		case 'user': $template = 'xml:<t:user template="'.$format_tpl.'" group="'.$_POST['group'].'" required_vars="'.$rv.'" />'; break;
		case 'sql': $template = 'xml:<t:select template="'.$format_tpl.'" sql="'.$_POST['sql'].'" required_vars="'.$rv.'" />'; break;
	}

	$result = Build($template);

	switch ($_POST['format']) {
		case 'html': break;
		case 'xml':
			header('Content-Type: application/xml; charset=UTF-8');
			header('Content-Disposition: attachment; filename="export.xml"');
			header('Content-Length: '.strlen($result));
			echo $result;
			exit;
		case 'csv_utf-8':
			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename="export.csv"');
			header('Content-Length: '.strlen($result));
			echo $result;
			exit;
		case 'csv_windows-1251':
			$result = mb_convert_encoding($result, 'Windows-1251', 'UTF-8');
			header('Content-Type: text/csv; charset=Windows-1251');
			header('Content-Disposition: attachment; filename="export.csv"');
			header('Content-Length: '.strlen($result));
			echo $result;
			exit;
	}

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Экспорт данных - <?= $title ?></title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<style type="text/css">
body {
	margin: 0px 20px;
	background-image: url('/cms/img/cms/left.jpg');
	background-repeat:no-repeat;
}
td {
	line-height: 20px;
	padding: 3px;
}
table {
	border: 1px solid LightGrey;
}
th {
	background-color: #d5f0fd;
	color: #0079a3;
	height: 24px;
}
</style>
<script type="text/javascript" src="/cms/js/ajax.js"> </script>
<script type="text/javascript" src="/cms/js/modaldlg.js"> </script>
<script type="text/javascript" src="/cms/js/cms.js"> </script>
<script type="text/javascript">
function selecttype(id) {
	var user = document.getElementById('user');
	var childrens = document.getElementById('childrens');
	var sql = document.getElementById('sql');

	user.style.display = user.id==id ? '' : 'none';
	childrens.style.display = childrens.id==id ? '' : 'none';
	sql.style.display = sql.id==id ? '' : 'none';
}
function addfield(select) {
	var tr = select.parentNode.parentNode;
	if (select.value!='' && tr.parentNode.lastChild==tr) {
		var newtr = tr.cloneNode(true);
		tr.parentNode.appendChild(newtr);
	}
}
</script>
</head>
<body>

<div class="tophelp">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Экспорт данных</b> - <?= $title ?></div>
</div>&nbsp;

<form id="FormExport" method="post" style="margin-top: 150px;">

<table cellpadding="0" cellspacing="3" >
<tr><td width="40%">Экспорт данных</td><td><select name="type" onchange="selecttype(this.value)">
	<option value="childrens">текущего раздела</option>
	<option value="user">пользователей</option>
	<option value="sql">по SQL-запросу</option>
</select></td></tr>
<tr><td>Формат</td><td><select name="format">
	<option value="html">HTML</option>
	<option value="xml">XML</option>
	<option value="csv_utf-8">CSV (UTF-8)</option>
	<option value="csv_windows-1251">CSV (Windows-1251)</option>
</select></td></tr>
<tr><td>Абсолютные ссылки</td><td><input type="checkbox" name="absolute_link" value="yes" /></td></tr>

<tbody id="childrens">
<tr><td>Глубина вложенности</td><td><select name="deep">
	<option value="1" selected="selected">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
</select></td></tr>
<tr><td>Количество записей</td><td><?php echo Build('xml:<t:childrenscount />', $vars); ?> / <?php echo Build('xml:<t:childrenscount deep="2" />', $vars); ?> / <?php echo Build('xml:<t:childrenscount deep="3" />', $vars); ?></td></tr>

<tr><td colspan="2">
<table cellpadding="0" cellspacing="3" width="100%">
<tr><th>Имя поля в БД</th></tr>
<tr><td>

<select name="field[]" onchange="addfield(this)">
<option> </option>
<optgroup label="Основные">
<option value="id">Id</option>
<option value="version">Версия</option>
<option value="create_time">Время создания</option>
<option value="publish_time">Время публикации</option>
<option value="cash">Кэшировать</option>
<option value="data_only">Ограниченный доступ</option>
<option value="owner_id">Id владельца</option>
<option value="parent_id">Id родителя</option>
<option value="external_class">Внешний класс</option>
<option value="n">Приоритет</option>
<option value="template_src">Корневой шаблон</option>
<option value="childrens_template_src">Шаблон</option>
<option value="link">Ссылка</option>
<option value="use_content_in_head">Исп. содержимое в загол. инф.</option>
<option value="head">Заголовочная информация</option>
<option value="title">Заголовок</option>
<option value="keywords">Ключевые слова</option>
<option value="description">Описание</option>
<option value="image">Изображение</option>
<option value="content">Содержимое</option>
</optgroup>
<?php echo Build('xml:<t:block template="externals@cms/pages/cms/export.xml" />', $vars); ?>
<?php echo Build('xml:<t:childrens deep="1" template="externals@cms/pages/cms/export.xml" length="1" page="1" />', $vars); ?>
<?php echo Build('xml:<t:childrens deep="2" template="externals@cms/pages/cms/export.xml" length="1" page="1" />', $vars); ?>
</select>

</td></tr></table>
</td></tr>

</tbody>

<tbody id="user" style="display: none">
<tr><td>Группа</td><td><select name="group"><option>Все</option>
	<t:rawquery sql="SELECT * FROM {TABLE_GROUP}" xpath="/db/group" template="group" />
</select></td></tr>
<tr><td>Количество записей</td><td><?php echo Build('xml:<t:childrenscount id="{USER_PARENT_ID}" />', $vars); ?></td></tr>

<tr><td colspan="2">
<table cellpadding="0" cellspacing="3" width="100%">
<tr><th>Имя поля в БД</th></tr>
<tr><td>

<select name="field[]" onchange="addfield(this)">
<option> </option>
<optgroup label="Пользовательские">
<option value="user.id">Id</option>
<option value="user.name">Имя</option>
<option value="user.password">Пароль</option>
<option value="user.expire">Срок действия</option>
<option value="user.email">E-mail</option>
<option value="group.id">Id группы</option>
<option value="group.name">Имя группы</option>
</optgroup>
<optgroup label="Основные">
<option value="id">Id</option>
<option value="version">Версия</option>
<option value="create_time">Время создания</option>
<option value="publish_time">Время публикации</option>
<option value="cash">Кэшировать</option>
<option value="data_only">Ограниченный доступ</option>
<option value="owner_id">Id владельца</option>
<option value="parent_id">Id родителя</option>
<option value="external_class">Внешний класс</option>
<option value="n">Приоритет</option>
<option value="template_src">Корневой шаблон</option>
<option value="childrens_template_src">Шаблон</option>
<option value="link">Ссылка</option>
<option value="use_content_in_head">Исп. содержимое в загол. инф.</option>
<option value="head">Заголовочная информация</option>
<option value="title">Заголовок</option>
<option value="keywords">Ключевые слова</option>
<option value="description">Описание</option>
<option value="image">Изображение</option>
<option value="content">Содержимое</option>
</optgroup>
<?php echo Build('xml:<t:block id="{USER_PARENT_ID}" template="externals@cms/pages/cms/export.xml" />'); ?>
</select>

</td></tr>
</table>
</td></tr>

</tbody>

<tbody id="sql" style="display: none">
<tr><td>SQL-запрос</td><td><textarea name="sql" cols="40" rows="10">SELECT * FROM `CmsPage` AS A WHERE 1</textarea></td></tr>
</tbody>

<tr><td>&nbsp;</td><td>
	<div class="button" style="float: left; width: 100px; text-align: center;"
		onclick="document.getElementById('FormExport').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		ОК
	</div>
</td></tr>
</table>
</form>
<br /><br />
<?php

if (isset($result)) echo $result;

?>

</body>
</html>
<?php

exit;

?>