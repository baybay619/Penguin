<?php

interface PenguinInterface {
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0);
	public function sendMessage($strMessage);
	public function sendPosition($intX, $intY);
	
}

?>
