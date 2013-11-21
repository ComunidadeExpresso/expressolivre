<?php
/**
 * importa classe do módulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.string.php');

/**
 * Classe que contém funções utilitárias para manipulação
 * de strings. As funções extendem a funcionalidade já oferecida
 * pelo PHP (agrupando e tornando mais prática a utilização)
 * e incluem novas ferramentas não implementadas
 * de dados e conversão (cast) entre tipos primitivos de dados no PHP
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 */
class wf_string extends StringUtils {}
?>