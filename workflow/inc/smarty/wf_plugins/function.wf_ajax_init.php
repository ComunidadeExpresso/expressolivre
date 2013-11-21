<?php
/**
 * Insere código que referencia os arquivos de JavaScript responsáveis pela funcionalidade do Ajax.
 *
 * @package Smarty
 * @subpackage wf_plugins
 * @version 1.1
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @param array $params Array de parametros para a função
 * @param object &$smarty Instância do objeto smarty em uso
 * @return string codigo com referencias aos JavaScripts.
 * @access public
 */
function smarty_function_wf_ajax_init($params, &$smarty)
{
	static $wf_ajax_init = false;

	if (!$wf_ajax_init)
	{
		$wf_ajax_init = true;

		$serverPath = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$localPath = PHPGW_SERVER_ROOT;

		$includeFiles = array('JSON', 'function.library', 'NanoRequest.class', 'NanoAjax.class', 'NanoController.class');
		$output = '<script type="text/javascript" src="' . $serverPath . '/workflow/js/jscode/prototype.js?' . @filesize($localPath . '/workflow/js/jscode/prototype.js') . '"></script>';
		foreach ($includeFiles as $file)
			$output .= '<script type="text/javascript" src="' . $serverPath . '/workflow/js/nano/' . $file . '.js?' . @filesize($localPath . '/workflow/js/nano/' . $file . '.js') . '"></script>';
		return $output;
	}
	else
		return '';
}
?>