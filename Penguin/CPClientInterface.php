<?php

interface CPClientInterface {
	
	public function login($strUsername, $strPassword);
	public function joinServer($strName);
	
	public function addListener($strHandler, Callable $mixCallback);
	public function removeListener($strHandler);
	
	public function sendXt();
	
}

?>
