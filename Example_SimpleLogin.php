<?php

spl_autoload_register(function($strClass){
	require_once sprintf('Penguin/%s.php', $strClass);
});

$objPenguin = new Penguin();

try {
	$objPenguin->login('Username', 'Password');
	$objPenguin->joinServer('Sled');
} catch(ConnectionException $objException){
	die();
}

$objPenguin->joinRoom(100);

while(true){
	$strData = $objPenguin->recv();
	
	if(XTParser::IsValid($strData)){
		echo $strData, chr(10);
	}
}

?>
