<?php

class CommonFunctions
{
	private $userJabber;
	private $password;
	
	function __construct()
	{
		$this->userJabber	= $_SESSION['phpgw_info']['jabberit_messenger']['user_jabber']."@".$_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit'];
		$this->password		= $_SESSION['phpgw_info']['jabberit_messenger']['passwd'];
	}

	function getUserCurrentUserJabber()
	{
		return "YWxleG5K87W".base64_encode($this->userJabber);
		//return $this->gerador().base64_encode($this->userJabber);
	}	

	function getUserCurrentPassword()
	{
		return "TWxGeG55K7W".base64_encode($this->password);
		//return $this->gerador().base64_encode($this->password);
	}	
	
	private function gerador()
	{
		$char = "ABCDEFGHIJKLMNOPQRSTUVWXTZ0123456789abcdefghiklmnopqrstuvwxyz";
		$key = "";
		
		for( $i = 0 ; $i < 61; ++$i )
		{
			$key   .= substr($char, $i, mt_rand(1,61));	
		}
		
		$key = strlen($key).$key;
		
		return $key;
	}
	
}
?>