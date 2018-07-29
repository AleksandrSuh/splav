<?php

if (!get_magic_quotes_gpc()) {
	$_POST['csv_enclosure'] = addslashes($_POST['csv_enclosure']);
	$_POST['regexpr_regexpr'] = addslashes($_POST['regexpr_regexpr']);
}

$vars = $GLOBALS['DBC']->getVars(empty($_SERVER['QUERY_STRING']) ? '/' : $_SERVER['QUERY_STRING']);
$id = $vars['id'];
$link = $vars['link'];
$title = str_replace('&amp;', '&', $vars['title']);
$external_class = $vars['external_class'];
$import_num = 0;

if (isset($_POST['type'])) {
$_POST['_a'] = array();
switch ($_POST['type']) {
case 'csv':
	if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
		$fp = fopen($_FILES['csv_file']['tmp_name'], 'r');
		for ($j=0; ($row = fgetcsv($fp, 0, $_POST['csv_delimiter'],
			stripslashes($_POST['csv_enclosure']))) !== false; $j++) {
			$_POST['_a'][$j] = array();
			if (!empty($_POST['publish'])) $_POST['_a'][$j]['action'] = 'saveAndPublish';
			else $_POST['_a'][$j]['action'] = 'save';
			if (!empty($_POST['titleToLink'])) $_POST['_a'][$j]['titleToLink'] = 'true';
			if (!empty($_POST['params'])) $_POST['_a'][$j]['params'] = $_POST['params'];
			$_POST['_a'][$j]['vars'] = array();
			if (!empty($_POST['create_time_now'])) $_POST['_a'][$j]['vars']['create_time'] = 'NOW';
			foreach ($_POST['field_name1'] as $i=>$v)
				if ($v!='' && !empty($_POST['field_name2'][$i])) {
					$field = $row[$v];
					if (extension_loaded('mbstring')) $field = mb_convert_encoding($field,
						'UTF-8', 'UTF-8, Windows-1251');
					$_POST['_a'][$j]['vars'][$_POST['field_name2'][$i]] = $field;
				}

			if (empty($_POST['_a'][$j]['vars']['parent_id'])) $_POST['_a'][$j]['parent_id'] = $id;
			else $_POST['_a'][$j]['parent_id'] = $_POST['_a'][$j]['vars']['parent_id'];
			unset($_POST['_a'][$j]['vars']['parent_id']);

			if (isset($_POST['_a'][$j]['vars']['id'])
				&& !$GLOBALS['DBC']->checkId($_POST['_a'][$j]['vars']['id'])) {
				$_POST['_a'][$j]['id'] = $_POST['_a'][$j]['vars']['id'];
				unset($_POST['_a'][$j]['vars']['id']);
			}
		}
		$import_num = $j;
	}
	break;
case 'regexpr':
	if (is_uploaded_file($_FILES['regexpr_file']['tmp_name'])) {
		preg_match_all(stripslashes($_POST['regexpr_regexpr']),
			file_get_contents($_FILES['regexpr_file']['tmp_name']), $matches);
		for ($j=0; $j<count($matches[0]); $j++) {
			$_POST['_a'][$j] = array();
			if (!empty($_POST['publish'])) $_POST['_a'][$j]['action'] = 'saveAndPublish';
			else $_POST['_a'][$j]['action'] = 'save';
			if (!empty($_POST['titleToLink'])) $_POST['_a'][$j]['titleToLink'] = 'true';
			if (!empty($_POST['params'])) $_POST['_a'][$j]['params'] = $_POST['params'];
			$_POST['_a'][$j]['vars'] = array();
			if (!empty($_POST['create_time_now'])) $_POST['_a'][$j]['vars']['create_time'] = 'NOW';
			foreach ($_POST['field_name1'] as $i=>$v)
				if (!empty($v) && !empty($_POST['field_name2'][$i]))
					$_POST['_a'][$j]['vars'][$_POST['field_name2'][$i]] = $matches[$v][$k];

			if (empty($_POST['_a'][$j]['vars']['parent_id'])) $_POST['_a'][$j]['parent_id'] = $id;
			else $_POST['_a'][$j]['parent_id'] = $_POST['_a'][$j]['vars']['parent_id'];
			unset($_POST['_a'][$j]['vars']['parent_id']);

			if (isset($_POST['_a'][$j]['vars']['id'])
				&& !$GLOBALS['DBC']->checkId($_POST['_a'][$j]['vars']['id'])) {
				$_POST['_a'][$j]['id'] = $_POST['_a'][$j]['vars']['id'];
				unset($_POST['_a'][$j]['vars']['id']);
			}
		}
		$import_num = $j;
	}
	break;
case 'xml':
	if (is_uploaded_file($_FILES['xml_file']['tmp_name'])) {
		$import_num = preImportXML($id, $_FILES['xml_file']['tmp_name'], isset($_POST['xml_entitydecode']),
			$_POST['field_name1'], $_POST['field_name2'],
			$_POST['publish'], $_POST['titleToLink'], $_POST['create_time_now'],
			$_POST['params']);
	}
	break;
case 'zip':
	if (is_uploaded_file($_FILES['zip_file']['tmp_name'])) $zipname = $_FILES['zip_file']['tmp_name'];
	else if (!empty($_POST['zip_file2'])) $zipname = DIR_ROOT.$_POST['zip_file2'];
	else break;
	$zip = new ZipArchive();
	$zip->open($zipname);
	$y = date('Y');
	$m = date('m');
	$u = uniqid();
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast')) mkdir(DIR_ROOT.DIR_FB.'/_fast');
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast/'.$y)) mkdir(DIR_ROOT.DIR_FB.'/_fast/'.$y);
	if (!file_exists(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m))
		mkdir(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m);
	if(!file_exists(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m.'/'.$u))
		mkdir(DIR_ROOT.DIR_FB.'/_fast/'.$y.'/'.$m.'/'.$u);
	$extractPath = DIR_FB.'/_fast/'.$y.'/'.$m.'/'.$u.'/';

	for ($j=0; $name = $zip->getNameIndex($j); $j++) {
		$ext = strtolower(preg_replace('/^.*\.([^\.]+)$/', '$1', $name));
		$zip->extractTo(DIR_ROOT.$extractPath, $name);
		
		$filelink = $extractPath.$name;
		$file = DIR_ROOT.$filelink;
		$fullname = $name;
		$name = preg_replace('/^(.*)\.(\w+)$/', '$1', $name);

		$_POST['_a'][$j] = array();
		if (!empty($_POST['publish'])) $_POST['_a'][$j]['action'] = 'saveAndPublish';
		else $_POST['_a'][$j]['action'] = 'save';
		if (!empty($_POST['titleToLink'])) $_POST['_a'][$j]['titleToLink'] = 'true';
		if (!empty($_POST['params'])) $_POST['_a'][$j]['params'] = $_POST['params'];
		$_POST['_a'][$j]['vars'] = array();
		if (!empty($_POST['create_time_now'])) $_POST['_a'][$j]['vars']['create_time'] = 'NOW';
		foreach ($_POST['field_name1'] as $i=>$v) {
			if (empty($v) || empty($_POST['field_name2'][$i])) continue;
			$t =& $_POST['_a'][$j]['vars'][$_POST['field_name2'][$i]];
			switch ($v) {
				case 'name': $t = $name; break;
				case 'fullname': $t = $fullname; break;
				case 'link': $t = $filelink; break;
				case 'file': $t = $file; break;
				case 'size': $t = filesize($file); break;
				case 'sizeK': $t = sprintf('%.2f', filesize($file)/1024); break;
				case 'sizeM': $t = sprintf('%.2f', filesize($file)/1024/1024); break;
				case 'img':
					switch ($ext) {
						case 'jpeg': case 'jpg': case 'gif': case 'png':
							$t = '<img src="'.$filelink.'" />';
							break;
					}
					break;
				case 'imgsize':
					$img = false;
					switch ($ext) {
						case 'jpg': case 'jpeg': $img = imagecreatefromjpeg($file); break;
						case 'gif': $img = imagecreatefromgif($file); break;
						case 'png': $img = imagecreatefrompng($file); break;
					}
					if ($img) {
						$t = imagesx($img).'x'.imagesy($img);
						imagedestroy($img);
					}
					break;
			}
		}
		
		if (empty($_POST['_a'][$j]['vars']['parent_id'])) $_POST['_a'][$j]['parent_id'] = $id;
		else $_POST['_a'][$j]['parent_id'] = $_POST['_a'][$j]['vars']['parent_id'];
		unset($_POST['_a'][$j]['vars']['parent_id']);

		if (isset($_POST['_a'][$j]['vars']['id'])
			&& !$GLOBALS['DBC']->checkId($_POST['_a'][$j]['vars']['id'])) {
			$_POST['_a'][$j]['id'] = $_POST['_a'][$j]['vars']['id'];
			unset($_POST['_a'][$j]['vars']['id']);
		}
	}
	$zip->close();
	$import_num = $j;
	break;
}
CCore::checkPost(CCore::CHECK_POST_NONE);

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Импорт данных - <?= $title ?></title>
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
	var csv = document.getElementById('csv');
	var regexpr = document.getElementById('regexpr');
	var xml = document.getElementById('xml');
	var zip = document.getElementById('zip');

	csv.style.display = csv.id==id ? '' : 'none';
	regexpr.style.display = regexpr.id==id ? '' : 'none';
	xml.style.display = xml.id==id ? '' : 'none';
	zip.style.display = zip.id==id ? '' : 'none';
}

