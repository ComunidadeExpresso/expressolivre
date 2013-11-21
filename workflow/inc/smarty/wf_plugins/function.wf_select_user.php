<?php
/**
 * Insere os elementos HTML que permitem a sele��o de usu�rios (ids/e-mails)
 * @package Smarty
 * @subpackage wf_plugins
 * @param object &$smarty Inst�ncia do objeto smarty em uso
 * @param array $params Array de parametros
 * - name : o nome do campo que ir� receber o id do usu�rio selecionado (o nome do usu�rio selecionado estar� no campo de formul�rio de nome "name" seguido do sufixo "_desc"). Quando for configurado para pegar o e-mail, os e-mails estar�o no campo com o nome indicado por "name" e, o conte�do ser� do tipo: "Usu�rio Um" , "Usu�rio Dois" (e assim por diante).
 * - id_value : valor de id de um usu�rio previamente selecionado.
 * - desc_value : nome de um usu�rio previamente selecionado (� ineficaz especificar somente o nome e n�o o id para deixar um usu�rio previamente selecionado).
 * - get_email : indica se deve ser buscado o e-mail ou o id.
 * - email_as_textarea : indica se o campo que receber� os emails deve ser um textarea (valor true) ou input type text (valor false). Este par�metro s� tem validade se get_email for true.
 * - hide_groups : indica se deve-se omitir os grupos na tela de sele��o.
 * - onlyVisibleAccounts : indica se devem ser recuperadas apenas as contas vis�veis, ou se as ocultas tamb�m devem ser listadas.
 * - organization : nome da organiza��o que estar� previamente selecionada.
 * - title : texto da tooltip que aparecer� quando o usu�rio colocar o mouse sobre o �cone para adicionar usu�rio.
 * - cols : largura do campo.
 * - rows : altura do campo.
 * @return string $output codigo com referencias aos javascripts.
 * @access public
 */
function smarty_function_wf_select_user($params, &$smarty)
{
	$requiredParams = array(
		'name');
	$defaultValues = array(
		'id_value' => '',
		'desc_value' => '',
		'get_email' => false,
		'email_as_textarea' => false,
		'hide_groups' => true,
		'organization' => null,
		'entities' => null,
		'title' => '',
		'cols'=> 80,
		'hide_organizations' => false,
		'hide_sectors' => false,
		'onlyVisibleAccounts' => true,
		'useGlobalSearch' => false,
		'useCCParams' => false,
		'size' => 27,
		'rows'=> 2);
	$extractParams = array(
		'name',
		'id_value',
		'desc_value',
		'title',
		'cols',
		'rows',
		'size',
		'onlyVisibleAccounts',
		'useGlobalSearch');

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_user] missing required parameter(s): $required", E_USER_ERROR);

	/* atribui valores default para os par�metros n�o passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;

	/* extrai alguns par�metros da matriz de par�metros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];

	$name_desc = $name . "_desc";

	if ($params['get_email'] == true)
	{
		$extraParams = "mail=1";
		if ($params['email_as_textarea'])
		{
			$output = <<<EOF
				<textarea id="$name" wrap="virtual" rows="$rows" cols="$cols" name="$name">$desc_value</textarea>
EOF;
		} else {
			$output = <<<EOF
				<input type="text" name="$name" id="$name" value="$id_value" size="$size"/>
EOF;
		}
	}
	else
	{
		$extraParams = "uid=1";
		$output = <<<EOF
			<input type="hidden" name="$name" id="$name" value="$id_value"/>
			<input type="text" name="$name_desc" id="$name_desc" value="$desc_value" readonly="true" size="$size"/>
EOF;
	}

	if ($params['hide_groups'] == true)
		$extraParams .= "&hidegroups=1";
	if (!is_null($params['organization']))
		$extraParams .= "&change_org=True&organization=" . $params['organization'];
	if (!is_null($params['entities']))
		$extraParams .= "&entities=" . $params['entities'];
	if ($params['hide_organizations'] == true)
		$extraParams .= "&hideOrganizations=1";
	if ($params['hide_sectors'] == true)
		$extraParams .= "&hideSectors=1";
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

	$image = Factory::getInstance('TemplateServer')->generateImageLink('add_user.png');

	$output .= <<<EOF
		<a alt="$title" title="$title" href="javascript:void(0)" onclick="openParticipantsWindow('$name', '$extraParams');"><img border="0" alt="" src="$image" /></a>
EOF;
	return $output;
}
?>
