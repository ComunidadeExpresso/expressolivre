<?php
/**
 * Retorna o link para uma determinada atividade
 * @param integer $activityId Id da atividade
 * @return string url para a atividade passada como parametro
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 * @access public
 * @author Alessandra Heil
 */
function wf_get_activity_link_by_id($activityId)
{
	/* load some variables */
	$urlBase = $_SERVER['SERVER_NAME'];

	$output = "http://".$urlBase."/index.php?menuaction=workflow.run_activity.go&activity_id=$activityId";

	return $output ;
}
?>