function addfield(select) {
	var tr = select.parentNode.parentNode;
	if (select.value!='' && tr.parentNode.lastChild==tr) {

		var newtr = tr.cloneNode(true);
		tr.parentNode.appendChild(newtr);
	}
}
function fb() {
	var wnd = window.open('/cms/fb', '_blank',
		'menubar=0,status=0,resizable=1,scrollbars=1,width=640,height=480');
}
function fbReturn(sUrl) {
	document.getElementById('zip_file2').value = sUrl;
}
</script>
</head>
<body>

<div class="tophelp">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Импорт данных</b> - <?= $title ?></div>
</div>&nbsp;

<form id="FormImport" enctype="multipart/form-data" method="post" style="margin-top: 150px; width: 500px;">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />

<?php

if (isset($_POST['type']))
	echo '<p style="color: #009900;">Импортировано записей: ',$import_num,'</p>';

?>

<table cellpadding="0" cellspacing="3" >
<tr><td width="40%">Тип</td><td><select name="type" onchange="selecttype(this.value)">
	<option value="csv">CSV</option>
	<option value="regexpr">TXT+RegExpr</option>
	<option value="xml">XML</option>
	<option value="zip">ZIP</option>
</select></td></tr>

<tbody id="csv">
<tr><td>Файл</td><td><input name="csv_file" type="file" /></td></tr>
<tr><td>Разделитель поля</td><td><input name="csv_delimiter" type="text" size="4" value=";" /></td></tr>
<tr><td>Символ ограничителя поля </td><td><input name="csv_enclosure" type="text" size="4" value="&quot;" /></td></tr>
</tbody>

