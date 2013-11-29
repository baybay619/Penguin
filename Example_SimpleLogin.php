<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objClient = new Penguin();

$mixStatus = $objClient->login('Tails25', 'tails25');
if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to login (', $intError, ' - ', $strError, ')', chr(10), die();
}

$objClient->joinServer('Blizzard');

$strResult = $objClient->recv();
echo $strResult, chr(10);

?>
