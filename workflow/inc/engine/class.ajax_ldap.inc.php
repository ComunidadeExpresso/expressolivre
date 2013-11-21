<?php
	/**
	* @package Galaxia
	* @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
	*/

class ajax_ldap
{
	/**
 	* @var object $ds data source
	* @access public
	*/
	var $ds;
	/**
 	* @var $user_context
	* @access public
	*/
	var $user_context  = '';
	/**
 	* @var $group_context
	* @access public
	*/
	var $group_context = '';
	/**
   	*
   	* @return void
   	* @access public
   	*/
	function ajax_ldap()
	{
		$tmpLDAP = &Factory::getInstance('WorkflowLDAP');
		$this->user_context  = $tmpLDAP->getUserContext();
		$this->group_context = $tmpLDAP->getGroupContext();

		$this->ds =& Factory::getInstance('WorkflowObjects')->getLDAP();
	}

	/**
   	* @param integer $account_id
   	* @param $wich
   	* @return
   	* @access public
   	*/
	function id2name($account_id,$which='account_lid')
	{
		if ($which == 'account_lid' || $which == 'account_type')	// groups only support account_lid and account_type
		{
			$allValues = array();
			$sri = ldap_search($this->ds, $this->group_context, '(&(gidnumber=' . (int)$account_id . ')(phpgwaccounttype=g))');
			$allValues = ldap_get_entries($this->ds, $sri);

			$attr = $which == 'account_lid' ? 'cn' : 'phpgwaccounttype';
			if (@$allValues[0]['cn'][0])
			{
				return $allValues[0]['cn'][0];
			}
		}
		$to_ldap = array(
				'account_lid'   => 'uid',
				'account_email' => 'mail',
				'account_firstname' => 'surname',
				'account_lastname'  => 'cn',
				'account_type'      => 'phpgwaccounttype',
		);
		if (!isset($to_ldap[$which])) return False;

		$allValues = array();
		$sri = ldap_search($this->ds, $this->user_context, '(&(uidnumber=' . (int)$account_id . ')(phpgwaccounttype=u))');
		$allValues = ldap_get_entries($this->ds, $sri);

		if (@$allValues[0][$to_ldap[$which]][0])
		{
			return $allValues[0][$to_ldap[$which]][0];
		}
		return False;
	}

	/**
   	* Get full account data from account id
   	* @return array the whole config-array for that app
   	* @access public
   	*/
	function id2fullname($account_id)
    {
		return Factory::getInstance('WorkflowLDAP')->getName($account_id);
    }

    /**
   	* Close the ldap connection
   	* @return void
   	* @access public
   	*/
    function close()
    {
        ldap_close($this->ds);
    }

    /**
    * Return groups IDs and names of a given user
    * @param $accountID ID of the user that we want to know the groups
   	* @return mixed Array containing the groups informations or false in case no group was found
   	* @access public
   	*/
    function membership($accountID = '')
	{
		if ($accountID == '')
			$accountID = $_SESSION['phpgw_info']['workflow']['account_id'];

		$ldap =& Factory::getInstance('WorkflowLDAP');
		if (count($output = $ldap->getUserGroups($accountID)) == 0)
			return false;
		else
			return $output;
	}
	/**
	* @param string $app
	* @param string $required
	* @param string $accountid
   	* @return mixed
   	* @access public
   	*/
	function get_location_list_for_id($app, $required, $accountid = '')
	{
		$GLOBALS['ajax']->db->select('phpgw_acl','acl_location,acl_rights',array(
				'acl_appname' => $app,
				'acl_account' => $accountid,
			),__LINE__,__FILE__);

		$locations = false;
		while ($GLOBALS['ajax']->db->next_record())
		{
			if ($GLOBALS['ajax']->db->f('acl_rights') & $required)
			{
				$locations[] = $GLOBALS['ajax']->db->f('acl_location');
			}
		}
		return $locations;
	}
	/**
	* Return members from accountid
	*
	* @param integer $accountid
   	* @return mixed (boolean or array)
   	* @access public
   	*/
	function member($accountid = '')
	{
			$security_equals = Array();
			$security_equals = $this->get_ids_for_location($accountid, 1, 'phpgw_group');

			if ($security_equals == False)
			{
				return False;
			}

			$members = array();
            $security_equals_count = count($security_equals);

			for ($idx=0; $idx<$security_equals_count; ++$idx)
			{
				$name = $this->id2name((int)$security_equals[$idx]);
				$members[] = Array('account_id' => (int)$security_equals[$idx], 'account_name' => $name);
			}

			return $members;
		}
	/**
	 * Return accounts from location
	 *
	 * @param $location
	 * @param $required
	 * @param $app
   	 * @return void
   	 * @access public
   	 */
	function get_ids_for_location($location, $required, $app = False)
	{

			$GLOBALS['ajax']->db->select('phpgw_acl',array('acl_account','acl_rights'),array(
				'acl_appname'  => $app,
				'acl_location' => $location,
				),__LINE__,__FILE__);

			$accounts = false;
			while ($GLOBALS['ajax']->db->next_record())
			{
				if (!!($GLOBALS['ajax']->db->f('acl_rights') & $required))
				{
					$accounts[] = (int) $GLOBALS['ajax']->db->f('acl_account');
				}
			}
			return $accounts;
	}

}
?>
