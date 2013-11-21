<?php
/** 
 * Este plugin insere duas combo boxes que permitem ao usu�rio selecionar uma cidade do Brasil. 
 * A primeira combo � utilizada para fazer a sele��o de um Estado. Uma vez feita esta sele��o, � feita uma chamada Ajax que carrega as cidades daquele Estado na segunda combo.
 * E, � nesta segunda combo que o usu�rio efetivamente seleciona a cidade. 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que a combo de sele��o de cidade ir� receber. 
 * - value: o ID da cidade que aparecer� inicialmente selecionada. 
 * - state_name: o nome da combo de sele��o de Estado. 
 * - state_value: o ID do Estado inicialmente selecionado.
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string $output codigo que insere os comboboxes. 
 * @access public 
 */
function smarty_function_wf_select_city($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','wf_select_state');

	$requiredParams = array(
		'name');
	$defaultValues = array(
		'value' => 1,
		'handleExpiredSessions' => true,
		'state_value' => 1,
		'state_name' => "_estado_" . rand() . '_' . rand());
	$extractParams = array(
		'name',
		'handleExpiredSessions',
		'value',
		'state_value',
		'state_name');

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_city] missing required parameter(s): $required", E_USER_ERROR);

	/* atribui valores default para os par�metros n�o passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;

	/* extrai alguns par�metros da matriz de par�metros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];

	/* par�metros extras s�o "acumulados" em uma �nica vari�vel */
	$extraParams = array();
	foreach ($params as $key => $value_params)
		if (!in_array($key, $extractParams))
			$extraParams[$key] = $value_params;

	$db = &Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID;
	if (isset($params['value']))
	{
		$sql = "SELECT id_state FROM phpgw_cc_city WHERE id_city = ?";
		$result = $db->query($sql, array($value));
		if ($result)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$state_value = $row['id_state'];
		}
		else
		{
			$state_value = 1;
			$value = 1;
		}
	}

	$handleExpiredSessions = ($handleExpiredSessions === true) ? 'true' : 'false';

	$output = smarty_function_wf_select_state(array(
											'name' => $state_name,
											'value' => $state_value,
											'onchange' => "draw_cities('$name', this.value, null, $handleExpiredSessions);"),
										$smarty);
	$output .= '<br/>';

	$sql = "SELECT id_city, city_name FROM phpgw_cc_city WHERE id_state = ? ORDER BY city_name";
	$result = $db->query($sql, array($state_value));
	$cities = array();
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		$cidades[$row['id_city']] = $row['city_name'];

	$output .= smarty_function_html_options(array_merge(array(
											'name' => $name,
											'id' => $name,
											'options' => $cidades,
											'selected' => $value,
											'print_result' => false), $extraParams),
										$smarty);
	return $output;
}
?>
