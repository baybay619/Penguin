<?php

namespace Penguin;
use Penguin\Exceptions;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objClient = new Penguin();
$objClient->addListener('jr', function($arrPacket){
	$intRoom = $arrPacket[3];
	echo 'Joined room! ', $intRoom, chr(10);
});

try {
	$objClient->login('Username', 'Password');
	$objClient->joinServer('Cozy');
} catch(Exceptions\ConnectionException $objException){
	die();
}

$objClient->joinRoom(100);

while(true){
	$strResult = $objClient->recv();
	echo $strResult, chr(10);
}

?>
