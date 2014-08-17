<?php

class CPClient extends ClientBase implements CPClientInterface {
	
	public $arrErrors;
	public $arrRooms;
	public $arrServers;
	
	public $arrPlayer;
	
	public $intPlayerId;
	public $strUsername;
	public $objPlayer;
	
	public $arrRoom;
	
	public $intExternalRoom, $intInternalRoom;
	
	protected $arrListeners;
	
	private $strPassword;
	
	private $strLoginKey;
	private $strConfirmationKey;
	
	private $strRawPlayer;
	
	public function __construct(){
		$this->arrErrors = parse_ini_file('INI/Errors.ini', true);
		$this->arrRooms = parse_ini_file('INI/Rooms.ini', true);
		$this->arrServers = parse_ini_file('INI/Servers.ini', true);
		
		$this->addListener('e', function($arrPacket){
			$intError = $arrPacket[2];
			
			echo 'Received error: ', $intError, '!', chr(10);
		});
		
		$this->addListener('lp', function($arrPacket){
			$arrPlayer = XTParser::ParseVertical($arrPacket[3]);
			
			$this->objPlayer = new Player($arrPlayer);
		});
		
		$this->addListener('jr', function($arrPacket){
			$intInternal = $arrPacket[2];
			$intRoom = $arrPacket[3];
			
			$this->intInternalRoom = $intInternal;
			$this->intExternalRoom = $intRoom;
			
			$this->arrRoom = array();
			
			array_shift($arrPacket);
			array_shift($arrPacket);
			array_shift($arrPacket);
			
			foreach($arrPacket as $strPenguin){
				$arrPlayer = XTParser::ParseVertical($strPenguin);
				if(!empty($arrPlayer)){
					if($arrPlayer[0] != $this->intPlayerId){
						$objPlayer = new Player($arrPlayer);
						$this->arrRoom[$objPlayer->getPlayerId()] = $objPlayer;
					}
				}
			}
		});
		
		$this->addListener('ap', function($arrPacket){
			$arrPlayer = XTParser::ParseVertical($arrPacket[3]);
			
			if($arrPlayer[0] != $this->intPlayerId){
				$objPlayer = new Player($arrPlayer);
				$this->arrRoom[$objPlayer->getPlayerId()] = $objPlayer;
			}
		});
		
		$this->addListener('rp', function($arrPacket){
			$intPlayer = $arrPacket[3];
			
			unset($this->arrRoom[$intPlayer]);
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
		
		if($mixReceived === 0){
			socket_shutdown($this->resSocket);
			echo 'Disconnected.', chr(10), die();
		}
		
		while(strpos($strData, chr(0)) === false){
			@socket_recv($this->resSocket, $strMissing, 8192, 0);
			$strData .= $strMissing;
		}
		
		if(XTParser::IsValid($strData) && !empty($this->arrListeners)){
			$arrData = explode(chr(0), $strData);
			array_pop($arrData);
			
			foreach($arrData as $strPacket){
				if(XTParser::IsValid($strPacket)){
					$arrPacket = XTParser::ParseRaw($strPacket);
					
					if(!empty($arrPacket)){
						$strHandler = $arrPacket[1];
						
						if(array_key_exists($strHandler, $this->arrListeners)){
							$mixCallable = $this->arrListeners[$strHandler];
							
							call_user_func($mixCallable, $arrPacket);
						}
					}
					$strData = $strPacket;
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
	
	public function login($strUsername, $strPassword){
		$this->strUsername = $strUsername;
		$this->strPassword = $strPassword;
		
		$strData = $this->sendHandshake('204.75.167.165', 3724);
		
		$objXml = simplexml_load_string($strData);
		$strKey = Crypto::generateKey($strPassword, $objXml->body->k);
		
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
		$strKey = Crypto::encryptPassword($this->strLoginKey . $objXml->body->k) . $this->strLoginKey;
		
		$this->send('<msg t="sys"><body action="login" r="0"><login z="w1"><nick><![CDATA[' . $this->strRawPlayer . ']]></nick><pword><![CDATA[' . $strKey . '#' . $this->strConfirmationKey . ']]></pword></login></body></msg>');
		$this->send('%xt%s%j#js%-1%' . $this->intPlayerId . '%' . $this->strLoginKey . '%en%');
		$this->send('%xt%s%g#gi%-1%');
		
		socket_set_nonblock($this->resSocket);
	}
	
	private function handleLogin($strResult){
		$arrPacket = XTParser::ParseRaw($strResult);

		if($arrPacket[1] == 'e'){
			$intError = $arrPacket[3];
			$strError = $this->arrErrors[$intError]['Description'];
			throw new ConnectionException($strError, $intError);
		}
		
		$strVertical = $arrPacket[3];
		$arrVertical = XTParser::ParseVertical($strVertical);
		
		$this->intPlayerId = $arrVertical[0];
		$this->strLoginKey = $arrVertical[3];
		$this->strConfirmationKey = $arrPacket[4];
		
		$this->strRawPlayer = $strVertical;
		
		return true;
	}
	
	private function sendHandshake($strAddress, $intPort, $intApiVersion = 153){
		$blnSuccess = $this->connect($strAddress, $intPort);
		
		if($blnSuccess === false){
			throw new ConnectionException('Unable to establish connection to a game server', -1);
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
