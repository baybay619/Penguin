<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objClient = new Penguin();

$objClient->login('Username', 'Password');

$objClient->joinServer('Blizzard');

$strResult = $objClient->recv();
echo $strResult, chr(10);

?>
