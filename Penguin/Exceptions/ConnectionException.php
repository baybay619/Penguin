<?php

namespace Penguin\Exceptions;

class ConnectionException extends \Exception {
	
	public function __construct($strError, $intError, $mixOther = null){
		$this->strError = $strError;
		$this->intError = $intError;
	
		echo $this->intError, ' - ', $this->strError, chr(10);
	}
	
}

?>
