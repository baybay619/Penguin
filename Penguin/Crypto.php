<?php

final class Crypto {
	
	public static function encryptPassword($strPassword){
		$strMd5Hash = md5($strPassword);
		$strSwappedMd5Hash = substr($strMd5Hash, 16, 16) . substr($strMd5Hash, 0, 16);
		
		return $strSwappedMd5Hash;
	}
	
	public static function generateKey($strPassword, $strRandKey){
		$strKey = strtoupper(static::encryptPassword($strPassword)) . $strRandKey . 'a1ebe00441f5aecb185d0ec178ca2305Y(02.>\'H}t":E1_root';
		$strHash = static::encryptPassword($strKey);
		
		return $strHash;
	}
	
}

?>
