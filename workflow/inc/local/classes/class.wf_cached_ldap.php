<?php
require_once PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'class.CachedLDAP.inc.php';

/**
* Gera um cache do LDAP (em banco de dados) para não perder informações no caso de exclusão de um funcionário
* @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
* @subpackage local
*/
class wf_cached_ldap extends CachedLDAP
{
}
?>
