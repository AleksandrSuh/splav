<?php

$Referer = urlencode($_SERVER['REQUEST_URI']);
$Category = isset($_GET['c']) && $_GET['c']!='' ? $_GET['c'] : '*';
$Search = isset($_GET['s']) ? $_GET['s'] : '';
$First = isset($_GET['f']) ? $_GET['f'] : 0;
$Length = isset($_GET['l']) ? $_GET['l'] : 10;

echo file_get_contents('http://gallery.artgk-cms.ru/?r='
	.$Referer.'&c='.$Category.'&s='.$Search.'&f='.$First.'&l='.$Length);

exit;

?>