<?php

class XTParser {
	
	public static function GetHandler($rawPacket){
		if(XTParser::IsValid($rawPacket)){
			$arrPacket = XTParser::ParseRaw($rawPacket);
			if(isset($arrPacket[1])){
				$strHandler = $arrPacket[1];
			
				return $strHandler;
			}
		}
		return false;
	}
	
	public static function IsValid($rawPacket){
		if(strpos($rawPacket, '%xt%') !== false && sizeof(explode('%', $rawPacket)) >= 3){
			return true;
		} else {
			return false;
		}
	}
	
	public static function ParseRaw($rawPacket){
		$arrData = explode(chr(0), $rawPacket);
		list($strData) = $arrData;
		
		$arrPacket = explode('%', $strData);
		array_shift($arrPacket);
		array_pop($arrPacket);
		
		return $arrPacket;
	}
	
	public static function ParseVertical($verticalData){
		$arrVertical = explode('|', $verticalData);
		array_pop($arrVertical);
		
		return $arrVertical;
	}
	
}

?>
