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

$arrData = $objPenguin->getPlayer(1); // Change 81 to any player id you want

list($intPlayer, $strUsername) = $arrData;

echo 'ID: ', $intPlayer, ', Username: ', $strUsername, chr(10);

$objPenguin->disconnect();

?>
