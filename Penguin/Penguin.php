<?php

class Penguin extends CPClient implements PenguinInterface {
	
	public function addItem($intItem){
		$this->sendXt('s', 'i#ai', $this->intInternalRoom, $intItem);
	}
	
	public function getPlayer($intPlayer){
		$this->sendXt('s', 'u#gp', $this->intInternalRoom, $intPlayer);
	}
	
	public function getPlayerByName($strUsername){
		$this->sendXt('s', 'u#pbn', -1, $strUsername);
	}
	
	public function findBuddy($intPlayer){
		$this->sendXt('s', 'u#bf', -1, $intPlayer);
	}
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0){		
		$this->sendXt('s', 'j#jr', -1, $intRoom, $intX, $intY);
	}
	
	public function sendMessage($strMessage){
		$this->sendXt('s', 'm#sm', $this->intInternalRoom, $this->intPlayerId, $strMessage);
	}
	
	public function sendPosition($intX, $intY){
		$this->sendXt('s', 'u#sp', $this->intInternalRoom, $intX, $intY);
	}
	
	public function updateColor($intColor){
		$this->sendXt('s', 's#upc', $this->intInternalRoom, $intColor);
	}
	
	public function updateHead($intItem){
		$this->sendXt('s', 's#uph', $this->intInternalRoom, $intItem);
	}
	
	public function updateFace($intItem){
		$this->sendXt('s', 's#upf', $this->intInternalRoom, $intItem);
	}
	
	public function updateNeck($intItem){
		$this->sendXt('s', 's#upn', $this->intInternalRoom, $intItem);
	}
	
	public function updateBody($intItem){
		$this->sendXt('s', 's#upb', $this->intInternalRoom, $intItem);
	}
	
	public function updateHand($intItem){
		$this->sendXt('s', 's#upa', $this->intInternalRoom, $intItem);
	}
	
	public function updateFeet($intItem){
		$this->sendXt('s', 's#upe', $this->intInternalRoom, $intItem);
	}
	
	public function updateFlag($intItem){
		$this->sendXt('s', 's#upl', $this->intInternalRoom, $intItem);
	}
	
	public function updatePhoto($intItem){
		$this->sendXt('s', 's#upp', $this->intInternalRoom, $intItem);
	}
	
}

?>
