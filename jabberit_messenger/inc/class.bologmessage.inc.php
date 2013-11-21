<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
require_once(PHPGW_API_INC . '/class.db.inc.php');

class bologmessage
{
	private $db;
	private $db_name;
	private $db_host;
	private $db_port;
	private $db_user;
	private $db_pass;
	private $db_type;
	
	function __construct()
	{
		$this->db = new db();
		$this->db_name = "bandersnatch";
		$this->db_host = "localhost";
		$this->db_port = "3306";
		$this->db_user = "";
		$this->db_pass = "";
		$this->db_type = "mysql";
		$this->connectDB();
	}

	function __destruct()
	{
		$this->db->disconnect();		
	}

	private function connectDB()
	{
		$this->db->connect($this->db_name,$this->db_host,$this->db_port,$this->db_user,$this->db_pass,$this->db_type);
	}	

	public function getMessageUser( $pUser, $pLim1, $pLim2 )
	{
		$query = "SELECT message_from, COUNT(*) AS total_messages, MIN(message_timestamp) AS first_message, MAX(message_timestamp) AS last_message FROM message WHERE message_from like '%".$pUser."%' GROUP BY message_from LIMIT ".$pLim1.",".$pLim2.";";

		$data = array();

		if($this->db->query($query))
		{
			while($this->db->next_record())
				$data[] = $this->db->row();
		}
		
		return $data;
	}
	
	public function getMessageUserDate( $pUser, $pDtFirst, $pDtLast, $pLim1, $pLim2 )
	{
		$data 		= array();
		$field_1	= "message_from";
		$field_2	= "message_to";
		
		$user = explode( '@', $pUser );
		$user = rawurlencode( $user[ 0 ] ) . '@' . $user[ 1 ]; 

		$query = "SELECT ".$field_1.", ".$field_2.", COUNT(*) AS total_messages, MIN(message_timestamp) AS first_message, MAX(message_timestamp) AS last_message FROM message WHERE ".$field_1." = '".$user."' AND message_timestamp BETWEEN '".$pDtFirst."' AND '".$pDtLast."' GROUP BY ".$field_2." LIMIT ".$pLim1.",".$pLim2.";";
		
		if($this->db->query($query))
		{
			while($this->db->next_record())
				$data[] = $this->db->row();
		}
		
		return $data;
	}
	
	public function getMessageUserComplete( $pUser1, $pUser2, $pDtFirst, $pDtLast, $pLim1, $pLim2 )
	{
		$user1 = explode( '@', $pUser1 );
		$user1 = rawurlencode( $user1[ 0 ] ) . '@' . $user1[ 1 ]; 
		
		$user2 = explode( '@', $pUser2 );
		$user2 = rawurlencode( $user2[ 0 ] ) . '@' . $user2[ 1 ]; 
		
		$query = "SELECT message_from, message_to, message_body, message_timestamp FROM message WHERE message_from = '".$user1."' AND message_to = '".$user2."' AND message_timestamp BETWEEN '".$pDtFirst."' AND '".$pDtLast."' ORDER BY message_timestamp DESC LIMIT ".$pLim1.",".$pLim2.";";
		
		if($this->db->query($query))
		{
			while($this->db->next_record())
				$data[] = $this->db->row();
		}
		
		return $data;
	}
}

?>