<?php

namespace Penguin;

trait Interactions {
	
	public function addItem($intItem){
		$this->sendXt('s', 'i#ai', $this->intInternalRoom, $intItem);
	}
	
	public function getPlayer($intPlayer){
		$arrData = array('s', 'u#gp', $this->intInternalRoom, $intPlayer);
		
		$arrData = $this->sendXtAndWait($arrData, 'gp');
		
		$arrPlayer = $this->decodeVerticalData($arrData[3]);
		
		return $arrPlayer;
	}
	
	public function joinRoom($intRoom, $intX = 0, $intY = 0){
		$intInternal = function($intRoom){
			return array_key_exists($intRoom, $this->arrRooms) ? $this->arrRooms[$intRoom]['Internal'] : -1;
		};
		
		$this->sendXt('s', 'j#jr', -1, $intRoom, $intX, $intY);
		
		$strData = $this->recv();
		$arrData = explode(chr(0), $strData);
		array_pop($arrData);
		
		foreach($arrData as $strData){
			$arrPacket = $this->decodeExtensionPacket($strData);
			
			if(!isset($arrPacket[1])){
				continue;
			}
			
			if($arrPacket[1] == 'jr'){
				$this->intExternalRoom = $intRoom;
				$this->intInternalRoom = $intInternal($intRoom);
				
				return true;
			} elseif($arrPacket[1] == 'e'){
				$intError = $arrPacket[3];
				
				if($intError != 200){
					$strError = $this->arrErrors[$intError]['Description'];
					
					return array($intError, $strError);
				} else {
					$this->intExternalRoom = $intRoom;
					$this->intInternalRoom = $intInternal($intRoom);
					
					return true;
				}
			}
		}
		
		$arrPacket = array(null, null);
		
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
							$this->intInternalRoom = $intInternal($intRoom);
								
							return true;
						} elseif($arrPacket[1] == 'e'){
							$intError = $arrPacket[3];
							
							if($intError != 200){
								$strError = $this->arrErrors[$intError]['Description'];
								
								return array($intError, $strError);
							} else {
								$this->intExternalRoom = $intRoom;
								$this->intInternalRoom = $intInternal($intRoom);
								
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
