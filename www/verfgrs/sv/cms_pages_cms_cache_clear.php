<?php

function DeleteSubDir($Dirname) {
	foreach (glob($Dirname.'/*') as $filename)
		if (is_dir($filename)) {
			DeleteSubDir($filename);
			rmdir($filename);
		} else {
			$GLOBALS['size'] += filesize($filename);
			unlink($filename);
		}
}

$GLOBALS['size'] = 0;

if (file_exists(DIR_ROOT.'/_cache')) {
	DeleteSubDir(DIR_ROOT.'/_cache');
	rmdir(DIR_ROOT.'/_cache');
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Кэш очищен</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
</head>

<body>
<table cellpadding="0" cellspacing="3" width="100%">
<tr><th>Кэш очищен</th></tr>
<tr><td><?= $GLOBALS['size']/1000 ?> Кб</td></tr>
</table>
</body>
</html>
<?php

exit;

?>