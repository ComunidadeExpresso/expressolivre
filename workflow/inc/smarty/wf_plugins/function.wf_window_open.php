<?php
/**
 * Abre uma nova janela de browser
 * @package Smarty
 * @subpackage wf_plugins
 * @author Mauricio Luiz Viani
 * @param array $params Array de parametros
 * - url: (obrigat�rio) Endere�o completo de uma p�gina a ser aberta na nova janela 
 * - name: (opcional) Nome do objeto janela a ser criado. Default 'win'
 * - width: (opcional) Largura, em pixels, da janela. Default 450.
 * - height: (opcional) Comprimento, em pixels, da janela. Default 550
 * - features: (opcional) Sequ�ncia de par�metros de configura��o da janela. Default: "scrollbars = yes, menubar=yes"
 * - text (opcional) Texto sobre o qual ser� montado o link de abertura. Default 'Abrir'
 * - img: (opcional) Nome de um arquivo de imagem (com extens�o) sobre o qual ser� montado o link. Se informado, inibe o uso de par�metro text
 * - button: (opcional) Indica se o link para abrir a nova janela deve ser um bot�o. Default false. Se informado inibe o par�metro img.   
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string $output c�digo
 * @access public
 */
function smarty_function_wf_window_open($params, &$smarty)
{		
	$requiredParams = array(
		'url');
	$defaultValues = array(
		'name' => 'win',
		'width' => '450',
		'height' => '550',
		'position' => 'right',
		'features' => "toolbar=no, scrollbars=yes, menubar=yes",
		'text' => 'Abrir',
		'img' => '',
		'button' => false);
	$extractParams = array(
		'url',
		'name',
		'width',
		'height',
		'position',
		'features',
		'text',
		'img',
		'button');

	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_window_open] missing required parameter(s): $required", E_USER_ERROR);
		
	/* atribui valores default para os par�metros n�o passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
			
	/* extrai alguns par�metros da matriz de par�metros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];

	/* par�metros extras s�o "acumulados" em uma �nica matriz */
	$extraParams = array();
	foreach ($params as $key => $value)
		if (!in_array($key, $extractParams))
			$extraParams[] = $key . ' = "' . $value . '"';
		
	$click_command = "wf_open_window('$url', '$name', '$width', '$height', '$position', '$features');";
	
	if ($button) {
		$output = "<input type=\"button\" onclick=\"$click_command\" value=\"$text\" name=\"$text\" " . implode(' ', $extraParams) . "/>";
	}
	else {
			if ($img) {
				$text = '<img src="' . $smarty->get_template_vars('wf_resources_path') . '/' . $img . '" border="0">';
			}
			$output = "<a href=\"javascript:void(0)\" onclick=\"$click_command\" " . implode(' ', $extraParams) . ">$text</a>";
	}
		
	return $output;
}
?>
