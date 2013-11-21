<?php
/**
 * Insere código que referencia arquivos de javascript responsáveis pela funcionalidade.
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (vazio)
 * @param object &$smarty Instância do objeto smarty em uso1
 * @return string $output codigo com referencias aos javascripts.
 * @access public
 */
function smarty_function_wf_autocomplete_input_init($params, &$smarty)
{
	$jsEffects		= $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/scriptaculous/effects.js';
	$jsControls		= $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/scriptaculous/controls.js';
	$jsJson			= $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/nano/JSON.js';
	$jsSha1			= $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/jscode/sha1.js';
	$jsAutocomplete	= $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/jscode/wf_autocomplete_input.js';
	$output = <<<EOF
	<script type="text/javascript" src="$jsEffects"></script>
	<script type="text/javascript" src="$jsControls"></script>
	<script type="text/javascript" src="$jsJson"></script>
	<script type="text/javascript" src="$jsSha1"></script>
	<script type="text/javascript" src="$jsAutocomplete"></script>
EOF;
	return $output;
}
?>
