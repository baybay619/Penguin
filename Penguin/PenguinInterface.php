<?php

interface PenguinInterface {
	
	public function getPlayer($intPlayer);
	public function getPlayerByName($strUsername);
	
	public function findBuddy($intPlayer);
	public function joinRoom($intRoom, $intX = 0, $intY = 0);
	
	public function sendMessage($strMessage);
	public function sendPosition($intX, $intY);
	
	public function updateColor($intColor);
	public function updateHead($intItem);
	public function updateFace($intItem);
	public function updateNeck($intItem);
	public function updateBody($intItem);
	public function updateHand($intItem);
	public function updateFeet($intItem);
	public function updateFlag($intItem);
	public function updatePhoto($intItem);
	
}

?>
