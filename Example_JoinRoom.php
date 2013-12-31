<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objPenguin = new Penguin();
$objPenguin->addListener('jr', function($arrPacket){
	$intRoom = $arrPacket[3];
	echo 'Joined room! ', $intRoom, chr(10);
});

try {
	$objPenguin->login('Username', 'Password');
	$objPenguin->joinServer('Cozy');
} catch(Exceptions\ConnectionException $objException){
	die();
}

$mixStatus = $objPenguin->joinRoom(100);

if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to join room (', $intError, ' - ', $strError, ')', chr(10);
}

while(true){
	$strData = $objPenguin->recv();
	echo $strData, chr(10);
	
	$objPenguin->sendPosition(mt_rand(100, 300), mt_rand(100, 300));
	usleep(1000);
}

?>
