<?php

$t = microtime(true);

if (!isset($_GET['url'])) $_GET['url'] = '/cms/img/1x1transparent.gif';
$max_width = isset($_GET['w']) ? $_GET['w'] : '*';
$max_height = isset($_GET['h']) ? $_GET['h'] : '*';
$mark_index = isset($_GET['m']) && $_GET['m']!='' ? $_GET['m'] : '0';
if (IMG_WATERMARK!='') {
	$marks = explode(';', IMG_WATERMARK);
	$mark_def = trim(isset($marks[$mark_index]) ? $marks[$mark_index] : $marks[0]);//var_dump($mark_def);
	if (preg_match('/^(\S+)/', $mark_def, $matches)) {
		$mark = $matches[1];
		if (preg_match('/( min\:(\S+)x(\S+))/', $mark_def, $matches)) {
			$mark_min_w = $matches[2];
			$mark_min_h = $matches[3];
		} else {
			$mark_min_w = 200;
			$mark_min_h = 200;
		}
		if (preg_match('/( pos\:(\S+)x(\S+))/', $mark_def, $matches)) {
			$mark_x = $matches[2];
			$mark_y = $matches[3];
		} else {
			$mark_x = 0.1;
			$mark_y = 0.8;
		}
		if (preg_match('/( size\:(\S+)x(\S+))/', $mark_def, $matches)) {
			$mark_w = $matches[2];
			$mark_h = $matches[3];
		} else {
			$mark_w = '*';
			$mark_h = 0.1;
		}
	} else $mark = NULL;
} else $mark = NULL;

$pi = pathinfo($_GET['url']);
$cacheRootDirname = $pi['dirname'].'/_cache';
$cacheDirname = $cacheRootDirname.'/'.($max_width=='*' ? '' : $max_width).'x'.($max_height=='*' ? '' : $max_height);
$cacheFilename = $cacheDirname.'/'.$pi['basename'];
if (!file_exists(DIR_ROOT.$cacheFilename)) {
	$pi['extension'] = isset($pi['extension']) ? strtolower($pi['extension']) : '';
	
	switch ($pi['extension']) {
		case "jpg": $pi['extension'] = 'jpeg';
		case "jpeg": $img = imagecreatefromjpeg(DIR_ROOT.$_GET['url']); break;
		case "gif": $img = imagecreatefromgif(DIR_ROOT.$_GET['url']); break;
		case "png": $img = imagecreatefrompng(DIR_ROOT.$_GET['url']); break;
	}
	
	$w = imagesx($img);
	$h = imagesy($img);
	$w2 = $w;
	$h2 = $h;
	
	if ($max_width!='*' && $max_height!='*') {
		$max_width = (int)$max_width;
		$max_height = (int)$max_height;
		if ($w2>$max_width) {$w2 = $max_width; $h2 = ceil($h/$w*$w2);}
		if ($h2>$max_height) {$h2 = $max_height; $w2 = ceil($w/$h*$h2);}
	} else if ($max_width!='*') {
		$max_width = (int)$max_width;
		if ($w2>$max_width) {$w2 = $max_width; $h2 = ceil($h/$w*$w2);}
	} else if ($max_height!='*') {
		$max_height = (int)$max_height;
		if ($h2>$max_height) {$h2 = $max_height; $w2 = ceil($w/$h*$h2);}
	}
	
	$img2 = imagecreatetruecolor($w2, $h2);
	imagecopyresampled($img2, $img, 0, 0, 0, 0, $w2, $h2, $w, $h);
	imagedestroy($img);

	if (!is_null($mark) && $w2>=$mark_min_w && $h2>=$mark_min_h) {
		$img3 = imagecreatefrompng(DIR_ROOT.$mark);
		$w3 = imagesx($img3);
		$h3 = imagesy($img3);
		if ($mark_w!='*' && $mark_h!='*') {
			$w4 = $w2*$mark_w;
			$h4 = $h2*$mark_h;
		} else if ($mark_w!='*') {
			$w4 = $w2*$mark_w;
			$h4 = ceil($h3/$w3*$w4);
		} else if ($mark_h!='*') {
			$h4 = $h2*$mark_h;
			$w4 = ceil($w3/$h3*$h4);
		} else {
			$w4 = $w3;
			$h4 = $h3;
		}
		imagecopyresized($img2, $img3, $w2*$mark_x, $h2*$mark_y, 0, 0, $w4, $h4, $w3, $h3);
		imagedestroy($img3);
	}
	
	if (!file_exists(DIR_ROOT.$cacheRootDirname)) mkdir(DIR_ROOT.$cacheRootDirname);
	if (!file_exists(DIR_ROOT.$cacheDirname)) mkdir(DIR_ROOT.$cacheDirname);
	switch ($pi['extension']) {
		case "jpg": case "jpeg": imagejpeg($img2, DIR_ROOT.$cacheFilename, JPEG_QUALITY); break;
		case "gif": imagegif($img2, DIR_ROOT.$cacheFilename); break;
		case "png": imagepng($img2, DIR_ROOT.$cacheFilename); break;
	}
	imagedestroy($img2);
}
//header('Content-Type: image/jpeg');

//file_put_contents('temp.txt', file_get_contents('temp.txt').sprintf("\n%s\n%s\n", microtime(true) - $t, memory_get_peak_usage()));

header('location: '.$cacheFilename);
exit;

?>