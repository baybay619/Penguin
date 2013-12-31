<?php

namespace Penguin;

trait Cryptography {
	
	private function encryptPassword($strPassword){
		$strMd5Hash = md5($strPassword);
		$strSwappedMd5Hash = substr($strMd5Hash, 16, 16) . substr($strMd5Hash, 0, 16);
		
		return $strSwappedMd5Hash;
	}
	
	private function generateKey($strPassword, $strRandKey){
		$strKey = strtoupper($this->encryptPassword($strPassword)) . $strRandKey . 'a1ebe00441f5aecb185d0ec178ca2305Y(02.>\'H}t":E1_root';
		$strHash = $this->encryptPassword($strKey);
		
		return $strHash;
	}
	
}

?>
