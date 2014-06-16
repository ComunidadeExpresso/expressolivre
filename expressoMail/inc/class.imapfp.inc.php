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
	 /*
	 #######################################################################
	 ####    Author       : Harish Chauhan          					####
	 ####    Start Date   : 14 Oct,2004               					####
	 ####    End Date     : -- Oct,2004         						####
	 ####    Updated      : 18 Feb,2010        							####
	 #### 	 Modified by  : jakjr, niltonneto  							####
	 #### 	 Description  : Additional Imap Class for not-implemented	####
	 #### 					 functions into PHP-IMAP extension.			####
	 #######################################################################	 
	 */

	class imapfp
	{
		var $host;  // host like 127.0.0.1 or mail.yoursite.com
		var $port;  // port default is 110 or 143
		var $user;  // user for logon
		var $imap;  // imap object
		var $password;  // user paswword
		var $state;   // variable define different state of connection
		var $connection; // handle to a open connection
		var $error;  // error string
		var $must_update;
		var $tag;
		var $mail_box;
		
		function imapfp()
		{
			$this->imap = new imap_functions();
	 		$this->user		= $this->imap->username;
			$this->password = $this->imap->password;
			$this->host		= $this->imap->imap_server;
			$this->port		= $this->imap->imap_port;			
			$this->state="DISCONNECTED";
			$this->connection=null;
			$this->error="";
			$this->must_update=false;
			$this->tag=uniqid("HKC");
			$this->imap_delimiter = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'];
		}

		function get_error()
		{
			if($this->error)
				return $this->error;
		}
 
		function get_state()
		{
			return $this->state;
		}

		function open($host="",$port="")
		{
			if(!empty($host))
			{
				if ($port == 993)
					$this->host="ssl://$host";
				else
					$this->host=$host;
			}
			if(!empty($port))
				$this->port=$port;
			return $this->open_connection();
		}
		
		function close()
		{
			if($this->must_update)
				$this->close_mailbox();
			$this->logout();
			@fclose($this->connection);
			$this->connection=null;
			$this->state="DISCONNECTED";
			return true;
		}
		
		function get_mailboxes_size()
		{			
			// INBOX
			if($this->put_line($this->tag . " GETANNOTATION \"user".$this->imap_delimiter.$this->user ."\" \"/vendor/cmu/cyrus-imapd/size\" \"value.shared\"" ))
			{
				$response_inbox=$this->get_server_responce();
				
				if(substr($response_inbox,strpos($response_inbox,"$this->tag ")+strlen($this->tag)+1,2)!="OK")
				{
					$this->error= "Error : $response !<br />";
					return false;
				}
			}
			else
			{
				$this->error= "Error : Could not send User request. <br />";
				return false;
			}
			$response_inbox_array =  preg_split('/\r\n/', $response_inbox);
			array_pop($response_inbox_array);
			array_shift($response_inbox_array);

			// SUB_FOLDERS
			if($this->put_line($this->tag . " GETANNOTATION \"user".$this->imap_delimiter.$this->user .$this->imap_delimiter."*\" \"/vendor/cmu/cyrus-imapd/size\" \"value.shared\"" ))
			{
				$response_sub=$this->get_server_responce();
				
				if(substr($response_sub,strpos($response_sub,"$this->tag ")+strlen($this->tag)+1,2)!="OK")
				{
					$this->error= "Error : $response !<br />";
					return false;
				}
			}
			else
			{
				$this->error= "Error : Could not send User request. <br />";
				return false;
			}
			
			$response_sub_array =  preg_split('/\r\n/', $response_sub);
			array_pop($response_sub_array);
			array_shift($response_sub_array);
			
			return array_merge($response_inbox_array, $response_sub_array);
		}
				
		//This function is used to get response line from server
		function get_line()
		{
			while(!feof($this->connection))
			{
				$line.=fgets($this->connection);
				if(strlen($line)>=2 && substr($line,-2)=="\r\n")
					return(substr($line,0,-2));
			}
		}
		
		//This function is to retrive the full response message from server
		function get_server_responce()
		{
			$i=0;
			while(1)
			{
				++$i;
				$response.="\r\n".$this->get_line();
				if(substr($response,strpos($response,$this->tag),strlen($this->tag))==$this->tag)
					break;
				
				//jakjr
				if ($i>300)
				{
					if ($response)
						return $response;
					else
						return false;
				}
			}
			return $response;
		}
		// This function is to send the command to server
		function put_line($msg="")
		{
			return @fputs($this->connection,"$msg\r\n");
		}

		//This function is to open the connection to the server 
		function open_connection()
		{
			if($this->state!="DISCONNECTED")
			{
				$this->error= "Error : Already Connected!<br />";
				return false;
			}
			if(empty($this->host) || empty($this->port))			
			{
				$this->error= "Error : Either HOST or PORT is undifined!<br />";
				return false;
			}
			$this->connection= fsockopen($this->host, $this->port, $errno, $errstr, 5);
			if(!$this->connection)
			{
				$this->error= "Could not make a connection to server , Error : $errstr ($errno)<br />";
				return false;
			}
			$respone=$this->get_line();
			$this->state="AUTHORIZATION";
			return true;
		}
		
		//The logout function informs the server that the client is done with the connection.
		function logout()
		{
			//jakjr
			if(($this->state!="AUTHORIZATION") && ($this->state!="AUTHENTICATED"))
			{
				$this->error= "Error : No Connection Found!<br />";
				return false;
			}
			if($this->put_line($this->tag." LOGOUT"))
			{
				$response=$this->get_server_responce();
				if(substr($response,strpos($response,"$this->tag ")+strlen($this->tag)+1,2)!="OK")
				{
					$this->error= "Error : $response !<br />";
					return false;
				}
			}
			else
			{
				$this->error= "Error : Could not send User request. <br />";
				return false;
			}
			return true;
		}
		
		//this function is used to login into server $user is a valid username and $pwd is a valid password.
		function login($user,$pwd)
		{
			$this->user = $user;
			
			if($this->state=="DISCONNECTED")
			{
				$this->error= "Error : No Connection Found!<br />";
				return false;
			}
			if($this->state=="AUTHENTICATED")
			{
				$this->error= "Error : Already Authenticated!<br />";
				return false;
			}
			if($this->put_line($this->tag." LOGIN $user $pwd"))
			{
				$response=$this->get_server_responce();
				
				if(substr($response,strpos($response,"$this->tag ")+strlen($this->tag)+1,2)!="OK")
				{
					$this->error= "Error : $response !<br />";
					return false;
				}
			}
			else
			{
				$this->error= "Error : Could not send User request. <br />";
				return false;
			}
			$this->state="AUTHENTICATED";
			return true;
		}
	}
?>
