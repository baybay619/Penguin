<?php

namespace Penguin;

spl_autoload_register(function($strClass){
	include str_replace('\\', '/', $strClass) . '.php';
});

$objPenguin = new Penguin();
$objPenguin->login('Username', 'Password');
$objPenguin->joinServer('Abominable');

$objPenguin->joinRoom(100);

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
}

?>
