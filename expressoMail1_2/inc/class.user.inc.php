<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
class user{
		
		function get_user(){
			
			return "<br /><font color='blue'>GET USER</font>".
						"<br />usuario =".$_SESSION['phpgw_info']['expressomail']['user']['userid'].
						"<br />senha =".$_SESSION['phpgw_info']['expressomail']['user']['passwd'];
		}
							
		function verify_user($params){
			
			$userId = $params['userid'];
			$delay =  $params['delay'];
			
			if($delay)
				sleep($delay);
			
			$result = '';					
			
			if($userId == $_SESSION['phpgw_info']['expressomail']['user']['userid'])				
				$result =  '<br /><font color="green">VERIFY USER ... VERIFIED</font>';
			else			
				$result =  '<br /><font color="red">VERIFY USER ... NOT VERIFIED</font>';
			
			return $result;											
		}
		
		function verify_user_get($params){
			
			return $params;
		}	
		
		function verify_user_post($params){
			
			return $this -> verify_user($params);
		}
		
		function get_email(){
			return $_SESSION['phpgw_info']['expressomail']['user']['email'];	
		}

        //MailArchiver login validate operations
        function get_mailarchiver_authid(){
            if((isset($_COOKIE["BALANCEID"])) && ($_COOKIE["BALANCEID"] != null) && ($_COOKIE["BALANCEID"] != " "))
                $cookie_balancer = $_COOKIE["BALANCEID"];
            else
                $cookie_balancer = "";
            if((isset($_COOKIE["sessionid"])) && ($_COOKIE["sessionid"] != null) && ($_COOKIE["sessionid"] != " "))
                $cookie_session = $_COOKIE["sessionid"];
            else
                $cookie_session = "";
            if(isset($_SESSION['phpgw_info']['expressomail']['user']['userid']) && ($_SESSION['phpgw_info']['expressomail']['user']['userid'] != null) && ($_SESSION['phpgw_info']['expressomail']['user']['userid'] != " "))
                $cookie_user = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
            else
                $cookie_user = "";
            
            if(isset($_SESSION['phpgw_info']['expressomail']['user']['passwd']) && ($_SESSION['phpgw_info']['expressomail']['user']['passwd'] != null) && ($_SESSION['phpgw_info']['expressomail']['user']['passwd'] != " "))
                $cookie_pass = $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
            else
                $cookie_pass = "";
            
            return(array($cookie_balancer, $cookie_session, $cookie_user, $cookie_pass));                    
        }

	}
?>
