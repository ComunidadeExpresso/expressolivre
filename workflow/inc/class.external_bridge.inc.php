<?php

/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once 'common.inc.php';
require_once 'engine/config.egw.inc.php';

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class external_bridge
{
	/**
	 * @var string $siteAddress the address of the site
	 * @access public
	 */
	var $siteAddress;

	/**
	 * @var object  $acl access rights object
	 * @access public
	 */
	var $acl;
	/**
	 * @var object $db
	 * @access public
	 */
	var $db;
	/**
	 * @var array $public_functions
	 * @access public
	 */
	var $public_functions = array(
		'render' => True
	);
	/**
	 * External bridge
	 * @access public
	 * @return void
 	 */
	function external_bridge()
	{
		$this->db = Factory::getInstance('WorkflowObjects')->getDBGalaxia();
		$this->acl = &Factory::getInstance('so_adminaccess', Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
	}
	/**
	 * load Data
	 * @access public
	 * @return void
 	 */
	function loadData($site)
	{
		/* define the dynamic values that can be used in the login process */
		$tmpUser = "";
		$tmpOrg = "";

		$tmpUser = $GLOBALS['phpgw_info']['user']['account_lid'];
		$tmpOrg = explode(",ou=", $GLOBALS['phpgw_info']['user']['account_dn']);
		$tmpOrg = explode(",", $tmpOrg[1]);
		$tmpOrg = $tmpOrg[0];

		$replace = array(
					'%user%' => $tmpUser,
					'%organization%' => $tmpOrg,
					'%password%' => $GLOBALS['phpgw_info']['user']['passwd']);

		/* select the required form values for submission */
		$result = $this->db->query("SELECT address, post FROM egw_wf_external_application WHERE (external_application_id = {$site})");
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		if (!$row)
			die("");

		$dataTmp = str_replace("\r", "", $row['post']);
		$dataTmp = explode("\n", $dataTmp);

		$this->siteAddress = $row['address'];

		$data = array();
		foreach ($dataTmp as $aux)
		{
			list($varName,$value) = explode("=", $aux, 2);
			$data["$varName"] = $value;
		}

		/* replace the tags with the actual values */
		foreach ($data as $key => $value)
			foreach ($replace as $before => $after)
				$data[$key] = str_replace($before, $after, $data[$key]);

		/* load the data */
	    $output = array();
	    foreach ($data as $key => $value)
	        $output[] = array(
			            "name" => $key,
			            "value" => $value);

		return $output;
	}
	/**
	 * External bridge
	 * @access public
	 * @return void
 	 */
	function render()
	{
		if (($GLOBALS['phpgw_info']['server']['use_https'] > 0) && ($_SERVER['HTTPS'] != 'on'))
		{
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}

		/* validate the var and avoid SQL injection */
		$site = $_REQUEST['site'];

		$redirect = false;
		if (!is_numeric($site))
			$redirect = true;
		else
		{
			/* check if the user has the permission to access the requested site */
			$site = (int) $site;
			if (!$this->acl->checkUserGroupAccessToResource('APX', $GLOBALS['phpgw_info']['user']['account_id'], $site))
				$redirect = true;
		}

		/* in case of any error, send the user to the frontpage */
		if ($redirect)
		{
			header("Location: index.php");
			exit;
		}

		/* generates the form */
		$generatedForm = '';
		$loginData = $this->loadData($site);
		foreach ($loginData as $formData)
			$generatedForm .= "<input type=\"hidden\" name=\"" . $formData['name']  . "\" id=\"" . $formData['name']  . "\" value=\"" . $formData['value']  . "\">";
		$generatedForm = 'document.write(\'' . $generatedForm . '\');';

		/* encode the form before submission */
		$encodedForm = '';
		for ($i = 0; $i < strlen($generatedForm); ++$i)
			$encodedForm .= '%' . bin2hex($generatedForm[$i]);
		$encodedForm = '<script type="text/javascript">eval(unescape(\'' . $encodedForm . '\'))</script>';

		/* assign variables to the template */
		$smarty = Factory::getInstance('workflow_smarty', false);
		$smarty->assign('encodedForm', $encodedForm);
		$smarty->assign('siteAddress', $this->siteAddress);
		$smarty->display('external_bridge.tpl');
	}
}
?>
