<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objClient = new Penguin();

$objClient->login('Arthy0', 'I\'m fuckin\' gay');

$objClient->joinServer('Blizzard');

$strResult = $objClient->recv();
echo $strResult, chr(10);

?>
