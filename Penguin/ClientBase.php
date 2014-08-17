<?php

abstract class ClientBase {
	
	public $resSocket;
	
	public $proxyObject = null;
	public $usingProxy = false;
	public $proxySettings = array("address" => "", "port" => 0);
	
	public function connect($strAddress, $intPort){
		if(!$this->usingProxy) {
			$this->resSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			
			$blnSuccess = socket_connect($this->resSocket, $strAddress, $intPort);
		} else {
			$this->proxyObject = new phsock($strAddress, $intPort, $this->proxySettings["address"], $this->proxySettings["port"]);
			$this->resSocket = &$this->proxyObject->socket;
			
			$blnSuccess = $this->proxyObject->ready;
		}
		
		return $blnSuccess;
	}
	
	public function disconnect(){
		if(!$this->usingProxy) {
			socket_shutdown($this->resSocket);
		} else {
			unset($this->proxyObject);
		}
	}
	
	public function recv(){
		$intReceived = socket_recv($this->resSocket, $strData, 8192, 0);

		return $strData;
	}
	
	public function send($strData){
		$strData .= chr(0);
		$intData = strlen($strData);
		
		if(!$this->usingProxy) {
			$intSent = socket_send($this->resSocket, $strData, $intData, 0);
		} else {
			$intSent = $this->proxyObject->ph_write($strData);
		}
		
		return $intSent;
	}
	
	public function setProxy($host, $port) {
		$this->usingProxy = true;
		
		$this->proxySettings["address"] = $host;
		$this->proxySettings["port"] = $port;
	}
	
}

?>
