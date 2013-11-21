<?php
/**
 * Este plugin insere um input que mostra os valores que podem ser selecionados enquanto o usu�rio digita.
 * Os valores s�o preenchidos previamente atrav�s de uma requisi��o ajax: a classe e m�todo que ser� invocado � passado por par�metro no momento em que o desenvolvedor utiliza o componente.
 *
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que o componente ir� receber.
 * - ajaxClass: classe ajax que ser� utilizada para fazer a consulta.
 * - ajaxMethod: m�todo da classe que ser� invocado para preencher a lista de op��es.
 * - methodParam: Par�metro para o m�todo que ser� invocado para preencher a lista de op��es.
 * - idValue: (opcional) o id de um item previamente selecionado (n�o � vis�vel para o usu�rio).
 * - textValue: (opcional) o texto de um item inicialmente selecionado (� vis�vel para o usu�rio).
 * - minLength: (opcional) n�mero m�nimo de caracteres necess�rios para que as op��es apare�am na lista.
 * - style: (opcional) estilo para o campo input
 * @param object &$smarty Inst�ncia do objeto smarty em uso
 * @return string $output codigo que insere os select boxes.
 * @access public
 */
function smarty_function_wf_autocomplete_input($params, &$smarty)
{
	$requiredParams = array(
		'name',
		'ajaxClass',
		'ajaxMethod'
	);
	$defaultValues = array(
		'minLength'	=> 1,
		'style'		=> "width: 200px",
		'mode'		=> "POPULATE_ON_LOAD"
	);
	$extractParams = array(
		'name',
		'ajaxClass',
		'ajaxMethod',
		'methodParam',
		'minLength',
		'idValue',
		'textValue',
		'style',
		'mode'
	);

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_autocomplete_input] missing required parameter(s): $required", E_USER_ERROR);

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

	$name_input = 'input' . $name;
	$name_list	= 'list' . $name;
	$name_response = 'response' . $name;

	/* se par�metro for um array "joga" para o javascript como um objeto JSON */
	if (is_array($methodParam)){
		$methodParam = json_encode($methodParam);
	} else {
		$sep = "'"; // se n�o for um objeto passa par�metro entre aspas
	}

	/* se o componente for do tipo padr�o (POPULATE_ON_LOAD) */
	if ($mode == 'POPULATE_ON_LOAD'){
		// Se o usu�rio necessitar utilizar os par�metros onfocus ou onblur, concatena as chamadas
		// passadas na declara��o do componente com as necess�rias para o funcionamento do plugin.
		// Outros par�metros s�o apenas repassados para o campo input.
		$onfocus = "checkDataLoaded('$name');" . $extraParams['onfocus'];
		unset($extraParams['onfocus']);
		$onblur = "setTimeout('selectAutocompleteElement(\'$name\')', 500);" . $extraParams['onblur'];
		unset($extraParams['onblur']);
		$extra_input = "";
		foreach($extraParams AS $key => $value){
			$extra_input .= " $key=$value ";
		}
		$inputElement = <<<EOF
		<input id="$name_input" name="$name_input" type="text" value="$textValue" style="$style" onfocus="$onfocus" onblur="$onblur" $extra_input/>
EOF;
	}
	/* se o componente for do tipo REPOPULATE_ON_CHANGE, dever� fazer chamada ajax para toda entrada nova (verificada na a��o onkeyup) */
	elseif ($mode == 'REPOPULATE_ON_CHANGE'){
		// Se o usu�rio necessitar utilizar os par�metros onblur ou onkeyup, concatena as chamadas
		// passadas na declara��o do componente com as necess�rias para o funcionamento do plugin.
		// Outros par�metros s�o apenas repassados para o campo input.
		$onblur = "setTimeout('selectAutocompleteElement(\'$name\')', 500);" . $extraParams['onblur'];
		unset($extraParams['onblur']);
		$onkeyup = "updateCacheRequestsTimeout('$name', '$ajaxClass', '$ajaxMethod', this.value, '$mode');" . $extraParams['onkeyup'];
		unset($extraParams['onkeyup']);
		$extra_input = "";
		foreach($extraParams AS $key => $value){
			$extra_input .= " $key=$value ";
		}
		$inputElement = <<<EOF
		<input id="$name_input" name="$name_input" type="text" value="$textValue" style="$style" onblur="$onblur" onkeyup="$onkeyup" $extra_input/>
EOF;
	}

	/* Cria um objeto JSON com os par�metros opcionais utilizados pelo javascript */
	$extraParams = array ('idValue'		=> $idValue
						, 'textValue'	=> $textValue
						, 'minLength'	=> $minLength
	);
	$extraParams = json_encode($extraParams);

	/* Cria todos os elementos HTML necess�rios para o componente */
	$output = <<<EOF
	<input id="$name" name="$name" type="hidden"/>
	$inputElement
	<span id="$name_response"></span>
	<div id="$name_list" class="autocomplete" style="display: none;"></div>
	<script>autocompleteSelect('$name', '$ajaxClass', '$ajaxMethod', $sep$methodParam$sep, '$mode', $extraParams);</script>
EOF;
	return $output;
}
?>
