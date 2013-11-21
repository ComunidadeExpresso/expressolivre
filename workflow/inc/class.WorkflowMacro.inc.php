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

/**
 * Classe que implementa algumas ações que se repetem em várias partes do módulo
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowMacro
{
	/**
	 * Construtor da classe WorkflowMacro
	 * @return object Objeto da classe WorkflowMacro
	 * @access public
	 */
	public function WorkflowMacro()
	{
	}

	/**
	 * Prepare the ExpressoLivre/Workflow's environment
	 * @param bool $preparingToWS True if it's preparing the environment to a webservice call, otherwise False
	 * @return void
	 * @access public
	 */
	public function prepareEnvironment($preparingToWS = false)
	{
		define('SEP', '/');
		/* if it's a command line or webservice call, set $currentApplication to 'login' */
		$currentApplication = ((php_sapi_name() == 'cli') || $preparingToWS) ? 'login' : 'home';
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => true, 'nonavbar' => true, 'currentapp' => $currentApplication, 'enable_network_class' => true, 'enable_contacts_class' => true, 'enable_nextmatchs_class' => true);
		require_once dirname(__FILE__) . '/../../header.inc.php';
		require dirname(__FILE__) . '/../setup/setup.inc.php'; /* DO NOT USE require_once */
		$GLOBALS['phpgw_info']['apps']['workflow'] = $setup_info['workflow'];
		$row = Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID->query('SELECT config_value FROM phpgw_config WHERE config_app = ? AND config_name = ?', array('phpgwapi', 'files_dir'))->fetchRow();
		$_SESSION['phpgw_info']['workflow']['vfs_basedir'] = ($row !== false) ? $row['config_value'] : '/home/expressolivre';
		$_SESSION['phpgw_info']['workflow']['phpgw_api_inc'] = PHPGW_API_INC;
		$_SESSION['phpgw_info']['workflow']['account_id'] = $GLOBALS['phpgw_info']['user']['account_id'];
		$_SESSION['phpgw_info']['workflow']['server']['webserver_url'] = $GLOBALS['phpgw_info']['server']['webserver_url'];

		require_once PHPGW_API_INC . '/functions.inc.php';
		require_once 'engine/class.ajax_config.inc.php';
		require_once 'engine/config.ajax.inc.php';
		$GLOBALS['ajax']->ldap = &Factory::getInstance('ajax_ldap');

		/* definição de algumas constantes */
		define('PHPGW_TEMPLATE_DIR', ExecMethod('phpgwapi.phpgw.common.get_tpl_dir', 'phpgwapi'));
		$_SERVER['DOCUMENT_ROOT'] = PHPGW_SERVER_ROOT;
	}

	/**
	 * Prepara o ambiente disponibilizado pelo Workflow para um dado processo
	 * @return void
	 * @access public
	 */
	public function prepareProcessEnvironment($processID)
	{
		require_once PHPGW_SERVER_ROOT . '/workflow/inc/local/functions/local.functions.php';

		$runtime = &Factory::getInstance('WfRuntime');
		$runtime->loadProcess($processID);

		/* GLOBALS */
		$GLOBALS['workflow']['wf_runtime'] = &$runtime;
		$GLOBALS['workflow']['wf_normalized_name'] = $runtime->process->getNormalizedName();
	}
}
?>
