<?php
/**
 * Plugin para gerar link de retorno ou link genérico.
 * @package Smarty
 * @subpackage wf_plugins
 * @author Mauricio Luiz Viani
 * @param array $params Array de parâmetros
 * - tab: (opcional) número da aba da interface do usuário. Default = 1. Ignorado se for informado o parâmetro url
 * - text: (opcional) texto sobre o qual será feito o link. Default = 'Voltar'
 * - url: (opcional) url completa para gerar o link. Se não informado irá gerar um link par a interface do usuário. 
 * - img: (opcional) nome de um arquivo de imagem (com extensão) sobre o qual será montado o link. Se informado, inibe o uso de parâmetro text.
 * @param object &$smarty Instância do objeto smarty em uso 
 * @return string código com o link 
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
		
	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
			
	/* atribui valores default para os parâmetros não passados */
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