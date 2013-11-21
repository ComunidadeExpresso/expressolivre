<?php
/**
 * Este plugin insere uma combo box e um select box m�ltiplo que permite ao usu�rio cadastrar v�rios �tens de uma lista.
 * O combo � utilizado para mostrar todos os �tens "cadastr�veis", que podem estar em formato de lista normal,
 * ou uma lista dividida em grupos (utilizando optGroup). Ao selecionar um item, deve-se clicar no bot�o "Adicionar" para que a sele��o seja copiada para o select box,
 * onde se encontram os itens a serem cadastrados. Ao adicionar um elemento,ele ser� desabilitado na combo para que n�o possa ser selecionado novamente.
 * Para remover um ou mais itens do select, basta selecion�-los e clicar no bot�o "Remover". Isso vai reabilit�-los na combo.
 * @author Anderson Tadayuki Saikawa
 * @author Everton Fl�vio Rufino Se�ra
 * @package Smarty
 * @subpackage wf_plugins
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @version 1.0
 * @param array $params Array de parametros
 * - nameTop: o nome que a combo ir� receber.
 * - nameBottom: o nome que o select ir� receber.
 * - arrayTop: lista dos �tens a serem carregados na combo.
 * - arrayBottom: lista dos �tens a serem carregados no select.
 * - onChangeTop: chamada da fun��o a ser executada no onChange da combo.
 * - onChangeBottom: chamada da fun��o a ser executada no onChange do select.
 * - size: tamanho do select box.
 * - sortEnableBottom: valor indicando se deve-se executar o sort no select box de baixo.
 * - diffEnable: valor indicando se deve-se executar o diff entre a combo e o select box.
 * - style: para definir os estilos do select box
 * @access public
 * @return string $output codigo que insere o plugin
 */
function smarty_function_wf_select_option_multiple($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('function','html_options');

	// This function build a "normal" select or an "option group" select
	function option_multiple_build($name, $array, $onChange, $extraParams, &$smarty)
	{
		if (!is_array($array))
		{
			return false;
		}

		$values = array_values($array);
		// If $array is a matrix, build an option group selectbox
		if (is_array($values[0]))
		{
			$result  = "<select name='$name' id='$name' onchange='enableButton(\"$name\");$onChange'>";
			$result .= "<option value='-1'></option>";

			foreach ($array as $key => $value)
			{
				$result .= "<optgroup label='$key'>";
				foreach ($value as $optKey => $optValue)
				{
					// text-indent style is used on Firefox. IE indents automatically.
					$result .= "<option value='$optKey' style='text-indent:1cm'>$optValue</option>";
				}
				$result .= '</optgroup>';
			}
			$result .= '</select>';
		}
		// If $array is NOT a matrix, build a normal selectbox
		else
		{
			// Insert a blank option with value=-1
			$array  = array(-1 => "") + $array;
			$result = smarty_function_html_options(array_merge(array(
													'name'     => $name,
													'id'       => $name,
													'onchange' => "enableButton('" . $name . "');$onChange",
													'options'  => $array), $extraParams),
												$smarty);
		}
		return $result;
	}

	$requiredParams  = array(
		'nameTop',
		'nameBottom'		);
	$defaultValues   = array(
		'size'             => 8,
		'style'            => "width:400px",
		'sortEnableBottom' => true,
		'diffEnable'       => true);
	$extractParams   = array(
		'nameTop',
		'nameBottom',
		'arrayTop',
		'arrayBottom',
		'onChangeTop',
		'onChangeBottom',
		'size',
		'diffEnable',
		'sortEnableBottom',
		'style');

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_select_option_multiple] missing required parameter(s): $required", E_USER_ERROR);

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
				<table valign=top>
				<tr>
					<td valign=top>
EOF;
	$output .=  option_multiple_build($nameTop, $arrayTop, $onChangeTop, $extraParams, $smarty);

	$output .= <<<EOF
			<!-- <input type="hidden" name="$name" id="$name" value="$id_value"/> -->
			&nbsp;&nbsp;<input class="form_botao" type="button" id="btn_$nameTop" value="Adicionar" disabled  onclick="addOption('$nameTop','$nameBottom','$sortEnableBottom')">
EOF;

	$output .= <<<EOF
					</td>
				</tr>
				<tr>
					<td valign=top>
EOF;

	$nameBottomLabel = ucwords( str_replace("_", " ", $nameBottom) );
	$output .= $nameBottomLabel;

	$output .= <<<EOF
				<br>
EOF;

	$output .= smarty_function_html_options(array_merge(array(
											'multiple' => 'true',
											'name'     => $nameBottom . "[]",
											'id'       => $nameBottom,
											'size'     => $size,
											'style'    => $style,
											'onChange' => "enableButton('" . $nameBottom . "');$onChangeBottom",
											'options'  => $arrayBottom),
											$extraParams),
										$smarty);

	$output .= <<<EOF
					</td>
				</tr>
				<tr>
					<td align=right>
						<input class="form_botao" type="button" id="btn_$nameBottom" value="Remover" disabled onclick="removeOptions('$nameBottom', '$nameTop')">
					</td>
				</tr>
				</table>
EOF;

	if($diffEnable)
	{
	$output .= <<<EOF
				<script>selectDiffMultiple('$nameBottom','$nameTop');</script>
EOF;
	}

	return $output;
}
?>
