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

require_once dirname(__FILE__) . SEP . 'common.inc.php';
require_once dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php';

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @author Rodrigo Daniel C Lira - rodrigo.lira@gmail.com
 */
class ui_participants extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions Array contento as funções públicas
	 * @access public
	 */
	var $public_functions = array(
        'form'  => true
    );
	/**
	 * @var object $bo Objeto que representa a camada Business
	 * @access public
	 */
	var $bo;

	/**
	 * Contrutor da classe
	 * @access public
	 * @return object
	 */
	function ui_participants()
	{
		$this->bo = Factory::getInstance('bo_participants');
	}

	/**
	 * Constrói a interface de participantes
	 * @access public
	 * @return void
	 */
	function form()
	{
		$smarty = Factory::getInstance('workflow_smarty', false);
		$smarty->setHeader(workflow_smarty::SHOW_HEADER | workflow_smarty::SHOW_FOOTER);
		$ldap = Factory::getInstance('WorkflowLDAP');
		$userDN = $GLOBALS['phpgw_info']['user']['account_dn'];
		$account = Factory::getInstance('accounts', $userDN);
		$organizationList = $this->bo->getOrganizations();

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','jscode', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'participants');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'connector');

		/* define the entities that should be listed */
		if (!isset($_REQUEST['entities']))
		{
			/* for backward compatibility */
			$entities = 'u';
			if (isset($_REQUEST['mail']))
				$entities .= 'l';
			else
				if (!isset($_REQUEST['hidegroups']))
					$entities .= 'g';
		}
		else
			$entities = $_REQUEST['entities'];

		/* define the type of information that should be returned */
		if (isset($_REQUEST['mail']))
			$id = 'mail'; //return the e-mail
		else
			$id = 'id'; //return the uidnumber

		/* indicates wether the uidnumbers should be preffixed with a char that represents the type of the entity (e.g. 'u' for users) */
		if (isset($_REQUEST['usePreffix']))
			$usePreffix = ($_REQUEST['usePreffix'] == '1') ? true : false;
		else
			$usePreffix = false;

		$hideOrganizations = ($_REQUEST['hideOrganizations'] == '1') ? true : false;
		$hideSectors = ($_REQUEST['hideSectors'] == '1') ? true : false;

		// the default value of $onlyVisibleAccounts is true
		if((isset($_REQUEST['onlyVisibleAccounts'])) && (empty($_REQUEST['onlyVisibleAccounts']) || $_REQUEST['onlyVisibleAccounts'] === 'false'))
			$onlyVisibleAccounts = false;
		else
			$onlyVisibleAccounts = true;

		// the default value of $useGlobalSearch is false
		if(!isset($_REQUEST['useGlobalSearch']) || empty($_REQUEST['useGlobalSearch']) || $_REQUEST['useGlobalSearch'] === 'false')
			$useGlobalSearch = false;
		else
			$useGlobalSearch = true;

		/* define the initial organization */
		$selectedOrganization = $ldap->getOrganizationFromDN($userDN);
		/* check for request supplied organization */
		if (isset($_REQUEST['organization']))
			if (preg_match('/^[a-z0-9_\- ]+$/i', $_REQUEST['organization']) > 0)
				$selectedOrganization = $_REQUEST['organization'];
		/* if the organization is invalid, use the first in the list */
		if (($selectedOrganization === false) || !in_array(strtolower($selectedOrganization), array_map('strtolower', $organizationList)))
			$selectedOrganization = $organizationList[0];

		$organizationRoot = 'ou=' . $selectedOrganization . ',' . $ldap->getLDAPContext();
		$selectedSector = $organizationRoot;
		if (isset($_REQUEST['sector']))
		{
			if (preg_match('/^[a-z0-9_\- =,]+$/i', $_REQUEST['sector']) > 0)
			{
				$requestedSector = $ldap->getSectors(null, false, $_REQUEST['sector']);
				if ($requestedSector !== false)
					$selectedSector = $_REQUEST['sector'];
			}
		}

		/* send the variables to Smarty */
		$smarty->assign('organizations', $organizationList);
		$smarty->assign('selectedOrganization', $selectedOrganization);
		$smarty->assign('sectors', $this->bo->getSectors(array('organization' => $selectedOrganization, 'onlyVisibleAccounts' => $onlyVisibleAccounts), true));
		$smarty->assign('selectedSector', $selectedSector);
		$smarty->assign('participants', $this->bo->getEntities(array('entities' => $entities, 'id' => $id, 'context' => $selectedSector, 'onlyVisibleAccounts' => $onlyVisibleAccounts, 'usePreffix' => $usePreffix), true));
		$smarty->assign('entities', $entities);
		$smarty->assign('id', $id);
		$smarty->assign('target', $_REQUEST['target_element']);
		$smarty->assign('usePreffix', $usePreffix);
		$smarty->assign('useCCParams', $_REQUEST['useCCParams']);
		$smarty->assign('hideOrganizations', $hideOrganizations);
		$smarty->assign('hideSectors', $hideSectors);
		$smarty->assign('onlyVisibleAccounts', $onlyVisibleAccounts);
		$smarty->assign('useGlobalSearch', $useGlobalSearch);
		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->display('participants.tpl');
	}

}
?>
