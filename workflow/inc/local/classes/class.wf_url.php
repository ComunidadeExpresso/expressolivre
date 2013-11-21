<?php
/**
 * importa classe do módulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.url.php');
/**
 * Classe que contém métodos utilitários para a construção
 * e a manipulação de URLs
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local
 */
class wf_url extends UrlUtils {
	/* desabilita método */
	function anchor($url, $text, $statusBarText='', $cssClass='', $jsEvents=array(), $target='', $name='', $id='', $rel='', $accessKey='') {}		
}
?>