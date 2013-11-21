<?php
/**
 * Insere código que regerencia arquivos js e css do jscalendar.
 * 
 * @package Smarty
 * @subpackage wf_plugins
 * @author  boots
 * @author  Sidnei Augusto Drovetto Junior
 * @version  1.1.2
 * @link http://www.dynarch.com/projects/calendar/ {dhtml calendar}
 *          (dynarch.com)
 * @param array $params Array de parametros
 * @param object &$smarty Instância do objeto smarty em uso
 * @return string $_out codigo a ser inserido 
 * @access public
 */
function smarty_function_wf_calendar_init($params, &$smarty)
{
	$path = "";
	if (isset($params['path']))
		$path = $params['path'] . "/";
	else
		$path = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/js/jscalendar/';
	$defaults = array(
		'css' => $path . 'calendar-blue.css',
		'src' => $path . 'calendar.js',
		'lang' => $path . 'calendar-br.js',
		'setup_src' => $path . 'calendar-setup.js',
		'input_format' => $path . 'calendar-input.js');
	
	foreach ($defaults as $field=>$default)
	{
		$_field = "_$field";
		if (array_key_exists($field, $params))
		{
			$$_field = (empty($params[$field])) ? $default : $params[$field];
		}
		else
		{
			$$_field = $default;
		}
	}

$_out = <<<EOF
	<link rel="stylesheet" type="text/css" media="all" href="{$_css}">
	<script type="text/javascript" src="{$_src}"></script>
	<script type="text/javascript" src="{$_lang}"></script>
	<script type="text/javascript" src="{$_setup_src}"></script>
	<script type="text/javascript" src="{$_input_format}"></script>
EOF;

	return $_out;
}
?>
