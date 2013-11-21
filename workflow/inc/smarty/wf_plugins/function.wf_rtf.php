<?php
/** 
 * Este plugin insere um editor de texto rico (RTF). Foram feitas modifica��es no javascript original do editor que permitem a utiliza��o de mais de um editor por p�gina.
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que o editor de texto rico ir� receber. 
 * - text: o conte�do inicial do editor.
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string $output codigo que insere o editor. 
 * @access public 
 */
function smarty_function_wf_rtf($params, &$smarty)
{
	$requiredParams = array(
		'name');
	$defaultValues = array(
		'text' => "");
	$extractParams = array(
		'name',
		'text');

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_rtf] missing required parameter(s): $required", E_USER_ERROR);
	
	/* atribui valores default para os par�metros n�o passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
	
	/* extrai alguns par�metros da matriz de par�metros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];
	
	$output = <<<EOF
<textarea name="$name" id="$name">{$text}</textarea>
<script language="javascript1.2">initDocument('$name');</script>
EOF;

	return $output;
}
?>
