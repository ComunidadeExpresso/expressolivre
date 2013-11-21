<?php
/**
 * importa classe do m�dulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.string.php');

/**
 * Classe que cont�m fun��es utilit�rias para manipula��o
 * de strings. As fun��es extendem a funcionalidade j� oferecida
 * pelo PHP (agrupando e tornando mais pr�tica a utiliza��o)
 * e incluem novas ferramentas n�o implementadas
 * de dados e convers�o (cast) entre tipos primitivos de dados no PHP
 * @author Carlos Eduardo Nogueira Gon�alves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 */
class wf_string extends StringUtils {}
?>