<?php
/**
 * Este plugin insere um input que mostra os valores que podem ser selecionados enquanto o usuário digita.
 * Os valores são preenchidos previamente através de uma requisição ajax: a classe e método que será invocado é passado por parêmetro no momento em que o desenvolvedor utiliza o componente.
 *
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - name: o nome que o componente irá receber.
 * - ajaxClass: classe ajax que será utilizada para fazer a consulta.
 * - ajaxMethod: método da classe que será invocado para preencher a lista de opções.
 * - methodParam: Parâmetro para o método que será invocado para preencher a lista de opções.
 * - idValue: (opcional) o id de um item previamente selecionado (não é visível para o usuário).
 * - textValue: (opcional) o texto de um item inicialmente selecionado (é visível para o usuário).
 * - minLength: (opcional) número mínimo de caracteres necessários para que as opções apareçam na lista.
 * - style: (opcional) estilo para o campo input
 * @param object &$smarty Instância do objeto smarty em uso
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

	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_autocomplete_input] missing required parameter(s): $required", E_USER_ERROR);

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

	$name_input = 'input' . $name;
	$name_list	= 'list' . $name;
	$name_response = 'response' . $name;

	/* se parâmetro for um array "joga" para o javascript como um objeto JSON */
	if (is_array($methodParam)){
		$methodParam = json_encode($methodParam);
	} else {
		$sep = "'"; // se não for um objeto passa parâmetro entre aspas
	}

	/* se o componente for do tipo padrão (POPULATE_ON_LOAD) */
	if ($mode == 'POPULATE_ON_LOAD'){
		// Se o usuário necessitar utilizar os parâmetros onfocus ou onblur, concatena as chamadas
		// passadas na declaração do componente com as necessárias para o funcionamento do plugin.
		// Outros parâmetros são apenas repassados para o campo input.
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
	/* se o componente for do tipo REPOPULATE_ON_CHANGE, deverá fazer chamada ajax para toda entrada nova (verificada na ação onkeyup) */
	elseif ($mode == 'REPOPULATE_ON_CHANGE'){
		// Se o usuário necessitar utilizar os parâmetros onblur ou onkeyup, concatena as chamadas
		// passadas na declaração do componente com as necessárias para o funcionamento do plugin.
		// Outros parâmetros são apenas repassados para o campo input.
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

	/* Cria um objeto JSON com os parâmetros opcionais utilizados pelo javascript */
	$extraParams = array ('idValue'		=> $idValue
						, 'textValue'	=> $textValue
						, 'minLength'	=> $minLength
	);
	$extraParams = json_encode($extraParams);

	/* Cria todos os elementos HTML necessários para o componente */
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
