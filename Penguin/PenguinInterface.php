<?php

namespace Penguin;

interface PenguinInterface {
	
	public function login($strUsername, $strPassword);
	public function joinServer($strName);
	
	public function decodeExtensionPacket($strRawPacket);
	public function decodeVerticalData($strVerticalData);
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0);
	
	public function sendXt();
	
}

?>
