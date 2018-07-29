<?php

if (!isset($_GET['name'])) $GLOBALS['CORE']->HTTPReturn(404);
if (isset($_GET['redirect'])) $_SERVER['HTTP_REFERER'] = $_GET['redirect'];

$newPassword = GeneratePassword();

list($r, $f, $l, $c) = $GLOBALS['DBC']->rawQuery('SELECT * FROM `'.TABLE_USER.'` WHERE `name`="'.$_GET['name'].'"',
	'/db/group/user[@name="'.$_GET['name'].'"]', '');
if ($c>0) {
	$row = $GLOBALS['DBC']->getRow($r, 0, $f);
	
	$a = array('action'=>'userSave', 'id'=>$row['id'], 'password'=>$newPassword, 'vars'=>array());
	$action = new CAction($a);
	$action->init();
	if ($action->check()) $action->exec();
	
	MailTo($row['email'], 'forget@'.$_SERVER['HTTP_HOST'], 'Восстановление пароля на '.$_SERVER['HTTP_HOST'],
		'Логин: '.$row['name'].'<br />Новый пароль: '.$newPassword);
	$result = 0;
} else $result = 1;

if (isset($_SERVER['HTTP_REFERER'])) {
	header('location: '.parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH).'?result='.$result);
	exit;
}

exit;

?>