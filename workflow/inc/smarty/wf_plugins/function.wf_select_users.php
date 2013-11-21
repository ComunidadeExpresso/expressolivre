<?php
/**
 * Insere código HTML que permite a seleção de vários usuários e grupos
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name : o nome do campo que irá receber os usuários e grupos selecionados
 * - value : usuários e grupos previamente selecionados. Deve estar no formato de matriz, seguindo o seguinte padrão: ::$matriz["u123"]="Nome do usuário 123" ; $matriz["g321"]="Nome do grupo 321" e, assim por diante.
 * - hide_groups : indica se deve-se omitir os grupos na tela de seleção.
 * - onlyVisibleAccounts : indica se devem ser recuperadas apenas as contas visíveis, ou se as ocultas também devem ser listadas.
 * - get_email : indica se deve ser buscado o e-mail ou o id.
 * - organization : nome da organização que estará previamente selecionada.
 * @param object &$smarty Instância do objeto smarty em uso
 * @return string $output codigo html.
 * @access public
 */
function smarty_function_wf_select_users($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');
	$imagesPath = substr(Factory::getInstance('TemplateServer')->generateImageLink(''), 0, -1);
	$requiredParams = array(
		'name');
	$defaultValues = array(
		'hide_groups' => false,
		'organization' => null,
		'hide_organizations' => false,
		'hide_sectors' => false,
		'onlyVisibleAccounts' => true,
		'useGlobalSearch' => false,
		'useCCParams' => false,
		'get_email' => false,
		'value' => array());
	$extractParams = array(
		'name',
		'value',
		'onlyVisibleAccounts',
		'useGlobalSearch');

	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_users] missing required parameter(s): $required", E_USER_ERROR);

	/* atribui valores default para os parâmetros não passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;

	/* extrai alguns parâmetros da matriz de parâmetros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];

	$name_desc = $name . "_desc";

	/* caso seja passado get_email=true, o parâmetro usePreffix não é utilizado */
	$extraParams = 'usePreffix=1';
	if ($params['hide_groups'] == true)
		$extraParams .= "&hidegroups=1";
	if ($params['organization'] != null)
		$extraParams .= "&change_org=True&organization=" . $params['organization'];
	if ($params['hide_organizations'] == true)
		$extraParams .= "&hideOrganizations=1";
	if ($params['hide_sectors'] == true)
		$extraParams .= "&hideSectors=1";
	if ($params['get_email'] == true)
		$extraParams .= "&mail=1";
	if (empty($onlyVisibleAccounts) || $onlyVisibleAccounts === 'false')
		$extraParams .= "&onlyVisibleAccounts=false";
	else
		$extraParams .= "&onlyVisibleAccounts=true";
	if (empty($useGlobalSearch) || $useGlobalSearch === 'false')
		$extraParams .= "&useGlobalSearch=false";
	else
		$extraParams .= "&useGlobalSearch=true";
	if (!empty($params['useCCParams']) && $params['useCCParams'] !== 'false')
		$extraParams .= "&useCCParams=true";

	$output = '<table border="0"><tr><td>';
	$output .= smarty_function_html_options(array(
											'name' => $name,
											'options' => $value,
											'multiple' => 'multiple',
											'style' => 'width:250px;height:200px',
											'id' => $name,
											'print_result' => false),
										$smarty);
	$output .= "</td><td>";
	$output .= <<<EOF
		<a href='javascript:void(0)' onclick="openParticipants(500, 315, '$name', '$extraParams');"><img border="0" src="$imagesPath/add_group.png"></a>
		<br />
		<a href='javascript:void(0)' onclick="openParticipants(500, 315, '$name', '$extraParams');">Adicionar</a>
		<br /><br />
		<a href='javascript:void(0)' onclick="delUsers('$name');"><img border="0" src="$imagesPath/delete_group.png"></a>
		<br />
		<a href='javascript:void(0)' onclick="delUsers('$name');">Remover</a>
EOF;
	$output .= "</td></tr></table>";
	return $output;

}
?>
