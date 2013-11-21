<?php
/**
 * Este plugin insere um campo input, um botão e uma combo box que permitem ao usuário buscar uma lista de funcionários através
 * do seu nome, ou parte do nome (atributo 'cn'), no ldap.
 * O usuário digita o nome a ser buscado no input e clica no botão "Pesquisar". Isso faz uma chamada Ajax que atualiza a combo com
 * todos os registros encontrados. Esta combo box pode ser atualizada de acordo com as necessidades do usuário através dos atributos
 * passados por parâmetro. Por exemplo, ela pode apresentar os uid dos registros e passar o uidnumber na submissão do formulário.
 * Isso é possível quando são informados os parâmetros opt_id e opt_name, sendo uidnumber e uid, respectivamente para esse exemplo.
 * Os respectivos valores padrão são dn e cn.
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que a combo de seleção do funcionário irá receber.
 * - handleExpiredSessions: indica se as sessões expiradas devem ser tratadas automaticamente.
 * - size_input: tamanho do campo input.
 * - value_btn: texto a ser apresentado no botão.
 * - class_btn: className do botão.
 * - opt_id: atributo a ser atribuído ao value das options da combo onde são carregados os registros buscados (valor padrão é 'dn').
 * - opt_name: atributo a ser atribuído ao innerHTML das options da combo onde são carregados os registros buscados (valor padrão é 'cn').
 * @param object &$smarty Instância do objeto smarty em uso
 * @return string $output codigo que insere os componentes.
 * @access public
 */
function smarty_function_wf_select_ldap_users($params, &$smarty)
{ 
    require_once $smarty->_get_plugin_filepath('function','html_options');
	$imagesPath = substr(Factory::getInstance('TemplateServer')->generateImageLink(''), 0, -1);

	$requiredParams = array(
		'name');
	$defaultValues = array(
		'value_btn'             => 'Pesquisar',
		'handleExpiredSessions' => true,
		'size_input'            => '20',
		'useCCParams'			=> true,
		'opt_id'                => 'dn',
		'opt_name'              => 'cn',
		'opt_complement'        => '');
	$extractParams = array(
		'name',
		'handleExpiredSessions',
		'size_input',
		'useCCParams',
		'value_btn',
		'class_btn',
		'opt_id',
		'opt_name',
		'opt_complement');

	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_ldap_users] missing required parameter(s): $required", E_USER_ERROR);

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

	/* define o nome do campo input, é usado o nome atribuído à combo concatenado ao sufixo "_txt" */
	$name_input = $name . "_txt";

	/* define o nome do span, é usado o nome atribuído à combo concatenado ao sufixo "_span" */
	$name_span  = $name . "_span";

	/* define o nome da imagem, é usado o nome atribuído à combo concatenado ao sufixo "_img" */
	$name_img   = $name . "_img";

	$handleExpiredSessions = ($handleExpiredSessions === true) ? 'true' : 'false';

	$useCCParams = ($useCCParams === true) ? 'true' : 'false';

	/* campos do componente */
	$output = <<<EOF
		<input type="text" id="$name_input" name="$name_input" size="$size_input" />
		<input type="button" class="$class_btn" value="$value_btn" 
			onclick="search_ldap_users_by_cn(document.getElementById('$name_input').value, '$name', '$opt_id', '$opt_name', 
					$handleExpiredSessions, '$opt_complement', $useCCParams)" />
		&nbsp;<img id="$name_img" border="0" src="$imagesPath/loading.gif" style="display:none">
		<span id="$name_span" style="display:none"><br><br><b>Selecione um nome abaixo: </b><br><br>
EOF;

	$output .= smarty_function_html_boxoptions(array_merge(array(
											'name'    => $name,
											'id'      => $name,
											'options' => array('-1' => "")),
											$extraParams),
										$smarty);

	$output .= <<<EOF
		</span>
EOF;

	return $output;
}
?>
