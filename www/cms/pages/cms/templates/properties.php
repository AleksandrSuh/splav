<?php

if (isset($_GET['src'])) $Src = preg_replace('/^.*\@(.*)$/', '$1', $_GET['src']);
else $GLOBALS['CORE']->HTTPReturn(404);

$Src = $GLOBALS['TEMPLATES']->getRealSrc($Src);
$d = $GLOBALS['TEMPLATES']->getTemplateDocment($Src);

$es = $d->documentElement->getElementsByTagNameNS('/templates/ns', 'info');
if ($es->length>0) $info = $es->item(0);

if (isset($info) && isset($_POST['Save'])) {
	foreach ($info->childNodes as $var) if ($var->nodeType==XML_ELEMENT_NODE && $var->namespaceURI=='/templates/ns'
		&& $var->localName=='setvar')
		$var->setAttribute('value', stripslashes($_POST['Opts'][$var->getAttribute('name')]));
	$d->save($Src);
	header('location: '.$_SERVER['REQUEST_URI'].'?src='.stripslashes($_GET['src']));
	exit;
}

$fmt = date('Y-m-d H:i:s', filemtime($Src));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS свойства шаблона</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
<script type="text/javascript">
var sInitColor=null;
function CallColorDlg(sFormId, sId) {
	if (sInitColor==null) var sColor = dlgHelper.ChooseColorDlg();
	else var sColor = dlgHelper.ChooseColorDlg(sInitColor);
	sColor = sColor.toString(16);
	if (sColor.length < 6) {
		var sTempString = "000000".substring(0,6-sColor.length);
		sColor = sTempString.concat(sColor);
	}
	document.getElementById(sFormId).elements[sId].value = '#'+sColor;
}
function CallStyleDlg(sFormId, sId) {
	var sStyle = document.getElementById(sFormId).elements[sId].value
	window.open('/cms/css/style?form='+sFormId+'&id='+sId+'&style='+encodeURIComponent(sStyle), '_blank',
		'menubar=0,status=0,resizable=1,scrollbars=1,width=450,height=400');
}
</script>
<style type="text/css">
td {
	border: 1px solid LightGrey;
	padding: 3px;
}
table {
	border: 1px solid LightGrey;
}
th {
	background-color: #d5f0fd;
	color: #0079a3;
	line-height: 20px;
}
a {
	text-decoration: underline;
}
</style>
</head>
<body>
<img src="/cms/img/cms/left.jpg" width="80" style="float: left;" />

<div class="tophelp" style="width: 240px;">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Свойства шаблона</b> - <?=basename($Src)?><br /><small>Время модификации: <?=$fmt?></small></div>
</div>&nbsp;

<OBJECT id="dlgHelper" CLASSID="clsid:3050f819-98b5-11cf-bb82-00aa00bdce0b" WIDTH="0px" HEIGHT="0px"></OBJECT>
<form id="FormTemplateOptions" method="post" action="?src=<?=stripslashes($_GET['src'])?>">
<table cellpadding="0" cellspacing="3" width="100%">
<?php

if (isset($info)) {
	foreach ($info->childNodes as $var) if ($var->nodeType==XML_ELEMENT_NODE && $var->namespaceURI=='/templates/ns'
		&& $var->localName=='setvar') {
		$name = $var->getAttribute('name');
		$value = $var->getAttribute('value');
		$type = $var->getAttribute('type');
		$description = $var->getAttribute('description');
		switch ($type) {
			case 'url': 
				$buf = '<input type="text" name="Opts['.$name.']" value="'.$value
					.'" /> <a href="#fb" onclick="FBDlg(\'FormTemplateOptions\', \'Opts['.$name
					.']\'); return false;" style="white-space: nowrap;">[файловый браузер]</a>
<a href="/cms/map" target="_blank" onclick="Select(\'FormTemplateOptions\', \'Opts['
				.$name.']\', \'link\'); return false;" style="white-space: nowrap;">[карта сайта]</a>';
				break;
			case 'color':
				$buf = '<input type="text" name="Opts['.$name.']" value="'.$value
					.'" /> <a href="#cp" onclick="CallColorDlg(\'FormTemplateOptions\', \'Opts['
					.$name.']\'); return false;" style="white-space: nowrap;">[выбрать цвет]</a>';
				break;
			case 'id':
				$buf = '<input type="text" name="Opts['.$name.']" value="'.$value
					.'" /> <a href="/cms/map" target="_blank"
onclick="Select(\'FormTemplateOptions\', \'Opts['.$name.']\', \'id\'); return false;" style="white-space: nowrap;">
[карта сайта]</a>';
				break;
			case 'checkbox':
				$buf = '<input type="checkbox" name="Opts['.$name.']" value="checked"'
					.($value=='checked' ? ' checked="checked"' : '').' />';
				break;
			case 'style': 
				$buf = '<input type="text" name="Opts['.$name.']" value="'.htmlspecialchars($value)
					.'" /> <a href="#st" onclick="CallStyleDlg(\'FormTemplateOptions\', \'Opts['
					.$name.']\'); return false;" style="white-space: nowrap;">[редактор стиля]</a>';
				break;
			case '':
				$buf = '<input type="text" name="Opts['.$name.']" value="'.$value.'" />';
				break;
			default:
				$opts = explode('|', $type);
				$buf = '<select name="Opts['.$name.']">';
				foreach ($opts as $opt) $buf .= '<option'.($opt==$value ? ' selected="selected"' : '')
					.' value="'.$opt.'">'.$opt.'</option>';
				$buf .= '</select>';
				break;
		}
		echo '<tr title="'.$description.'"><td>'.$name.'</td><td>'.$buf.'</td></tr>';
	}
	echo '<tr><td colspan="2"><input type="submit" name="Save" value="Сохранить" /></td></tr>';
	echo '<tr><th colspan="2">Информация о шаблоне</th></tr><tr><td>Источник</td><td><a href="'
		.$info->getAttribute('url').'">'.$info->getAttribute('url').'</a></td></tr><tr><td>Категория</td><td>'
		.$info->getAttribute('category').'</td></tr><tr><td colspan="2">'.$info->getAttribute('description')
		.'</td></tr><tr><td colspan="2"><img src="'.$info->getAttribute('img')
		.'" alt="эскиз шаблона" /></td></tr>';
} else {
	echo '<tr><td colspan="2">Шаблон не содержит опций и информации</td></tr>';
}

?>
</table></form>
</body>
</html>
<?php

exit;

?>