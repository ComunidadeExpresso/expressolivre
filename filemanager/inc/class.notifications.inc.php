<?php

define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
require_once(PHPGW_API_INC . '/class.db.inc.php');

class notifications
{
	private $db;
	private $db_name;
	private $db_host;
	private $db_port;
	private $db_user;
	private $db_pass;
	private $db_type;

	
	var $public_functions = array
	(
		'AddEmail'			=> True,
		'DeleteEmail'		=> True,
		'DeleteEmailUser'	=> True,
		'EmailsToSend'		=> True
	);
	
	public function notifications()
	{
		$this->db_name = $GLOBALS['phpgw_info']['server']['db_name'];
		$this->db_host = $GLOBALS['phpgw_info']['server']['db_host'];
		$this->db_port = $GLOBALS['phpgw_info']['server']['db_port'];
		$this->db_user = $GLOBALS['phpgw_info']['server']['db_user'];
		$this->db_pass = $GLOBALS['phpgw_info']['server']['db_pass'];
		$this->db_type = $GLOBALS['phpgw_info']['server']['db_type'];
		$this->connectDB();

	}

	private final function connectDB()
	{
		$this->db = new db();
		$this->db->connect($this->db_name,$this->db_host,$this->db_port,$this->db_user,$this->db_pass,$this->db_type);		
	}	
	
	public function AddEmail()
	{	
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$emailFrom	= $_GET['emailFrom'];
		$emailTo	= $_GET['emailTo'];
		$return		= "";
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_filemanager_notification WHERE email_from = '".$emailFrom."';";

			if( $this->db->query($query) )
			{
				while( $this->db->next_record())
					$result[] = $this->db->row();					
			}
					
			if( count($result) == 0 )
			{
				$query	= "INSERT INTO phpgw_filemanager_notification(email_from,email_to) VALUES('".$emailFrom."', '".$emailTo."')";
				$return	= $emailTo;
			}
			else
			{
				$email_to	= ( $result[0]['email_to'] ) ? $result[0]['email_to'].",".$emailTo : $emailTo ;
				$query		= "UPDATE phpgw_filemanager_notification SET email_to = '".$email_to."' WHERE email_from = '".$emailFrom."';";
				
				$sort = explode("," , $email_to);
				natsort($sort);
				
				$return		= implode(",", $sort );
			}
			
			if( !$this->db->query($query) )
			{
				$return	= "False";
			}
		}
		
		echo $return;
	}
	
	public function DeleteEmail()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		

		$emailFrom 	= $_GET['emailFrom'];
		$emailTo	= $_GET['emailTo'];
		$return		= "True";
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_filemanager_notification WHERE email_from = '".$emailFrom."';";

			if( $this->db->query($query) )
			{
				while( $this->db->next_record())
					$result[] = $this->db->row();					
			}
			
			$email_to = explode( ",", $result[0]['email_to'] );

			for( $i = 0 ; $i < count($email_to); ++$i )
			{
				if($email_to[$i] == $emailTo)
				{
					unset( $email_to[$i] );
				}
			}
			
			natsort( $email_to );
			
			$return = implode(",", $email_to );
			$query  = "UPDATE phpgw_filemanager_notification SET email_to = '".$return."' WHERE email_from = '".$emailFrom."';";
			

			if( !$this->db->query($query) )
				$return	= "False";
			else
				$return = "True";
		}
		
		echo $return;
	}
	
	public function DeleteEmailUser()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		

		$id 	= $_GET['filemanagerId']; 		
		$return	= "False";
		
		if( $this->db )
		{
			$query = "DELETE FROM phpgw_filemanager_notification WHERE filemanager_id = '".$id."';";

			if( $this->db->query( $query ) )
				$return	= "True";
		}
		
		echo $return;
	}
	
	public function EmailsToSend( $pEmail )
	{
		$query = "SELECT * FROM  phpgw_filemanager_notification WHERE email_from = '".$pEmail."'";
		
		if( $this->db )
		{
			if( $this->db->query($query) )
			{
				while( $this->db->next_record())
					$result[] = $this->db->row();					
			}
		}

		return $result[0]['email_to'];
	}
	
	public function SearchId( $pData )
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		

		$query = "SELECT * FROM phpgw_filemanager_notification WHERE filemanager_id ='".$pData."';";
		
		if( $this->db )
		{
			if( $this->db->query($query) )
			{
				while( $this->db->next_record())
					$result[] = $this->db->row();					
			}
		}
		
		return $result;
	}
	
	public function SearchEmail( $pEmail, $pLimit, $pOffset )
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		

		$query = "SELECT * FROM phpgw_filemanager_notification WHERE email_from like '%".$pEmail."%' " .
				 "ORDER BY email_from OFFSET (".$pOffset."-1)*".$pLimit." LIMIT ".$pLimit.";";

		if( $this->db )
		{
			if( $this->db->query($query, __LINE__, __FILE__, $pOffset) )
			{
				while( $this->db->next_record())
					$result[] = $this->db->row();					
			}
		}
		
		return $result;				
	}
}

?>