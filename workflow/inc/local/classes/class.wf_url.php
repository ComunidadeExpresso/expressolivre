<?php
/**
 * importa classe do m�dulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.url.php');
/**
 * Classe que cont�m m�todos utilit�rios para a constru��o
 * e a manipula��o de URLs
 * @author Carlos Eduardo Nogueira Gon�alves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local
 */
class wf_url extends UrlUtils {
	/* desabilita m�todo */
	function anchor($url, $text, $statusBarText='', $cssClass='', $jsEvents=array(), $target='', $name='', $id='', $rel='', $accessKey='') {}		
}
?>