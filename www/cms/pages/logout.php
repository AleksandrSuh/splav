<?php

if (isset($_GET['redirect'])) $_SERVER['HTTP_REFERER'] = $_GET['redirect'];

session_destroy();
if (preg_match('/\/cms/', $_SERVER['HTTP_REFERER'])) header('location: /');
else header('location: '.$_SERVER['HTTP_REFERER']);
exit;

?>
