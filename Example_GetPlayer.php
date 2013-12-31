<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objPenguin = new Penguin();

try {
	$mixStatus = $objPenguin->login('Username', 'Password');
	$objPenguin->joinServer('Abominable');
} catch(Exceptions\ConnectionException $objException){
	die();
}

$arrData = $objPenguin->getPlayer(81); // Change 81 to any player id you want
list($intPlayer, $strUsername) = $arrData;
echo 'ID: ', $intPlayer, ', Username: ', $strUsername, chr(10);

$objPenguin->disconnect();

?>
