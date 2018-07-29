<?php
//начнем с обработчика
if ($_POST['doSend'])
	{ //если нажата кнопка отправить, то запускаем проверку и отправку
	switch($_POST['operator'])
		{
			case 'beeline': $suffix="@sms.beemail.ru";
			case 'motiv': $suffix="@sms.ycc.ru";
			break;
			//сюда потом можно добавить еще операторов
		}
	if (strlen($_POST['cellular'])<>10 || $suffix=="" || $_POST['body']=="") //проверяем введенные данные
		{
			?><b>Внимание!</b> Не заполнено из обязательных полей!<?
		}
	else
		{
			if (@$_POST['username']) 
				{
					$_POST['username']=" От: ".$_POST['username'];
				}
			else
				{
					$_POST['username']="";
				}
			//если введен отправитель, то в конец сообщения добавляем подпись
			$body=$_POST['body'].$_POST['username'];
			//собираем адрес получаетеля
			$address= "7".$_POST['cellular'].$suffix;
			//отпрваляем
			if (mail($address,"",$body,"From: \"sms\"\nContent-Type: text/html; charset=utf-8"))
				{
					//если сообщение отправлено, выводим
					?><b>Ваше sms успешно отправлено.</b><?
				}
			else
				{
					//иначе
					?><b>При отправке sms произошла ошибка.</b><?
				}	
		}
	}
else //если не нажата кнопка выводим форму
	{
		?>
		<form action="sms.php" method="POST">
		<select name="operator" onChange="subjectmenu(\’parent,this,0)">
		<option value="beeline">Билайн Россия</option>
		<option value="motiv">Мотив Россия</option>
		<!– сюда потом можно добавить еще операторов –>
		</select><br />
		Получатель*: +7 <input type="text" name="cellular" maxlength="10" size="10"><br />Ваше имя: <input name="username" type="text" maxlength="13" size="14"><br />Текст*: <textarea name="body" cols="40" rows="4"></textarea><br /><input type="submit" name="doSend" value="Отправить">
		</form>
		<?
	}
?>