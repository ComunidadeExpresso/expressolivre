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
 * Camada View para Mover Instâncias.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class ui_move_instances extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions Lista de métodos que podem ser executados.
	 * @access private
	 */
	var $public_functions = array(
		'form'	=> true
	);

	/**
	 * Carrega os valores que serão utilizados para criação da interface para mover instâncias.
	 * @return void
	 * @access public
	 */
	function form()
	{
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','jscode', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','nano', 'JSON');
		$javaScripts .= $this->get_js_link('workflow','move_instances', 'main');
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'scriptaculous', 'load=effects,dragdrop');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'niftycube');

		$css = $this->get_common_css();
		$css .= $this->get_css_link('move_instances');
		$css .= $this->get_css_link('niftyCorners');

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->display('move_instances.tpl');
	}
}
?>
