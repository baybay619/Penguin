<?php

namespace Penguin;

interface PenguinInterface {
	
	public function login($strUsername, $strPassword);
	public function joinServer($strName);
	
	public function addListener($strHandler, Callable $mixCallback);
	public function removeListener($strHandler);
	
	public function decodeExtensionPacket($strRawPacket);
	public function decodeVerticalData($strVerticalData);
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0);
	public function sendMessage($strMessage);
	public function sendPosition($intX, $intY);
	
	public function sendXt();
	public function sendXtAndWait($arrData, $strHandler);
	
}

?>