<tbody id="regexpr" style="display: none">
<tr><td>Файл</td><td><input name="regexpr_file" type="file" /></td></tr>
<tr><td valign="top">Регулярное выражение</td><td><textarea name="regexpr_regexpr" cols="30" rows="5">//iu</textarea></td></tr>
</tbody>

<tbody id="xml" style="display: none">
<tr><td>Файл</td><td><input name="xml_file" type="file" /></td></tr>
<tr><td colspan="2"><input name="xml_entitydecode" type="checkbox" checked="checked" /> Спец-символы закодированы</td></tr>
</tbody>

<tbody id="zip" style="display: none">
<tr><td>Загрузить архив</td><td><input name="zip_file" type="file" /></td></tr>
<tr><td>Выбрать архив</td><td><input id="zip_file2" name="zip_file2" type="text" />
	<button onclick="fb(); return false;">Обзор...</button></td></tr>
<tr><td colspan="2">
Используйте следующие имена полей:
<ul>
	<li><i>name</i> &mdash; имя файла</li>
	<li><i>fullname</i> &mdash; имя файла с расширением</li>
	<li><i>link</i> &mdash; ссылка на файл от корня</li>
	<li><i>file</i> &mdash; путь к файлу на сервере</li>
	<li><i>size</i> &mdash; размер файла в байтах</li>
	<li><i>sizeK</i> &mdash; размер файла в Кб</li>
	<li><i>sizeM</i> &mdash; размер файла в Мб</li>
	<li><i>img</i> &mdash; изображение</li>
	<li><i>imgsize</i> &mdash; размеры изображения</li>
</ul>
</td></tr>
</tbody>

<tr><td colspan="2">

<table cellpadding="0" cellspacing="3" width="100%">
<tr><th>Имя/Номер поля</th><th>Имя поля в БД</th></tr>
<tr><td><input name="field_name1[]" type="text" size="4" /></td><td>

<select name="field_name2[]" onchange="addfield(this)">
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
<?php

if (!empty($external_class)) {
	echo '<optgroup label="Дополнительные ('.$external_class.')">';
	foreach ($GLOBALS['EXTERNAL_CLASS'][$external_class]['property'] as $property)
		echo '<option value="external.'.$property['name'].'">'.$property['name'].'</option>';
	echo '</optgroup>';
}

?>
</select>


<!--<select name="add[]">
<option></option>
<option value="replace">заменить</option>
</select>-->

</td></tr>
</table>

</td></tr>
<tr><td colspan="2"><input name="publish" type="checkbox" checked="" /> Опубликовать</td></tr>
<tr><td colspan="2"><input name="titleToLink" type="checkbox" checked="checked" /> Сформировать ссылку из заголовка</td></tr>
<tr><td colspan="2"><input name="create_time_now" type="checkbox" checked="checked" /> Установить текущую дату создания</td></tr>
<tr><td>Дополнительные параметры</td><td><input type="text" name="params" /></td></tr>
<tr><td>&nbsp;</td><td>
	<div class="button" style="float: left; width: 100px; text-align: center;"
		onclick="CmsLoadWndShow(); document.getElementById('FormImport').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		ОК
	</div>
</td></tr>
</table>
</form>

</body>
</html>
<?php

exit;

?>