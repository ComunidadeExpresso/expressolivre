<?php
/** 
 * Insere c�digo que referencia arquivos de javascript respons�veis pela funcionalidade
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (vazio)
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string $output codigo com referencias aos javascripts. 
 * @access public 
 */
function smarty_function_wf_select_city_init($params, &$smarty)
{
	$webServer = $GLOBALS['phpgw_info']['server']['webserver_url'];
	$jsFilePath = $webServer . '/workflow/js';
	$localFile = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'js' . SEP . 'jscode' . SEP . 'connector.js';
	$size = 0;
	if (@file_exists($localFile))
		$size = filesize($localFile);

$output = <<<EOF
<input type="hidden" value="" id="txt_loading">
<script language='javascript'>var _web_server_url = '$webServer';</script>
<script type="text/javascript" src="$jsFilePath/jscode/connector.js?$size"></script>
<script type="text/javascript" src="$jsFilePath/jscode/wf_select_city.js"></script>
EOF;
	return $output;
}
?>
