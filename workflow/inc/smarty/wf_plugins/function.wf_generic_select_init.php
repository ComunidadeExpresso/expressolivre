<?php
/**
 * Insere código que referencia arquivos de javascript responsáveis pela funcionalidade. 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (vazio)
 * @param object &$smarty Instância do objeto smarty em uso 
 * @return string $output codigo com referencias aos javascripts. 
 * @access public 
 */
function smarty_function_wf_generic_select_init($params, &$smarty)
{
	$jsFile = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/jscode/generic_select.js';
$output = <<<EOF
	<script language="javascript1.2" src="$jsFile"></script>
EOF;
	return $output;
}
?>
