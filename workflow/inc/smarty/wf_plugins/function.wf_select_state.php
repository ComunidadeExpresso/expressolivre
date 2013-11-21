<?php
/**
 * Insere uma combo box que permite ao usuário selecionar um Estado do Brasil. 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (Qualquer outro parâmetro passado será incorporado na tag da combo gerada.)
 * - name: o nome que o elemento HTML receberá. 
 * - value: o ID do Estado que estará previamente selecionado.
 * Qualquer outro parâmetro passado será incorporado na tag da combo gerada.
 * @param object &$smarty Instância do objeto smarty em uso 
 * @return string $output codigo que insere a combobox. 
 * @access public 
 */
function smarty_function_wf_select_state($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');

	$requiredParams = array(
		'name');
	$defaultValues = array(
		'value' => 1);
	$extractParams = array(
		'name',
		'value');
	
	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_state] missing required parameter(s): $required", E_USER_ERROR);
	
	/* atribui valores default para os parâmetros não passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
	
	/* extrai alguns parâmetros da matriz de parâmetros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];
	
	/* parâmetros extras são "acumulados" em uma única variável */
	$extraParams = array();
	foreach ($params as $key => $value_params)
		if (!in_array($key, $extractParams))
			$extraParams[$key] = $value_params;

	$sql = "SELECT id_state, state_name FROM phpgw_cc_state WHERE id_country = 'BR' ORDER BY state_name";

	$result = Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID->query($sql);
	$estados = array();
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		$estados[$row['id_state']] = $row['state_name'];

	$output = smarty_function_html_options(array_merge(array(
											'name' => $name,
											'id' => $name,
											'options' => $estados,
											'selected' => $value,
											'print_result' => false), $extraParams),
										$smarty);
	return $output;
}
?>
