<?php
/**
 * Insere o componente que permite a sele��o de itens gen�ricos. 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros. O valor padr�o para todos os par�metros n�o obrigat�rios, � uma string vazia. 
 * - name: (obrigat�rio) o nome que ir� armazenar o id do item selecionado; 
 * - container_number: (obrigat�rio) o n�mero do container onde os itens est�o armazenados. 
 * - id_value: (opcional) o id de um item previamente selecionado (n�o � vis�vel para o usu�rio). 
 * - desc_value: (opcional) o texto de um item inicialmente selecionado (� vis�vel para o usu�rio). 
 * - title: (opcional) o t�tulo da tooltip quando o usu�rio passa o mouse sobre o bot�o de adicionar.   
 * @param object &$smarty Inst�ncia do objeto smarty em uso  
 * @return string $output c�digo que insere o componente.
 * @access public  
 */
function smarty_function_wf_generic_select($params, &$smarty)
{
	$requiredParams = array(
		'name');
	$defaultValues = array(
		'id_value' => '',
		'desc_value' => '',
		'title' => '',
		'container_number' => 0);
	$extractParams = array(
		'name',
		'id_value',
		'desc_value',
		'title',
		'container_number');
	
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
	$digest = md5($_SERVER['REQUEST_URI']);
	$extraParams = "container_number=$container_number&digest=$digest";

	$output = <<<EOF
		<input type="hidden" name="$name" id="$name" value="$id_value"/>
		<input type="text" name="$name_desc" id="$name_desc" value="$desc_value" readonly="true"/>
EOF;

	$templateServer = &Factory::getInstance('TemplateServer');
	$imageAdd = $templateServer->generateImageLink('add.png');
	$imageRemove = $templateServer->generateImageLink('close.png');
$output .= <<<EOF
	<a alt="$title" title="$title" href="javascript:void(0)" onclick="openGenericList('$name', '$extraParams');"><img border="0" alt="" src="$imageAdd" /></a>
	<a alt="$title" title="$title" href="javascript:void(0)" onclick="genericListRemove('$name', '$name_desc');"><img border="0" alt="" src="$imageRemove" /></a>
EOF;
	return $output;
}
?>
