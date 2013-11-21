<?php

final class Errors {
		
	private static $sInstance;
	private $errors;
	
	public static function getInstance(){
		if (!self::$sInstance) {
			self::$sInstance = new Errors();
		}
		return self::$sInstance;
	}
	
	function __construct(){
		$this->errors = array();
		if($handle = fopen(__DIR__."/../../config/Errors.tsv", "r")){	
			while (!feof($handle)) {
				$line = trim(fgets($handle,1024));
				if($line == null || $line[0] == "#")
					continue;
				$error = preg_split("/[\t]+/", $line);
				if(is_array($error)&& count($error) == 3) {
					$this->errors[] = array("code" => $error[0], "key" => $error[1], "message" => $error[2]);
				}
			}
		}	
	}
	
	static public function runException($needle, $argument = false){		
		$error = false;
		$type = (is_int($needle) ? "code" : "key");
		foreach( Errors::getInstance()->errors as $value ){
			if($value[$type] == $needle){
				$error = $value;
				break;
			}
		}
		
		if($error['message'] && $argument){
			$error['message'] = str_replace("%1", $argument, $error['message']);
		}		
		if(!$error){
			Errors::getInstance()->runException("E_UNKNOWN_ERROR");
		}		
		
		throw new ResponseException($error['message'], $error['code']);		 
	}	
		
}