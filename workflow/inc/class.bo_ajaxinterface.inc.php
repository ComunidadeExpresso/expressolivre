<?php
/**************************************************************************\
* eGroupWare                                                 *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/**
 * Caracter separador
 * @name SEP
 */
if (!defined('PHPGW_SERVER_ROOT'))
{
	define('SEP', '/');
	/**
	 * Raiz do servidor
	 * @name PHPGW_SERVER_ROOT
	 */
	define('PHPGW_SERVER_ROOT' , $_SESSION['phpgw_info']['workflow']['server_root']);
	/**
	 * Caminho para o diretorio INCLUDE
	 * @name PHPGW_INCLUDE_ROOT
	 */

	define('PHPGW_INCLUDE_ROOT', $_SESSION['phpgw_info']['workflow']['phpgw_include_root']);
	/**
	 * Caminho para a PHPGW_API
	 * @name PHPGW_API_INC
	 */
	define('PHPGW_API_INC'     , $_SESSION['phpgw_info']['workflow']['phpgw_api_inc']);
}

require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'common.inc.php');
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'engine' . SEP . 'class.ajax_config.inc.php');
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'engine' . SEP . 'config.ajax.inc.php');

/**
 * Implementa o suporte básico para execução de métodos requisitados via AJAX
 * Cria objetos globais para bancos de dados, ldap, config do eGroupware e
 * acl do workflow
 *
 * @package Workflow
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 * @author Sidnei Augusto C Drovetto - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @access public
*/
class bo_ajaxinterface
{
	/**
	* Cria objetos globais para o ldap, banco do expresso, banco do workflow,
	* e acl do workflow
	*
	* @return void
	* @access public
	*/
	function bo_ajaxinterface()
	{
		if (isset($_SESSION['phpgw_info']['workflow']['account_id']))
		{
			$GLOBALS['ajax']->ldap = &Factory::getInstance('ajax_ldap');
			$GLOBALS['ajax']->db =& Factory::getInstance('WorkflowObjects')->getDBExpresso();
			$GLOBALS['ajax']->db->Halt_On_Error = 'no';

			$GLOBALS['ajax']->db_workflow =& Factory::getInstance('WorkflowObjects')->getDBWorkflow();
			$GLOBALS['ajax']->db_workflow->Halt_On_Error = 'no';

			$GLOBALS['phpgw']->ADOdb = &$GLOBALS['ajax']->db->Link_ID;
			$GLOBALS['ajax']->acl = &Factory::getInstance('so_adminaccess', Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
		else
		{
			die("Impossível executar a operação solicitada.");
		}
	}

	/**
	* Fecha a conexão com os objetos globais
	*
	* @return void
	* @access public
	*/
	function disconnect_all()
	{
		$GLOBALS['ajax']->db->Link_ID->Close();
		$GLOBALS['ajax']->db_workflow->Link_ID->Close();
		$GLOBALS['ajax']->ldap->close();
	}
}
?>
