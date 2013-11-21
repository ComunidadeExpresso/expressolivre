<?php
/**
 * Este plugin insere um campo input, um bot�o e uma combo box que permitem ao usu�rio buscar uma lista de funcion�rios atrav�s
 * do seu nome, ou parte do nome (atributo 'cn'), no ldap.
 * O usu�rio digita o nome a ser buscado no input e clica no bot�o "Pesquisar". Isso faz uma chamada Ajax que atualiza a combo com
 * todos os registros encontrados. Esta combo box pode ser atualizada de acordo com as necessidades do usu�rio atrav�s dos atributos
 * passados por par�metro. Por exemplo, ela pode apresentar os uid dos registros e passar o uidnumber na submiss�o do formul�rio.
 * Isso � poss�vel quando s�o informados os par�metros opt_id e opt_name, sendo uidnumber e uid, respectivamente para esse exemplo.
 * Os respectivos valores padr�o s�o dn e cn.
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que a combo de sele��o do funcion�rio ir� receber.
 * - handleExpiredSessions: indica se as sess�es expiradas devem ser tratadas automaticamente.
 * - size_input: tamanho do campo input.
 * - value_btn: texto a ser apresentado no bot�o.
 * - class_btn: className do bot�o.
 * - opt_id: atributo a ser atribu�do ao value das options da combo onde s�o carregados os registros buscados (valor padr�o � 'dn').
 * - opt_name: atributo a ser atribu�do ao innerHTML das options da combo onde s�o carregados os registros buscados (valor padr�o � 'cn').
 * @param object &$smarty Inst�ncia do objeto smarty em uso
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

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_ldap_users] missing required parameter(s): $required", E_USER_ERROR);

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

	/* define o nome do campo input, � usado o nome atribu�do � combo concatenado ao sufixo "_txt" */
	$name_input = $name . "_txt";

	/* define o nome do span, � usado o nome atribu�do � combo concatenado ao sufixo "_span" */
	$name_span  = $name . "_span";

	/* define o nome da imagem, � usado o nome atribu�do � combo concatenado ao sufixo "_img" */
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
