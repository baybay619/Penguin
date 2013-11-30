<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objClient = new Penguin();

$mixStatus = $objClient->login('Username', 'Password');
if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to login (', $intError, ' - ', $strError, ')', chr(10), die();
}

$objClient->joinServer('Blizzard');

while(true){
	$strResult = $objClient->recv();
	if($strResult !== null){
		echo $strResult, chr(10);
		return true;
	}
}

?>
