<?php
/**
 * importa classe do módulo
 */
require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.utils.type.php');
/**
 * Classe que contém funções utilitárias para verificação de tipagem
 * de dados e conversão (cast) entre tipos primitivos de dados no PHP
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local  
 */
class wf_type extends TypeUtils {}
?>