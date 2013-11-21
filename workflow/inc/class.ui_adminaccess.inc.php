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

require_once(dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php');

/**
* Monta a interface inicial da Lista de Controle de Acesso
*
* @package Workflow
* @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
* @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
* @license http://www.gnu.org/copyleft/gpl.html GPL
*/
class ui_adminaccess extends ui_ajaxinterface
{
	/**
	* Relaciona os métodos públicos da classe
	*
	* @var array $public_functions
	* @access private
	*/
	var $public_functions = array(
		'form'	=> true,
	);

	/**
	* Verifica permissão do usuário atual para rodar a lista de controle de acesso
	* Caso não tenha permissão, emite mensagem e encerra execução
	*
	* @return void
	* @access public
	*/
	function ui_adminaccess()
	{
		if (!Factory::getInstance('workflow_acl')->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']))
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminprocesses');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}

	/**
	* Monta a página inicial da interface de controle de acesso, a partir do template, e
	* executa o método inicial JavaScript para preencher dadas
	*
	* @return void
	* @access public
	*/
	function form()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Access Control List');
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','jscode', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','adminaccess', 'main');
		$javaScripts .= $this->get_js_link('workflow','adminaccess', 'control_folder');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'participants');

		$css = $this->get_common_css();

		$tabs = array(
			'Administração',
			'Desenvolvimento',
			'Organograma',
			'Processos',
			'Monitoramento',
			'Aplicações Externas'
		);

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->assign('tabs', $tabs);
		$smarty->display('adminaccess.tpl');
	}
}
?>
