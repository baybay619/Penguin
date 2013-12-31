<?php

namespace Penguin;
use Petrel;

class Penguin extends Petrel\ClientBase implements PenguinInterface {
	
	public $arrErrors;
	public $arrRooms;
	public $arrServers;
	
	public $arrPlayer;
	
	public $intPlayerId;
	public $strUsername;
	
	public $intExternalRoom, $intInternalRoom;
	
	private $arrListeners;
	
	private $strPassword;
	
	private $strLoginKey;
	private $strConfirmationKey;
	
	private $strRawPlayer;
	
	use Cryptography;
	use Interactions;
	
	public function __construct(){
		$this->arrErrors = parse_ini_file('INI/Errors.ini', true);
		$this->arrRooms = parse_ini_file('INI/Rooms.ini', true);
		$this->arrServers = parse_ini_file('INI/Servers.ini', true);
		
		$this->addListener('lp', function($arrPacket){
			// Insert usage of lp
		});
	}
	
	public function addListener($strHandler, Callable $mixCallback){
		$this->arrListeners[$strHandler] = $mixCallback;
	}
	
	public function removeListener($strHandler){
		unset($this->arrListeners[$strHandler]);
	}
	
	public function recv(){
		$arrSockets = array($this->resSocket);
		$intChanged = socket_select($arrSockets, $arrWrite, $arrExcept, 30);
		if($intChanged == 0){
			echo 'Haven\'t received any data within the past 30 seconds', chr(10);
			$strData = $this->recv();
			return $strData;
		}
		
		$mixReceived = @socket_recv($this->resSocket, $strData, 8192, 0);
		
		if($mixReceived === false && $mixReceived != 0){
			socket_shutdown($this->resSocket);
			echo 'Connection failure.', chr(10), die();
		} elseif($mixReceived === 0){
			socket_shutdown($this->resSocket);
			echo 'Connection failure.', chr(10), die();
		}
		
		$blnExtension = strpos($strData, 'xt') !== false;
		if($blnExtension && !empty($this->arrListeners)){
			$arrData = explode(chr(0), $strData);
			array_pop($arrData);
			
			foreach($arrData as $strPacket){
				$arrPacket = $this->decodeExtensionPacket($strPacket);
				
				if(!empty($arrPacket)){
					$strHandler = $arrPacket[1];
					
					if(array_key_exists($strHandler, $this->arrListeners)){
						$mixCallable = $this->arrListeners[$strHandler];
						
						call_user_func($mixCallable, $arrPacket);
					}
				}
			}
		}

		return $strData;
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
		
		return array($strAddress, $intPort);
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
		
		$strData = $this->sendHandshake('204.75.167.177', 3724);
		
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
		
		socket_set_nonblock($this->resSocket);
	}
	
	private function handleLogin($strResult){
		$arrPacket = $this->decodeExtensionPacket($strResult);

		if($arrPacket[1] == 'e'){
			$intError = $arrPacket[3];
			$strError = $this->arrErrors[$intError]['Description'];
			throw new Exceptions\ConnectionException($strError, $intError);
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
		$blnSuccess = $this->connect($strAddress, $intPort);
		
		if($blnSuccess === false){
			throw new Exceptions\ConnectionException('Unable to establish connection to a game server', -1);
		}
		
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
