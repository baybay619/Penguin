<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objPenguin = new Penguin();
$mixStatus = $objPenguin->login('Username', 'Password');

if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to login (', $intError, ' - ', $strError, ')', chr(10), die();
}

$objPenguin->joinServer('Abominable');

$mixStatus = $objPenguin->joinRoom(100);

if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to join room (', $intError, ' - ', $strError, ')', chr(10);
}

echo 'Successfully joined room!', chr(10);

$arrMessages = array('Hello world', 'Party at my igloo', 'I like your hair', 'Welcome', 'Cool');

while(true){
	$objPenguin->sendMessage($arrMessages[array_rand($arrMessages)]);
	sleep(mt_rand(2, 3));
}

?>
