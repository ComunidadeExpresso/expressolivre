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

/**
 * Classe utilizada para melhorar a segurança do módulo ao se executar código dos processos
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowSecurity
{
	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	public function WorkflowSecurity()
	{
	}

	/**
	 * Aplica as diretivas de segurança do módulo
	 * @return void
	 * @access public
	 */
	public function enableSecurityPolicy()
	{
		$this->ensureEnvironmentProperWorking();
		WorkflowWatcher::workflowWatcherEnableSecurity();
		$this->protectDatabaseObjects();
		$this->removeSensitiveInformation();
	}

	/**
	 * Garante que o ambiente funcionará corretamente após a ativação da segurança
	 * @return void
	 * @access private
	 */
	private function ensureEnvironmentProperWorking()
	{
		/* garante que o objeto de DataBase do Expresso estará disponível */
		Factory::getInstance('WorkflowObjects')->getDBExpresso();
	}

	/**
	 * Protege os objetos de banco de dados (classe DB) conhecidos e que estão na $GLOBALS
	 * @return void
	 * @access public
	 */
	public function protectDatabaseObjects()
	{
		$variables = array();
		$variables[] = &$GLOBALS['phpgw']->accounts->db;
		$variables[] = &$GLOBALS['phpgw']->applications->db;
		$variables[] = &$GLOBALS['phpgw']->acl->db;
		$variables[] = &$GLOBALS['phpgw']->hooks->db;
		$variables[] = &$GLOBALS['phpgw']->preferences->db;
		$variables[] = &$GLOBALS['phpgw']->session->db;
		$variables[] = &$GLOBALS['phpgw']->translation->db;
		$variables[] = &$GLOBALS['run_activity']->categories->db;
		$variables[] = &$GLOBALS['run_activity']->categories->db2;
		$variables[] = &$GLOBALS['phpgw']->db;
		foreach ($variables as &$variable)
		{
			if (is_null($variable) || (get_class($variable) !== 'db'))
				continue;
			$this->removeSensitiveInformationFromDatabaseObject($variable);
			$variable = Factory::newInstance('WorkflowWatcher', $variable);
		}
	}

	/**
	 * Remove informações sensíveis de variáveis que o código dos processos pode acessar ($GLOBALS e $_SESSION)
	 * @return void
	 * @access public
	 */
	public function removeSensitiveInformation()
	{
		unset(
			$GLOBALS['phpgw_info']['server']['db_host'],
			$GLOBALS['phpgw_info']['server']['db_port'],
			$GLOBALS['phpgw_info']['server']['db_name'],
			$GLOBALS['phpgw_info']['server']['db_user'],
			$GLOBALS['phpgw_info']['server']['db_pass'],
			$GLOBALS['phpgw_info']['server']['db_type'],

			$_SESSION['phpgw_info']['workflow']['server']['db_host'],
			$_SESSION['phpgw_info']['workflow']['server']['db_port'],
			$_SESSION['phpgw_info']['workflow']['server']['db_name'],
			$_SESSION['phpgw_info']['workflow']['server']['db_user'],
			$_SESSION['phpgw_info']['workflow']['server']['db_pass'],
			$_SESSION['phpgw_info']['workflow']['server']['db_type'],

			$_SESSION['phpgw_info']['expressomail']['server']['db_host'],
			$_SESSION['phpgw_info']['expressomail']['server']['db_port'],
			$_SESSION['phpgw_info']['expressomail']['server']['db_name'],
			$_SESSION['phpgw_info']['expressomail']['server']['db_user'],
			$_SESSION['phpgw_info']['expressomail']['server']['db_pass'],
			$_SESSION['phpgw_info']['expressomail']['server']['db_type'],

			$GLOBALS['phpgw_domain']['default']
		);
	}

	/**
	 * Remove informações de objetos de banco de dados
	 * @return void
	 * @access public
	 */
	public function removeSensitiveInformationFromDatabaseObject(&$object)
	{
		$object->User = '';
		$object->Password = '';
		$object->Database = '';
		$object->Port = '';
		$object->Host = '';
		$object->Link_ID->host = '';
	}
}
?>
