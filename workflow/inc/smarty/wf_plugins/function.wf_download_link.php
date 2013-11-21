<?php
/**
 * Este plugin tem por finalidade criar links para downloads de arquivos. 
 * Estes links n�o apontam para o arquivo que ser� baixado e sim para uma p�gina que ir� enviar o arquivo 
 * (�til para arquivos que est�o no banco de dados). 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - base_url : a URL que ir� enviar o arquivo (tratar a requisi��o). 
 * - text : texto do link gerado 
 * - getParams : par�metro(s) que identifica(m) o(s) download(s).
 * @param object &$smarty clever simulation of a method  
 * @return string $output link para uma p�gina que ir� enviar o arquivo 
 * @access public
 */
function smarty_function_wf_download_link($params, &$smarty)
{
	$requiredParams = array(
		'getParams');
	$defaultValues = array(
		'base_url' => $_SERVER['REQUEST_URI'],
		'text' => 'download',
		'getParams' => '');
	$extractParams = array(
		'base_url',
		'text',
		'getParams');
	
	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_download_link] missing required parameter(s): $required", E_USER_ERROR);
	
	/* atribui valores default para os par�metros n�o passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
	
	/* extrai alguns par�metros da matriz de par�metros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];
	
	/* par�metros extras s�o "acumulados" em uma �nica matriz */
	$extraParams = array();
	foreach ($params as $key => $value)
		if (!in_array($key, $extractParams))
			$extraParams[] = $key . ' = "' . $value . '"';
	
	$base_url .= (strpos($base_url, '?') === false) ? '?' : '&';
	$getParams = '&' . $getParams;

	$output = '<a href="' . $base_url . 'download_mode=true' . $getParams . '" ' . implode(' ', $extraParams)  . '>' . $text . '</a>';
	return $output;
}
?>
