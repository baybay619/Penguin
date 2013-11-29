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
		echo $strPacket, chr(10);
		
		$this->send($strPacket);
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
		$intRandom = mt_rand(0, 3);
		if($intRandom == 0) return '204.75.167.218';
		if($intRandom == 1) return '204.75.167.219';
		if($intRandom == 2) return '204.75.167.176';
		if($intRandom == 3) return '204.75.167.177';
	}
	
	private function generateLoginPort(){
		$intASCII = ord($this->strUsername);
		return $intASCII ? 6112 : 3724;
	}
	
	public function decodeExtensionPacket($strRawPacket){
		$arrXt = explode('%', $strRawPacket);
		
		array_shift($arrXt);
		array_pop($arrXt);
		
		return $arrXt;
	}
	
	public function decodeVerticalData($strVerticalData){
		$arrVertical = explode('|', $strVerticalData);
		
		return $arrVertical;
	}
	
	public function login($strUsername, $strPassword){
		$this->strUsername = $strUsername;
		$this->strPassword = $strPassword;
		
		$strAddress = $this->generateLoginAddress();
		$intPort = $this->generateLoginPort();
		
		$strData = $this->sendHandshake($strAddress, $intPort);
		
		$objXml = simplexml_load_string($strData);
		$strKey = $this->generateKey($strPassword, $objXml->body->k);
		
		$this->send('<msg t="sys"><body action="login" r="0"><login z="w1"><nick><![CDATA[' . $this->strUsername . ']]></nick><pword><![CDATA[' . $strKey . ']]></pword></login></body></msg>');
		
		$strResult = $this->recv();
		
		$blnResult = $this->handleLogin($strResult);
		
		$this->disconnect();
		
		return $blnResult;
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
		
		$this->recv();
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
