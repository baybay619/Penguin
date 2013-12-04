<?php

namespace Petrel;

abstract class ClientBase {
	
	public $resSocket;
	
	public function connect($strAddress, $intPort){
		$this->resSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		socket_connect($this->resSocket, $strAddress, $intPort);
	}
	
	public function disconnect(){
		socket_close($this->resSocket);
	}
	
	public function recv(){
		$intReceived = socket_recv($this->resSocket, $strData, 8192, 0);

		return $strData;
	}
	
	public function send($strData){
		$strData .= chr(0);
		$intData = strlen($strData);
		
		$intSent = socket_send($this->resSocket, $strData, $intData, 0);
		
		return $intSent;
	}
	
}

?>
