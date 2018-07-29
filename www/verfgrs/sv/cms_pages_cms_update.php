<?php

function CheckUpdates(&$Size, &$Version) {
	$headers = @get_headers('http://update43.artgk-cms.ru/?version='.urlencode(CMS_VERSION)
		.(!isset($_GET['beta']) && UPDATE_CHECK_ALPHA=='true' ? '&alpha=true' : '').'&beta='
		.(isset($_GET['beta']) ? $_GET['beta'] : UPDATE_CHECK_BETA), 1);
	if ($headers['Content-Type']=='application/zip') {
		$Size = (int)$headers['Content-Length'];
		$i = 0;
		while ($Size>=1000) {
			$Size /= 1000;
			$i++;
		}
		switch ($i) {
			case 0: $measure = 'b'; break;
			case 1: $measure = 'Kb'; break;
			case 2: $measure = 'Mb'; break;
		}
		$Size = sprintf('%.1f', $Size).$measure;
		preg_match('/.*\"(\d+)(\w+)?\.zip\"$/', $headers['Content-Disposition'], $matches);
		$Version = date('Y-m-d H:i:s', $matches[1])
			.(isset($matches[2]) && $matches[2]!='' ? ' '.$matches[2] : '');
		return true;
	} else return false;
}

if (file_exists(DIR_ROOT.'/update.php') && getenv('REMOTE_ADDR')!='127.0.0.1') {
	require_once DIR_ROOT.'/update.php';
	unlink(DIR_ROOT.'/update.php');
}

$size = '';
$version = '';
$has_updates = CheckUpdates($size, $version);

$beta_text = '';
if (UPDATE_CHECK_BETA=='true') {
	if (isset($_GET['beta']) && $_GET['beta']=='false')
		$beta_text = '<a href="?beta=true">[проверить бета-версии]</a>';
	else if ($has_updates)
		$beta_text = '<a href="?beta=false">[проверить стабильные версии]</a>';
}


if ($has_updates && isset($_POST['install'])) {
	file_put_contents('update.zip', file_get_contents('http://update43.artgk-cms.ru/?version='
		.urlencode(CMS_VERSION).(!isset($_GET['beta']) && UPDATE_CHECK_ALPHA=='true' ? '&alpha=true' : '')
		.'&beta='.(isset($_GET['beta']) ? $_GET['beta'] : UPDATE_CHECK_BETA)));
	$zip = new ZipArchive();
	$zip->open('update.zip');
	for ($i=0; $name = $zip->getNameIndex($i); $i++) {
		$extractToName = $name;
		$extractPath = './';
		$zip->extractTo($extractPath, $extractToName);
	}
	$zip->close();
	unlink('update.zip');
	if (file_exists(DIR_ROOT.'/update.php')) {
		require_once DIR_ROOT.'/update.php';
		unlink(DIR_ROOT.'/update.php');
	}
	header('location: '.$_SERVER['REQUEST_URI']);
} else if ($has_updates) {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS Update</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
</head>
<body>
<img src="/cms/img/cms/left.jpg" width="80" style="float: left;" />

<div class="tophelp" style="width: 240px;">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>Доступно обновление</b><br /><small>'.$beta_text.'</small></div>
</div>&nbsp;
<div style="border: 1px solid LightGrey; margin: 70px 20px 20px; padding: 10px;">
<table cellpadding="0" cellspacing="3" width="100%">
<tr><td align="right" style="color: #75c5f0;">Текущая версия:</td><td>'.CMS_VERSION.'</td></tr>
<tr><td align="right" style="color: #75c5f0;">Новая версия:</td><td>'.$version.'</td></tr>
<tr><td align="right" style="color: #75c5f0;">Размер:</td><td>'.$size.'</td></tr>
<tr><td colspan="2" align="center">
	<form method="post" id="FormInstall"><input type="hidden" name="install" value="Установить" /></form>
	<div class="button" style="width: 90px; margin-right: 20px; text-align: center;"
		onclick="document.getElementById(\'FormInstall\').submit();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		Установить
	</div>
</td></tr>
</table></div>

</body>
</html>';
} else {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS Update</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<script type="text/javascript" src="/cms/js/cms.js"> </script>
</head>
<body>
<img src="/cms/img/cms/left.jpg" width="80" style="float: left;" />

<div class="tophelp" style="width: 240px;">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>У вас установлена последняя версия</b><br /><small>'.$beta_text.'</small></div>
</div>&nbsp;
<div style="border: 1px solid LightGrey; margin: 70px 20px 20px; padding: 10px;">
<table cellpadding="0" cellspacing="3" width="100%">
<tr><td align="right" style="color: #75c5f0;">Текущая версия:</td><td>'.CMS_VERSION.'</td></tr>
<tr><td colspan="2">После установки новой версии не забывайте использовать
Ctrl+F5 в браузере для обновления страницы с очисткой кэша.</td></tr>
</table></div>

</body>
</html>';
}


exit;

?>