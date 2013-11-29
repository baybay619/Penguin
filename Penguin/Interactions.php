<?php

namespace Penguin;

trait Interactions {
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0){
		$this->sendXt('s', 'j#jr', -1, $intRoom, $intX, $intY);
		
		$strData = $this->recv();
		$arrData = explode(chr(0), $strData);
		array_pop($arrData);
		
		foreach($arrData as $strData){
			$arrPacket = $this->decodeExtensionPacket($strData);
			
			if($arrPacket[1] == 'jr'){
				$this->intExternalRoom = $intRoom;
				$this->intInternalRoom = $this->arrRooms[$intRoom]['Internal'];
				
				return true;
			} elseif($arrPacket[1] == 'e'){
				$intError = $arrPacket[3];
				
				if($intError != 200){
					$strError = $this->arrErrors[$intError]['Description'];
					
					return array($intError, $strError);
				} else {
					$this->intExternalRoom = $intRoom;
					$this->intInternalRoom = $this->arrRooms[$intRoom]['Internal'];
					
					return true;
				}
			}
		}
		
		while($arrPacket[1] != 'jr'){
			$strData = $this->recv();
			if($strData != null){
				$arrData = explode(chr(0), $strData);
				array_pop($arrData);
				
				foreach($arrData as $strPacket){
					if($strPacket != ''){
						$arrPacket = $this->decodeExtensionPacket($strPacket);

						if($arrPacket[1] == 'ap'){
							$this->intExternalRoom = $intRoom;
							$this->intInternalRoom = $this->arrRooms[$intRoom]['Internal'];
								
							return true;
						} elseif($arrPacket[1] == 'e'){
							$intError = $arrPacket[3];
							
							if($intError != 200){
								$strError = $this->arrErrors[$intError]['Description'];
								
								return array($intError, $strError);
							} else {
								$this->intExternalRoom = $intRoom;
								$this->intInternalRoom = $this->arrRooms[$intRoom]['Internal'];
								
								return true;
							}
						}
					}
				}
			}
		}
	} // End joinRoom
	
	public function sendMessage($strMessage){
		$this->sendXt('s', 'm#sm', $this->intInternalRoom, $this->intPlayerId, $strMessage);
	}
	
	public function sendPosition($intX, $intY){
		$this->sendXt('s', 'u#sp', $this->intInternalRoom, $intX, $intY);
	}
	
}

?>
