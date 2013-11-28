<?
class SOCKS5 {
	
	public $resSocket;
	public $blnConnect;
	public $blnDebug;
	
	public function __construct($strAddress, $intPort){
		if($this->resSocket = pfsockopen($strAddress, $intPort, $intError, $strError)){
			$strSend = pack('C3', 0x05, 0x01, 0x00);
			fwrite($this->resSocket, $strSend);
			
			$strRecv = '';
			while($strBuffer = fread($this->resSocket, 1024)){
				$strRecv .= $strBuffer;
			}
			
			$binResponse = unpack('Cversion/Cmethod', $strRecv);
			if($binResponse['version'] == 0x05 && $binResponse['method'] == 0x00){
				return true;
			}
			
			fclose($this->resSocket);
		}
		return false;
	}
	
	public function connect($strAddress, $intPort){
		if($this->resSocket){
			if(ip2long($strAddress) == -1){
				$strSend = pack('C5', 0x05, 0x01, 0x00, 0x03, strlen($strAddress)) . $strAddress . pack('n', $intPort);
			} else {
				$strSend = pack('C4Nn', 0x05, 0x01, 0x00, 0x01, ip2long(gethostbyname($strAddress)), $intPort);
			}
			fwrite($this->resSocket, $strSend);
			$strRecv = '';
			while ($buffer = fread($this->socket, 1024)) {
				$strRecv .= $buffer;
			}
			$arrResponse = unpack('Cversion/Cresult/Creg/Ctype/Lip/Sport', $strRecv);
			if($arrResponse['version'] == 0x05 and $arrResponse['result'] == 0x00) {
				$this->blnConnect = true;
				return true;
			}
		}
		$this->blnConnect = false;
		return false;
	}
	
}

?>
