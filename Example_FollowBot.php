<?php

spl_autoload_register(function($strClass){
	require_once sprintf('Penguin/%s.php', $strClass);
});

$mixTarget = 'Tails25'; // Change this to your target's player id or username

$objPenguin = new Penguin();
$objPenguin->addListener('sp', function($arrPacket) use($objPenguin, &$mixTarget){
	$intPlayer = $arrPacket[3];
	$intX = $arrPacket[4];
	$intY = $arrPacket[5];
	
	if(isset($objPenguin->arrRoom[$intPlayer])){
		$objPenguin->arrRoom[$intPlayer]->setX($intX);
		$objPenguin->arrRoom[$intPlayer]->setY($intY);
	}
	
	if($intPlayer == $mixTarget){
		$objPenguin->sendPosition($intX, $intY);
	}
});
$objPenguin->addListener('pbn', function($arrPacket) use(&$mixTarget){
	$mixTarget = $arrPacket[4];
});
$objPenguin->addListener('bf', function($arrPacket) use($objPenguin){
	$intRoom = $arrPacket[3];
	
	if($intRoom == -1){
		echo 'Target is offline', chr(10), die();
	}
	
	$objPenguin->joinRoom($intRoom);
});
$objPenguin->addListener('rp', function($arrPacket) use($objPenguin, &$mixTarget){
	$intPlayer = $arrPacket[3];
	unset($objPenguin->arrRoom[$intPlayer]);
	
	if($intPlayer == $mixTarget){
		$objPenguin->findBuddy($mixTarget);
	}
});

try {
	$objPenguin->login('Username', 'Password');
	$objPenguin->joinServer('Sleet');
} catch(ConnectionException $objException){
	die();
}

if(!is_numeric($mixTarget)){
	$objPenguin->getPlayerByName($mixTarget);
	
	while(!is_numeric($mixTarget)){
		$objPenguin->recv();
	}
}

$objPenguin->findBuddy($mixTarget);

while(true){
	$strData = $objPenguin->recv();
	
	if(XTParser::IsValid($strData)){
		echo $strData, chr(10);
	}
}

?>
