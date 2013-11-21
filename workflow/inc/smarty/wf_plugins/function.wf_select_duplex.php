<?php
/**
 * Este plugin insere dois select boxes de sele��es m�ltiplas que permitem ao usu�rio cadastrar v�rios �tens de uma lista dada. O primeiro select � utilizado para mostrar todos os �tens "cadastr�veis". Pode-se selecionar um ou mais �tens que s�o transferidos para o segundo select atrav�s do bot�o ">>". 
 * O mesmo pode ser feito do segundo select para o primeiro atrav�s do bot�o "" 
 * A id�ia � realizar o cadastro de todos os �tens contidos no segundo select ao final das opera��es.  
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros
 * - nameLeft: o nome que o select da esquerda ir� receber. 
 * - nameRight: o nome que o select da direita ir� receber. 
 * - arrayLeft: lista dos �tens a serem carregados no select da esquerda. 
 * - arrayRight: lista dos �tens a serem carregados no select da direita. 
 * - size: tamanho dos select boxes. 
 * - diffEnable: valor indicando se deve-se executar o diff entre os select boxes. 
 * - style: para definir os estilos dos select boxes
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string $output codigo que insere os select boxes. 
 * @access public 
 */
function smarty_function_wf_select_duplex($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');

	$requiredParams = array(
		'nameLeft',
		'nameRight');
	$defaultValues = array(
		'size'       => 8,
		'style'      => "width:200px",
		'nameLeft'   => "_disponiveis_" . rand(),
		'nameRight'  => "_cadastrados_" . rand(),
		'diffEnable' => true);
	$extractParams = array(
		'nameLeft',
		'nameRight',
		'arrayLeft',
		'arrayRight',
		'size',
		'diffEnable',
		'style');
	
	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_duplex] missing required parameter(s): $required", E_USER_ERROR);
	
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

	$output = <<<EOF
				<table><tr>
					<td valign=bottom>
EOF;

	$nameLeftLabel = ucwords( str_replace("_", " ", $nameLeft) );
	$output .= $nameLeftLabel;

	$output .= <<<EOF
				<br>
EOF;

	$output .= smarty_function_html_options(array_merge(array(
											'multiple' => 'true',
											'name'     => $nameLeft . "[]",
											'id'       => $nameLeft,
											'size'     => $size,
											'style'    => $style,
											'options'  => $arrayLeft), $extraParams),
										$smarty);
	$output .= <<<EOF
					</td>
					<td valign=middle>
EOF;

	$output .= <<<EOF
			<input type="hidden" name="$name" id="$name" value="$id_value"/>
			<input class="form_botao" type="button" value=">>" onclick="moveOptions('$nameLeft','$nameRight')">
			<br>
			<input class="form_botao" type="button" value="<<" onclick="moveOptions('$nameRight','$nameLeft')">
EOF;

	$output .= <<<EOF
					</td>
					<td valign=bottom>
EOF;

	$nameRightLabel = ucwords( str_replace("_", " ", $nameRight) );
	$output .= $nameRightLabel;

	$output .= <<<EOF
				<br>
EOF;

	$output .= smarty_function_html_options(array_merge(array(
											'multiple' => 'true',
											'name'     => $nameRight . "[]",
											'id'       => $nameRight,
											'size'     => $size,
											'style'    => $style,
											'options'  => $arrayRight), $extraParams),
										$smarty);

	$output .= <<<EOF
					</td>
				</tr></table>
EOF;

	if($diffEnable)
	{
	$output .= <<<EOF
				<script>selectDiff('$nameRight','$nameLeft');</script>					
EOF;
	}

	return $output;
}
?>
