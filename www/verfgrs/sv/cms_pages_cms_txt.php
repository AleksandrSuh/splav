<?php

if (!isset($_GET['file'])) $GLOBALS['CORE']->HTTPReturn(404);
$File = $_GET['file'];

switch ($File) {
	case '/style.css':
	case '/.htaccess':
	case '/robots.txt':
		break;
	default:
		$GLOBALS['CORE']->HTTPReturn(404);
		break;
}

if (isset($_POST['text'])) {
	file_put_contents(DIR_ROOT.$File, $_POST['text']);
	header('location: '.$_SERVER['REQUEST_URI'].'?file='.$File);
	exit;
}

if (file_exists(DIR_ROOT.$File)) $filemtime = 'Файл модифицирован: '.date('Y-m-d H:i:s', filemtime(DIR_ROOT.$File));
else $filemtime = 'Файл не существует';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS редактор <?=basename($File)?></title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
</head>
<body>
<img src="/cms/img/cms/left.jpg" width="80" style="float: left;" />

<div class="tophelp" style="width: 240px;">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b><?=$File?></b><br /><small><?=$filemtime?></small></div>
</div>&nbsp;

<form method="post" id="FormTxt" style="clear: both;"><table cellpadding="0" cellspacing="3" width="100%">
<tr><td>
<textarea name="text" style="width: 99%; height: 100px;"><?php echo @file_get_contents(DIR_ROOT.$File); ?></textarea>
</td></tr>
<tr><td align="center">
	<div class="button" style="width: 100px; margin-right: 20px; text-align: center;" onclick="document.getElementById('FormTxt').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Сохранить
	</div>
</td></tr>
</table>
</form>
</body>
</html>
<?php

exit;

?>