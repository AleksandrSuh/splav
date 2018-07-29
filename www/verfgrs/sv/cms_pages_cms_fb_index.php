<?php

define('DIR_BROWSE',
	(isset($_GET['d']) && preg_match('/^'.str_replace('/', '\/', DIR_FB).'/', $_GET['d']) ? $_GET['d'] : DIR_FB));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
<title><?php echo DIR_BROWSE; ?></title><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link type="text/css" rel="stylesheet" href="/cms/css/fb.css" />
<link type="text/css" rel="stylesheet" href="/cms/css/editor.css" />
<script type="text/javascript" src="/cms/js/ajax.js"></script>
<script type="text/javascript" src="/cms/js/fb.js"></script>
<script type="text/javascript" src="/cms/js/modaldlg.js"></script>
</head>

<body>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td colspan="2" height="1%">
<div class="path">
<?php

$dirs = explode('/', DIR_BROWSE);
$path = '';

for ($i=1; $i<count($dirs); $i++) {
	$path .= '/'.$dirs[$i];
	echo ' &raquo; <a href="/cms/fb/?d='.$path.'">'.$dirs[$i].'</a>';
}

?>
</div>
<div class="ToolBarEditorFb" id="oToolBarDiv" style="background-color: threedface; position: fixed; width: 100%; z-index: 999; top: 20px;">
<a href="javascript:Props()" title="Свойства"><img src="/cms/img/toolicons/props.gif" /></a>
<a href="javascript:NewDir()" title="Создать папку"><img src="/cms/img/toolicons/folder.gif" /></a>
<a href="javascript:Refresh()" title="Обновить"><img src="/cms/img/toolicons/refresh.gif" /></a>
<!--<a href="javascript:Up()" title="Вверх"><img src="/cms/img/toolicons/up.gif" /></a>-->
<a href="javascript:Cut()" title="Вырезать"><img src="/cms/img/toolicons/cut.gif" /></a>
<a href="javascript:Copy()" title="Копировать"><img src="/cms/img/toolicons/copy.gif" /></a>
<a href="javascript:Paste()" title="Вставить"><img src="/cms/img/toolicons/paste.gif" /></a>
<a href="javascript:Delete()" title="Удалить"><img src="/cms/img/toolicons/del.gif" /></a>
<a href="javascript:Download()" title="Скачать файл"><img src="/cms/img/toolicons/download.gif" /></a>
<a href="#" title="Загрузить файл на сервер">
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="20" height="20" id="up" align="middle">
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="allowFullScreen" value="false" />
		<param name="movie" value="/cms/img/toolicons/up4.swf?d=<?= $_GET['d'] ?>&PHPSESSID=<?= $_COOKIE['PHPSESSID'] ?>" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" />
		<embed src="/cms/img/toolicons/up4.swf?d=<?= $_GET['d'] ?>&PHPSESSID=<?= $_COOKIE['PHPSESSID'] ?>" quality="high" wmode="transparent" bgcolor="#ffffff" width="20" height="20" name="up" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
</a>
<a href="javascript:Zip()" title="Добавить в архив Zip"><img src="/cms/img/toolicons/zip.gif" /></a>
<a href="javascript:Unzip()" title="Извлечь из Zip-архива"><img src="/cms/img/toolicons/unzip.gif" /></a>
</div>
<div style="height: 49px;"></div>

</td></tr><tr><td valign="top" width="1%">
<table class="main" cellspacing="0" cellpadding="0"><tr><th>
<a href="javascript: SelectAll();" class="SelectAll">Все</a></th><th>Имя</th><th>Размер</th></tr>
<?php

$dirList = '';
$fileList = '';
foreach (glob(DIR_ROOT.DIR_BROWSE.'/*') as $filename)  {
	$pi = pathinfo($filename);
	$path = DIR_BROWSE.'/'.$pi['basename'];
	if (is_dir($filename)) $dirList .= '<tr><td><input type="checkbox" class="select" name="'
		.$path.'" /></td><td><a href="?d='
		.$path.'" title="'
		.$pi['basename'].'"><img src="/cms/img/filetypes/folder.gif" width="16" height="16" /> '
		.$pi['basename'].'</a></td><td>&nbsp;</td></tr>';
	else {
		$inf = '&nbsp;';
		$icon = 'unknown.gif';
		$href = 'javascript: Click(\''.$path.'\')';
		$mouseover = 'View(\''.$path.'\')';
		$pi['extension'] = isset($pi['extension']) ? strtolower($pi['extension']) : '';
		switch ($pi['extension']) {
			case 'zip': case 'rar': $icon = 'archive.gif'; break;
			case 'xls': $icon = 'excel.gif'; break;
			case 'exe': $icon = 'exe.gif'; break;
			case 'htm': case 'html': $icon = 'html.gif'; break;
			case 'gif': case 'jpg': case 'jpeg': case 'png': $icon = 'image.gif'; break;
			case 'mpg': case 'mpeg': case 'avi': case 'wmv': $icon = 'movie.gif'; break;
			case 'pdf': $icon = 'pdf.gif'; break;
			case 'mp3': case 'wma': case 'waw': case 'ogg': $icon = 'sound.gif'; break;
			case 'swf': $icon = 'swf.gif'; break;
			case 'txt': case 'ini': case 'log': $icon = 'txt.gif'; break;
			case 'doc': case 'rtf': $icon = 'word.gif'; break;
			case 'xml': $icon = 'xml.gif'; break;
		}
		$fileList .= '<tr><td><input type="checkbox" class="select" name="'
			.$path.'" /></td><td><a href="'
			.$href.'" onmouseover="'
			.$mouseover.'" title="'
			.$pi['basename'].'"><img src="/cms/img/filetypes/'.$icon.'" width="16" height="16" /> '
			.$pi['basename'].'</a></td><td align="right">'
			.number_format(filesize($filename), 0, ',', ' ').'</td></tr>';
	}
}

echo $dirList, $fileList;
echo '</table></td><td valign="top" width="99%"><div id="View"></div></td></tr></table></body></html>';

exit;

?>