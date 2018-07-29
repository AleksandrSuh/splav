<?php

$Style = isset($_GET['style']) ? stripslashes($_GET['style']) : '';
$Color			= preg_match('/[^\-]?color\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$BackgroundColor	= preg_match('/background-color\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$FontFamily		= preg_match('/font-family\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$FontSize		= preg_match('/font-size\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$TextAlign		= preg_match('/text-align\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$FontStyle		= preg_match('/font-style\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$TextDecoration		= preg_match('/text-decoration\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';
$FontWeight		= preg_match('/font-weight\:\s*([^\;]+)\;/', $Style, $match) ? $match[1] : '';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS Редактор стиля</title>
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
function StyleEditorReturn(oForm) {
	var buf = '';
	if (oForm.elements['color'].value!='') buf += 'color: '+oForm.elements['color'].value+'; ';
	if (oForm.elements['background-color'].value!='')
		buf += 'background-color: '+oForm.elements['background-color'].value+'; ';
	if (oForm.elements['font-family'].value!='') buf += 'font-family: '+oForm.elements['font-family'].value+'; ';
	if (oForm.elements['font-size'].value!='') buf += 'font-size: '+oForm.elements['font-size'].value+'; ';
	if (oForm.elements['text-align'].value!='') buf += 'text-align: '+oForm.elements['text-align'].value+'; ';
	if (oForm.elements['italic'].checked)	 buf += 'font-style: italic; ';
	if (oForm.elements['underline'].checked) buf += 'text-decoration: underline; ';
	if (oForm.elements['bold'].checked) buf += 'font-weight: bold; ';
	window.opener.document.getElementById('<?=$_GET['form']?>').elements['<?=$_GET['id']?>'].value = buf;
	window.close();
}
</script>
</head>
<body>
<OBJECT id="dlgHelper" CLASSID="clsid:3050f819-98b5-11cf-bb82-00aa00bdce0b" WIDTH="0px" HEIGHT="0px"></OBJECT>
<form id="FormStyleEditor"><table cellpadding="0" cellspacing="3" width="100%">
<tr><th colspan="2">Редактор стиля</th></tr>
<tr><td>Цвет</td><td>
	<input type="text" name="color" value="<?=$Color?>" />
	<a href="#cp" onclick="CallColorDlg('FormStyleEditor', 'color'); return false;" style="white-space: nowrap;">
		[выбрать цвет]</a></td>
<tr><td>Цвет фона</td><td>
	<input type="text" name="background-color" value="<?=$BackgroundColor?>" />
		<a href="#cp" onclick="CallColorDlg('FormStyleEditor', 'background-color'); return false;"
			style="white-space: nowrap;">[выбрать цвет]</a></td>
<tr><td>Шрифта</td><td><select name="font-family">
	<option value=""<?=$FontFamily=='' ? ' selected="selected"' : ''?>></option>
    <option value="Arial"<?=$FontFamily=='Arial' ? ' selected="selected"' : ''?>>Arial</option>
    <option value="'Courier New'"<?=$FontFamily=='\'Courier New\'' ? ' selected="selected"' : ''?>>
	Courier New</option>
    <option value="'Lucida Console'"<?=$FontFamily=='\'Lucida Console\'' ? ' selected="selected"' : ''?>>
	Lucida Console</option>
    <option value="Tahoma"<?=$FontFamily=='Tahoma' ? ' selected="selected"' : ''?>>Tahoma</option>
    <option value="'Times New Roman'"<?=$FontFamily=='\'Times New Roman\'' ? ' selected="selected"' : ''?>>
	Times New Roman</option>
    <option value="Verdana"<?=$FontFamily=='Verdana' ? ' selected="selected"' : ''?>>Verdana</option>
</select></td>
<tr><td>Размер шрифта</td><td><select name="font-size">
	<option value=""<?=$FontSize=='' ? ' selected="selected"' : ''?>></option>
    <option value="10px"<?=$FontSize=='10px' ? ' selected="selected"' : ''?>>10px</option>
    <option value="13px"<?=$FontSize=='13px' ? ' selected="selected"' : ''?>>13px</option>
    <option value="16px"<?=$FontSize=='16px' ? ' selected="selected"' : ''?>>16px</option>
    <option value="18px"<?=$FontSize=='18px' ? ' selected="selected"' : ''?>>18px</option>
    <option value="24px"<?=$FontSize=='24px' ? ' selected="selected"' : ''?>>24px</option>
    <option value="32px"<?=$FontSize=='32px' ? ' selected="selected"' : ''?>>32px</option>
    <option value="48px"<?=$FontSize=='48px' ? ' selected="selected"' : ''?>>48px</option>
</select></td>
<tr><td>Выравнивание</td><td><select name="text-align">
	<option value=""<?=$TextAlign=='' ? ' selected="selected"' : ''?>></option>
    <option value="left"<?=$TextAlign=='left' ? ' selected="selected"' : ''?>>По левому краю</option>
    <option value="center"<?=$TextAlign=='center' ? ' selected="selected"' : ''?>>По центру</option>
    <option value="right"<?=$TextAlign=='right' ? ' selected="selected"' : ''?>>По правому краю</option>
    <option value="justify"<?=$TextAlign=='justify' ? ' selected="selected"' : ''?>>По ширине</option>
</select></td>
<tr><td>Курсив</td><td><input type="checkbox" name="italic"<?=$FontStyle=='italic' ? ' checked="checked"' : ''?> />
	</td></tr>
<tr><td>Подчеркнутый</td><td>
	<input type="checkbox" name="underline"<?=$TextDecoration=='underline' ? ' checked="checked"' : ''?> />
	</td></tr>
<tr><td>Полужирный</td><td><input type="checkbox" name="bold"<?=$FontWeight=='bold' ? ' checked="checked"' : ''?> />
	</td></tr>
<tr><td colspan="2">
	<button onClick="StyleEditorReturn(this.form)">&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;</button>
	</td></tr>
</table></form>
</body>
</html>
<?php

exit;

?>