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

$objPenguin->joinServer('Cozy');

$mixStatus = $objPenguin->joinRoom(100);

if($mixStatus !== true){
	list($intError, $strError) = $mixStatus;
	echo 'Unable to join room (', $intError, ' - ', $strError, ')', chr(10);
}

echo 'Successfully joined room!', chr(10);

while(true){
	$strData = $objPenguin->recv();
	if($strData != null){
		if(substr_count($strData, chr(0)) > 1){
			$arrData = explode(chr(0), $strData);
			array_pop($arrData);
			
			foreach($arrData as $strPacket){
				if($strPacket != ''){
					echo $strPacket, chr(10);
				}
			}
		} else {
			$arrData = explode(chr(0), $strData);
			list($strPacket) = $arrData;
			
			echo $strPacket, chr(10);
		}
	}
	$objPenguin->sendPosition(mt_rand(100, 300), mt_rand(100, 300));
}

?>
