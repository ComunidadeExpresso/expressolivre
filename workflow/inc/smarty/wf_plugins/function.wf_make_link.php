<?php
/**
 * Plugin para gerar link de retorno ou link gen�rico.
 * @package Smarty
 * @subpackage wf_plugins
 * @author Mauricio Luiz Viani
 * @param array $params Array de par�metros
 * - tab: (opcional) n�mero da aba da interface do usu�rio. Default = 1. Ignorado se for informado o par�metro url
 * - text: (opcional) texto sobre o qual ser� feito o link. Default = 'Voltar'
 * - url: (opcional) url completa para gerar o link. Se n�o informado ir� gerar um link par a interface do usu�rio. 
 * - img: (opcional) nome de um arquivo de imagem (com extens�o) sobre o qual ser� montado o link. Se informado, inibe o uso de par�metro text.
 * @param object &$smarty Inst�ncia do objeto smarty em uso 
 * @return string c�digo com o link 
 * @access public
 */
function smarty_function_wf_make_link($params, &$smarty)
{		
	$defaultValues = array(
		'tab' => '1',
		'text' => 'Voltar',
		'url' => '',
		'img' => '');
	$extractParams = array(
		'tab',
		'text',
		'url',
		'img');
		
	/* verifica se todos os par�metros obrigat�rios foram passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
			
	/* atribui valores default para os par�metros n�o passados */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];
		
	if ($img) {
		$text = "<img src='" . $smarty->get_template_vars('wf_resources_path') . "/" . $img . "' border='0'>";
	}

	if ($url) {
		$back_url = $url;
	} else {
		$back_url = ''; 
		$back_url = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/workflow/index.php?start_tab=' . $tab;
	}
	
	$output = '<a href="' . $back_url . '">'. $text . '</a>';
	return $output;
}
?>