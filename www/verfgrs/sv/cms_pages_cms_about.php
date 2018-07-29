<?php

if (AUTH_SERIAL=='') {
	$serial = 'отсутствует';
	$color = '#990000';
} else {
	$serial = AUTH_SERIAL;
	$key = @file_get_contents('http://auth43.artgk-cms.ru/?'.AUTH_SERIAL);
	list($db, $subdomains, $hidelink) = CCore::checkSiteKey($key);
	if ($db || $subdomains || $hidelink) $color = '#009900';
	else $color = '#990000';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ArtGK CMS</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
<style type="text/css">
.license {height: 300px;}
</style>
</head>
<body style="background-image: url('/cms/img/cms/left.jpg'); background-repeat:no-repeat;">

<div class="tophelp">
	<img src="/cms/img/cms/tophelp-left.jpg" style="float: left;" />
	<img src="/cms/img/cms/tophelp-right.jpg" style="float: right" />
	<div id="oHelp"><b>ArtGK CMS</b> <?= CMS_VERSION ?> - <small>Свидетельство о государственной регистрации программы для ЭВМ № 2008613398</small></div>
</div>&nbsp;

<div style="float: right; clear: right; margin: 20px; padding: 10px; border: 1px solid LightGrey">
	Серийный номер: <span style="color: <?= $color ?>;"><?= $serial ?></span><br />
	<small>
		Лицензия, тех.поддержка: <?= ($db ? 'Да' : 'Нет') ?><br />
		Лицензия на поддомены: <?= ($subdomains ? 'Да' : 'Нет') ?><br />
		Убрать ссылку: <?= ($hidelink ? 'Да' : 'Нет') ?><br />
	</small>
</div>

<div style="margin: 150px 20px 20px;">
<table cellpadding="0" cellspacing="3" width="100%">
<tr><td><div class="license"><?php echo @file_get_contents(DIR_ROOT.'/cms/license.txt'); ?></div></td></tr>
<tr><td align="center">
	<div class="button" style="width: 100px; margin-right: 20px; text-align: center;" onclick="window.close();">
		<img src="/cms/img/cms/button-left.jpg" style="float: left;" />
		<img src="/cms/img/cms/button-right.jpg" style="float: right" />
		OK
	</div>
</td></tr>
</table>
</div>

</body>
</html>

<?php

exit;

?>