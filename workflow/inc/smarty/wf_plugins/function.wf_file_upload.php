<?php
/**
 * Insere o componente que permite o upload de um ou mais arquivos. 
 * @package Smarty
 * @subpackage wf_plugins
 * @param array $params Array de parametros (vazio)
 *  - name: (obrigatório) o nome do componente (que será do tipo "file") que irá conter os arquivos; 
 *  - max: (opcional) número máximo de arquivos que podem ser enviados simultaneamente. Se nenhum parâmetro for passado, pode-se enviar quantos arquivos forem necessários.
 * @param object &$smarty Instância do objeto smarty em uso 
 * @return string $output codigo que insere o componente 
 * @access public
 */
function smarty_function_wf_file_upload($params, &$smarty)
{
	$requiredParams = array(
		'name');
	$defaultValues = array(
		'max' => -1);
	$extractParams = array(
		'name',
		'max');
	
	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($requiredParams as $required)
		if (!array_key_exists($required, $params) || (empty($params[$required])))
			$smarty->trigger_error("[wf_file_upload] missing required parameter(s): $required", E_USER_ERROR);
	
	/* atribui valores default para os parâmetros não passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;
	
	/* extrai alguns parâmetros da matriz de parâmetros */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];
	
	$divName = $name . "_div";
	$id = $name;
	$name .= '[]';

	$output = <<<EOF
		<input id="$id" type="file" name="$name" />
		<div id="$divName"></div>
		<script>
	    var multi_selector_$id = new MultiSelector(document.getElementById('$divName'), $max);
	    multi_selector_$id.addElement(document.getElementById('$id'), '$name');
		</script>
EOF;
	
	return $output;
}
?>
