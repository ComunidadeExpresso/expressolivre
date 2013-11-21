<?php
/**
 * importa classe do módulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.php');
/**
 * Classe utilitária
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 */
class wf_util extends Utils {

	function wf_util()
	{
		return true;
	}

	/* desabilita métodos */
	function includeFile($filePath, $return=FALSE) {}
	function scriptName() {}
	function serverName() {}
	function get($key) {}
	function anchor($url, $text, $statusBarText='', $cssClass='', $jsEvents=array(), $target='', $name='', $id='', $rel='', $accessKey='') {}	

	/**
	* Formata um array em uma linha do formato CSV
	*
	* @return string Linha no formato CSV
	* @access public
	*/
	function createCSVLine($item, $separator, $quotes = false)
	{
		if(is_array($item)){
			if ($quotes){
				foreach ($item as $key => $value){
					$item[$key] = '"' . str_replace('"', '""', $value) . '"';
				}
			}
			return implode($separator, $item) . "\n";
		}
		return false;
	}

	/**
	* Cria um arquivo CSV baseado em um array
	*
	* @return string Linha no formato CSV
	* @access public
	*/
	function createCSVFile($data, $separator, $quotes = false)
	{
		if(!is_array($data))
			return false;

		foreach($data as $item){
			$output .= $this->createCSVLine($item, $separator, $quotes);
		}
		return $output;
	}
}
?>
