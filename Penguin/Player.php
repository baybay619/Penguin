<?php

class Player {
	
	private $intPlayerId;
	private $strUsername;
	
	private $intColor;
	private $intHead;
	private $intFace;
	private $intNeck;
	private $intBody;
	private $intHand;
	private $intFeet;
	private $intFlag;
	private $intPhoto;
	
	private $intX;
	private $intY;
	private $intFrame;
	
	public function __construct($arrData){
		list($this->intPlayerId, $this->strUsername, $intUnknown, $this->intColor, $this->intHead, $this->intFace, $this->intNeck, $this->intBody, $this->intHand, $this->intFeet, $this->intFlag, $this->intPhoto, $this->intX, $this->intY, $this->intFrame) = $arrData;
	}
	
	public function __call($strName, $arrArguments){
		$strType = substr($strName, 0, 3);
		switch($strType){
			case 'get':
				$strProperty = substr($strName, 3);
				if(property_exists($this, 'int' . $strProperty)) return $this->{'int' . $strProperty};
				if(property_exists($this, 'str' . $strProperty)) return $this->{'str' . $strProperty};
				if(property_exists($this, 'arr' . $strProperty)) return $this->{'arr' . $strProperty};
				if(property_exists($this, 'bln' . $strProperty)) return $this->{'bln' . $strProperty};
			break;
			case 'set':
				$strProperty = substr($strName, 3);
				list($strValue) = $arrArguments;
				if(property_exists($this, 'int' . $strProperty)) return $this->{'int' . $strProperty} = $strValue;
				if(property_exists($this, 'str' . $strProperty)) return $this->{'str' . $strProperty} = $strValue;
				if(property_exists($this, 'arr' . $strProperty)) return $this->{'arr' . $strProperty} = $strValue;
				if(property_exists($this, 'bln' . $strProperty)) return $this->{'bln' . $strProperty} = $strValue;
			break;
		}
	}

}

?>
