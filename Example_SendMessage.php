<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objPenguin = new Penguin();
$objClient->addListener('jr', function($arrPacket){
	$intRoom = $arrPacket[3];
	echo 'Joined room! ', $intRoom, chr(10);
});

try {
	$objPenguin->login('Username', 'Password');
	$objPenguin->joinServer('Abominable');
} catch(Exceptions\ConnectionException $objException){
	die();
}

$objPenguin->joinRoom(100);

$arrMessages = array('Hello world', 'Party at my igloo', 'I like your hair', 'Welcome', 'Cool');

while(true){
	$objPenguin->sendMessage($arrMessages[array_rand($arrMessages)]);
	sleep(mt_rand(2, 3));
}

?>
