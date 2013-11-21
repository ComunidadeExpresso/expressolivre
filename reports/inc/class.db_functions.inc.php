<?php
define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
include_once(PHPGW_API_INC.'/class.db.inc.php');

class db_functions
{	
	var $db;
	var $user_id;
	
	function db_functions()
	{
		if (is_array($_SESSION['phpgw_info']['expresso']['server']))
			$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
		else
			$_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];
		
		$this->db = new db();
		$this->db->Halt_On_Error = 'no';
		
		$this->db->connect(
				$_SESSION['phpgw_info']['expresso']['server']['db_name'], 
				$_SESSION['phpgw_info']['expresso']['server']['db_host'],
				$_SESSION['phpgw_info']['expresso']['server']['db_port'],
				$_SESSION['phpgw_info']['expresso']['server']['db_user'],
				$_SESSION['phpgw_info']['expresso']['server']['db_pass'],
				$_SESSION['phpgw_info']['expresso']['server']['db_type']
		);		
		$this->user_id = $_SESSION['phpgw_info']['expresso']['user']['account_id'];
	}

	// BEGIN of functions.
	function read_acl($account_lid)
	{
		$query = "SELECT * FROM phpgw_expressoadmin WHERE manager_lid = '" . $account_lid . "'"; 
		$this->db->query($query);
		while($this->db->next_record())
			$result[] = $this->db->row();
		return $result;
	}
}
?>
