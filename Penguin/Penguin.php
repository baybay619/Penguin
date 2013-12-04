<?php

namespace Penguin;
use Petrel;

class Penguin extends Petrel\ClientBase implements PenguinInterface {
	
	public $arrErrors;
	public $arrRooms;
	public $arrServers;
	
	public $intPlayerId;
	public $strUsername;
	
	public $intExternalRoom, $intInternalRoom;
	
	private $strPassword;
	
	private $strLoginKey;
	private $strConfirmationKey;
	
	private $strRawPlayer;
	
	use Interactions;
	
	public function __construct(){
		$this->arrErrors = parse_ini_file('INI/Errors.ini', true);
		$this->arrRooms = parse_ini_file('INI/Rooms.ini', true);
		$this->arrServers = parse_ini_file('INI/Servers.ini', true);
	}
	
	public function sendXt(){
		$arrData = func_get_args();
		$strPacket = '%xt%';
		
		$strPacket .= implode('%', $arrData) . '%';
		
		$this->send($strPacket);
	}
	
	// Because blocking breaks things ?
	public function sendXtAndWait($arrData, $strHandler){
		$strPacket = '%xt%';
		
		$strPacket .= implode('%', $arrData) . '%';
		
		$this->send($strPacket);
		
		// And now we wait..
		
		$strData = $this->recv();
		$arrData = explode(chr(0), $strData);
		array_pop($arrData);
		
		foreach($arrData as $strPacket){
			$arrPacket = $this->decodeExtensionPacket($strPacket);
			
			if($arrPacket[1] == $strHandler){
				return $arrPacket;
			} elseif($arrPacket[1] == 'e'){
				$intError = $arrPacket[3];
				$strError = $this->arrErrors[$intError]['Description'];
				
				return array($intError, $strError);
			}
		}
		
		while($arrPacket[1] != $strHandler){
			$strData = $this->recv();
			if($strData != null){
				$arrData = explode(chr(0), $strData);
				array_pop($arrData);
				
				foreach($arrData as $strPacket){
					if($strPacket != ''){
						$arrPacket = $this->decodeExtensionPacket($strPacket);
						
						if($arrPacket[1] == $strHandler){
							return $arrPacket;
						} elseif($arrPacket[1] == 'e'){
							$intError = $arrPacket[3];
							$strError = $this->arrErrors[$intError]['Description'];
							
							return array($intError, $strError);
						}
					}
				}
			}
		}
	} // End sendXtAndWait		
	
	public function waitForHandler($strHandler){
		do {
			$strData = $this->recv();
			
			if($strData != null){
				$arrData = explode(chr(0), $strData);
				array_pop($arrData);
				
				foreach($arrData as $strData){
					$arrPacket = $this->decodeExtensionPacket($strData);
					
					if($arrPacket[1] == $strHandler){
						return $arrPacket;
					}
				}
			}
		} while($arrPacket[1] != $strHandler);
	}
	
	private function encryptPassword($strPassword){
		$strMd5Hash = md5($strPassword);
		$strSwappedMd5Hash = substr($strMd5Hash, 16, 16) . substr($strMd5Hash, 0, 16);
		
		return $strSwappedMd5Hash;
	}
	
	private function generateKey($strPassword, $strRandKey){
		$strKey = strtoupper($this->encryptPassword($strPassword)) . $strRandKey . 'a1ebe00441f5aecb185d0ec178ca2305Y(02.>\'H}t":E1_root';
		$strHash = $this->encryptPassword($strKey);
		
		return $strHash;
	}
	
	private function generateLoginAddress(){
		$arrAddresses = array(
			'204.75.167.218',
			'204.75.167.219',
			'204.75.167.176',
			'204.75.167.177'
		);
		
		$intRandom = array_rand($arrAddresses);
		$strAddress = $arrAddresses[$intRandom];
		
		$intASCII = ord($this->strUsername);
		$intPort = $intASCII ? 6112 : 3724;
		
		if($strAddress == '204.75.167.176' && $intPort == 3724){
			return $this->generateLoginAddress();
		} else {
			return array($strAddress, $intPort);
		}
	}
	
	public function decodeExtensionPacket($strRawPacket){
		$arrXt = explode('%', $strRawPacket);
		
		array_shift($arrXt);
		array_pop($arrXt);
		
		return $arrXt;
	}
	
	public function decodeVerticalData($strVerticalData){
		$arrVertical = explode('|', $strVerticalData);
		array_pop($arrVertical); // Hopefully this doesn't break anything 
		
		return $arrVertical;
	}
	
	public function login($strUsername, $strPassword){
		$this->strUsername = $strUsername;
		$this->strPassword = $strPassword;
		
		$arrServer = $this->generateLoginAddress();
		list($strAddress, $intPort) = $arrServer;
		
		$strData = $this->sendHandshake($strAddress, $intPort);
		
		$objXml = simplexml_load_string($strData);
		$strKey = $this->generateKey($strPassword, $objXml->body->k);
		
		$this->send('<msg t="sys"><body action="login" r="0"><login z="w1"><nick><![CDATA[' . $this->strUsername . ']]></nick><pword><![CDATA[' . $strKey . ']]></pword></login></body></msg>');
		
		$strResult = $this->recv();
		
		$mixResult = $this->handleLogin($strResult);
		
		$this->disconnect();
		
		return $mixResult;
	}
	
	public function joinServer($strName){
		$strAddress = $this->arrServers[$strName]['IP'];
		$intPort = $this->arrServers[$strName]['Port'];
		
		$strResult = $this->sendHandshake($strAddress, $intPort);
		
		$objXml = simplexml_load_string($strResult);
		$strKey = $this->encryptPassword($this->strLoginKey . $objXml->body->k) . $this->strLoginKey;
		
		$this->send('<msg t="sys"><body action="login" r="0"><login z="w1"><nick><![CDATA[' . $this->strRawPlayer . ']]></nick><pword><![CDATA[' . $strKey . '#' . $this->strConfirmationKey . ']]></pword></login></body></msg>');
		
		$this->send('%xt%s%j#js%-1%' . $this->intPlayerId . '%' . $this->strLoginKey . '%en%');
		
		$this->send('%xt%s%g#gi%-1%');
		
		$arrPacket = $this->waitForHandler('lp');
	}
	
	private function handleLogin($strResult){
		$arrPacket = $this->decodeExtensionPacket($strResult);

		if($arrPacket[1] == 'e'){
			$intError = $arrPacket[3];
			$strError = $this->arrErrors[$intError]['Description'];
			return array($intError, $strError);
		}
		
		$strVertical = $arrPacket[3];
		$arrVertical = $this->decodeVerticalData($strVertical);
		
		$this->intPlayerId = $arrVertical[0];
		$this->strLoginKey = $arrVertical[3];
		$this->strConfirmationKey = $arrPacket[4];
		
		$this->strRawPlayer = $strVertical;
		
		return true;
	}
	
	private function sendHandshake($strAddress, $intPort, $intApiVersion = 153){
		$this->connect($strAddress, $intPort);
		
		$this->send('<msg t="sys"><body action="verChk" r="0"><ver v="' . $intApiVersion . '" /></body></msg>');
		$this->send('<msg t="sys"><body action="rndK" r="-1"></body></msg>');
		
		$strResult = $this->recv();
		
		while(!strpos($strResult, '</k>')) {
			$strResult = $this->recv();
		}
		
		return $strResult;
	}
	
}

?>
