<?php

$to = $_POST['to'];
$from = $_POST['from'];
$subject = $_POST['subject'];
$message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'.$_POST['message'].'</body></html>';
$headers = "From: $from\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($to, $subject, $message, $headers);

$URL = isset($_POST["redirect"]) && $_POST["redirect"]!='' ? $_POST["redirect"] : $_SERVER['HTTP_REFERER'];
$DecodedURL = urldecode($URL);
header("location: $DecodedURL");
exit;

?>