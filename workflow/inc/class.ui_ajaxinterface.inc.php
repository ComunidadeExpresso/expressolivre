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
require_once 'engine/class.ajax_config.inc.php';
require_once 'engine/config.ajax.inc.php';
/**
 * Cria o ambiente de sessão para rodar Ajax e implementa alguns métodos
 * básicos para inclusão de JavaScript e CSS
 *  
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 */
class ui_ajaxinterface
{
	/**
	* @var int $num_version Identifica a verão dos arquivos JavaScript e CSS. Incremente o valor deste atribuito
	* sempre que existirem alterações nos arquivos, para forçar o download pelo browser
	* @access public 
	*/		
	var $num_version = 213;

	/**
	 * Construtor
	 * @access public
	 * @return object
	 */			
	function ui_ajaxinterface()
	{
	}

	/**
	* Coloca na variável de sessão todas as informações que serão necessárias para 
	* rodar os métodos acionados pelas requisições Ajax.
	* 
	* @return void
	* @access public 
	*/
	function set_wf_session()
	{
		$acl = Factory::getInstance('workflow_acl');
		$_SESSION['phpgw_info']['workflow']['server_root'] = PHPGW_SERVER_ROOT;
		$_SESSION['phpgw_info']['workflow']['phpgw_api_inc'] = PHPGW_API_INC;
		$_SESSION['phpgw_info']['workflow']['phpgw_include_root'] = PHPGW_INCLUDE_ROOT;
		$vfs = createobject('phpgwapi.vfs');
		$_SESSION['phpgw_info']['workflow']['vfs_basedir'] = $vfs->basedir;
		$_SESSION['phpgw_info']['workflow']['server'] = $GLOBALS['phpgw_info']['server'];
		$_SESSION['phpgw_info']['workflow']['lang'] = $GLOBALS['lang'];
		$_SESSION['phpgw_info']['workflow']['account_id'] = $GLOBALS['phpgw_info']['user']['account_id'];
		$_SESSION['phpgw_info']['workflow']['user_groups'] = Factory::getInstance('WorkflowLDAP')->getUserGroups($GLOBALS['phpgw_info']['user']['account_id']);
		$user_is_admin = $acl->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']);
		$_SESSION['phpgw_info']['workflow']['user_is_admin'] = $user_is_admin;
		$_SESSION['phpgw_info']['workflow']['user_can_admin_process'] = ($user_is_admin || ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow')));
		$_SESSION['phpgw_info']['workflow']['user_can_admin_instance'] = ($user_is_admin || ($GLOBALS['phpgw']->acl->check('admin_instance_workflow',1,'workflow')));

		$can_monitor = $acl->checkUserGroupAccessToType('MON', $GLOBALS['phpgw_info']['user']['account_id']);
		$_SESSION['phpgw_info']['workflow']['user_can_monitor'] = ($user_is_admin || $can_monitor);
		$_SESSION['phpgw_info']['workflow']['user_can_clean_instances'] = $_SESSION['phpgw_info']['workflow']['user_can_monitor'];
		$_SESSION['phpgw_info']['workflow']['user_can_clean_aborted_instances'] = $_SESSION['phpgw_info']['workflow']['user_can_monitor'];
		$_SESSION['phpgw_info']['workflow']['user']['preferences'] = $GLOBALS['phpgw_info']['user']['preferences']['workflow'];
		$_SESSION['phpgw_info']['workflow']['voip_groups'] = $GLOBALS['phpgw_info']['server']['voip_groups'];
	}

	/**
	* Insere código html para incorporar arquivos JavaScript de uso comum
	* 
	* @return string codigo html para insercao do javascript
	* @access public 
	*/
	function get_common_js()
	{
		$result = "<script language='javascript'>var _web_server_url = '" . $GLOBALS['phpgw_info']['server']['webserver_url'] . "';</script>";
		$result = $result . $this->get_js_link('workflow','jscode', 'globals');			
		$result = $result . $this->get_js_link('workflow','jscode', 'common_functions');
		$result = $result . $this->get_js_link('workflow','jscode', 'abas');
		$result = $result . $this->get_js_link('workflow','jscode', 'connector');
		$result = $result . $this->get_js_link('workflow','jscode', 'sniff_browser');
		$result = $result . $this->get_js_link('workflow','jscode', 'wz_dragdrop');
		$result = $result . $this->get_js_link('workflow','jscode', 'dJSWin');
		$result = $result . $this->get_js_link('workflow','jscode', 'doiMenuDOM');
		
		return $result;
	}

	/**
	* Insere o código html para inclusão do arquivo de estilo common.css
	* 
	* @return string tag html completa para inserção do arquivo common.css
	* @access public 
	*/
	function get_common_css()
	{
		return $this->get_css_link('common');
	}

	/**
	* Insere o código html para inclusão de um arquivo de folha de estilo. Primeiramente
	* procura o arquivo no template do usuário. Se não encontrado monta o link para o
	* template default.
	*
	* @param string $CSSName nome do arquivo de folha de estilo.
	* @param string $mediaType o tipo de mídia (padrão: all)
	* @return string tag html completa para inserção do arquivo solicitado
	* @access public
	*/
	function get_css_link($CSSName, $mediaType = 'all')
	{
		$CSSName = "css/{$CSSName}.css";
		$templateServer = &Factory::getInstance('TemplateServer');
		$CSSLink = $templateServer->getWebFile($CSSName);
		$CSSFile = $templateServer->getSystemFile($CSSName);

		return '<link href="' . $CSSLink . '?' . (file_exists($CSSFile) ? filesize($CSSFile) : $this->num_version) . '" type="text/css" rel="StyleSheet" media="' . $mediaType . '">';
	}

	/**
	* Insere o código html para inclusão de um arquivo JavaScript.
	* 
	* @param string $module nome do módulo do eGroupware (preferencialmente workflow)
	* @param string $js_package nome do grupamento de arquivos javascript
	* @param string $js_name nome do arquivo javascript
	* @param array  $params parametros 
	* @return string tag html completa para inserção do arquivo solicitado 
	* @access public 
	*/
	function get_js_link($module, $js_package, $js_name, $params = null)
	{
		if (is_null($params))
			$params = array();
		else
			if (!is_array($params))
				$params = array($params);

		$localFile = $_SESSION['phpgw_info']['workflow']['server_root'] . SEP . $module . SEP . 'js' . SEP . $js_package . SEP . $js_name.'.js';
		if (@file_exists($localFile))
			$params[] = filesize($localFile);
		else
			$params[] = $this->num_version;
		$js_file = $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.$module.SEP.'js'.SEP.$js_package.SEP.$js_name.'.js';
		return '<script src="'.$js_file.'?'.implode('&', $params).'" type="text/javascript"></script>';
	}
	
	/**
	* Executa a função inicial JavaScript que irá montar os dados da interface.
	* 
	* @param string $param nome da função JavaScript
	* @return string tag html completa com a chamada da função 
	* @access public 
	*/
	function run_init_script($param)
	{
		return '<script language="javascript">' . $param . '</script>';
		}
	
	}
?>
