<?php

header('Content-Type: application/xml; charset=UTF-8');

if (!isset($_GET['url']) || $_GET['url']=='') $GLOBALS['CORE']->HTTPReturn(404);
$Url = $_GET['url'];

$q = parse_url($Url, PHP_URL_QUERY);
$q = parse_str($q, $buf);
$filename = basename($buf['file']);

switch (pathinfo($filename, PATHINFO_EXTENSION)) {
	case 'xml':
		while (file_exists(DIR_ROOT.'/templates/'.$filename))
			$filename = str_replace('.xml', '_.xml', $filename);
		($buf = @file_get_contents($Url)) || $GLOBALS['CORE']->HTTPReturn(404);
		file_put_contents(DIR_ROOT.'/templates/'.$filename, $buf);
		break;
	case 'zip':
		file_put_contents('temp.zip', file_get_contents($Url));
		$zip = new ZipArchive();
		$zip->open('temp.zip');
		for ($i=0; $extractToName = $zip->getNameIndex($i); $i++)
			$zip->extractTo(DIR_ROOT, $extractToName);
		$zip->close();
		unlink('temp.zip');
		break;
}



echo $filename;

exit;

?>