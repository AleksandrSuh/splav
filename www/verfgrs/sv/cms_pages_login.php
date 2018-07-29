<?php

if (isset($_POST['login'])) {
	if (strpos($_POST['login'], '"')) exit;
	if (!isset($_COOKIE[session_name()])) session_start();
	$_SESSION['login_pass'] = true;
	$_SERVER['PHP_AUTH_USER'] = $_POST['login'];
	$_SERVER['PHP_AUTH_PW'] = $_POST['password'];
}
if (isset($_SESSION['login_pass']) && $_SESSION['login_pass'] && isset($_SERVER['PHP_AUTH_USER'])) {
	$_SESSION['login_pass'] = false;
	$_SESSION['userdata'] = NULL;
	$_SESSION['user'] = $_SERVER['PHP_AUTH_USER'];
	if (strpos($_SESSION['user'], '"')) exit;
	$_SESSION['password'] = md5($_SERVER['PHP_AUTH_PW']);
	if (isset($_POST['redirect'])) header('location: '.$_POST['redirect']);
	else if (isset($_POST['login'])) header('location: '.$_SERVER['HTTP_REFERER']);
	else header('location: /');
	exit;
} else if (function_exists('apache_request_headers')) {
	if (!isset($_COOKIE[session_name()])) session_start();
	$_SESSION['login_pass'] = true;
	header('WWW-Authenticate: Basic realm=""');
	header('HTTP/1.1 401 Unauthorized');
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Авторизация</title>
<link type="text/css" rel="stylesheet" href="/cms/css/cms.css" />
</head>
<body>
<form method="post" action="/login"><input type="hidden" name="redirect" value="/" />
<table cellpadding="0" cellspacing="3" width="100%">
<tr><th colspan="2">Авторизация</th></tr>
<tr><td>Логин</td><td><input type="text" name="login" /></td></tr>
<tr><td>Пароль</td><td><input type="password" name="password" /></td></tr>
<tr><td colspan="2"><input type="submit" value="Вход" /></td></tr>
</table>
</form>
</body>
</html>
<?php

exit;

?>