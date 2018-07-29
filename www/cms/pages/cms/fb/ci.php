<?php

if (!isset($_POST['src_width'])
	|| !isset($_POST['src_height'])
	|| !isset($_POST['cut_width'])
	|| !isset($_POST['cut_height'])
	|| !isset($_POST['cut_left'])
	|| !isset($_POST['cut_top'])
	|| !isset($_POST['iWidth'])
	|| !isset($_POST['iHeight'])) exit;

if (!file_exists(DIR_ROOT.$_POST['url'])) exit;

$pi = pathinfo($_POST['url']);
$pi['extension'] = isset($pi['extension']) ? strtolower($pi['extension']) : '';
	
switch ($pi['extension']) {
	case "jpg": $pi['extension'] = 'jpeg';
	case "jpeg": $img = imagecreatefromjpeg(DIR_ROOT.$_POST['url']); break;
	case "gif": $img = imagecreatefromgif(DIR_ROOT.$_POST['url']); break;
	case "png": $img = imagecreatefrompng(DIR_ROOT.$_POST['url']); break;
}

$w = imagesx($img);
$h = imagesy($img);


$left = ceil($_POST['cut_left'] * ($w / $_POST['src_width']));
$top = ceil($_POST['cut_top'] * ($h / $_POST['src_height']));

$w2 = ceil($_POST['cut_width'] * ($w / $_POST['src_width']));
$h2 = ceil($_POST['cut_height'] * ($h / $_POST['src_height']));

$w3 = max($w2, (int)$_POST['iWidth']);
$h3 = max($h2, (int)$_POST['iHeight']);

$img2 = imagecreatetruecolor($w3, $h3);

imagecopyresampled($img2, $img, 0, 0, $left, $top, $w3, $h3, $w2, $h2);
imagedestroy($img);

switch ($pi['extension']) {
	case "jpg": case "jpeg": imagejpeg($img2, DIR_ROOT.$_POST['url'], 100); break;
	case "gif": imagegif($img2, DIR_ROOT.$_POST['url']); break;
	case "png": imagepng($img2, DIR_ROOT.$_POST['url']); break;
}
imagedestroy($img2);

exit;

?>