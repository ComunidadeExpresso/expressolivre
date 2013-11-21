<?php
/**
 * Retorna o link para a próxima atividade
 * @return string
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public
 */
function wf_get_next_activity_link()
{
	/* load some variables */
	$instance =& $GLOBALS['workflow']['wf_runtime'];
	$currentActivity = $instance->activity_id;
	$instanceId = $instance->instance_id;
	$urlBase = $GLOBALS['phpgw_info']['server']['webserver_url'];

	/* determine the next activity */
	if (isset($instance->instance->changed['nextActivity'][$currentActivity]))
		$nextActivityId = $instance->instance->changed['nextActivity'][$currentActivity];
	else
		if (isset($instance->instance->nextActivity[$currentActivity]))
			$nextActivityId = $instance->instance->nextActivity[$currentActivity];
	
	if (isset($nextActivityId) && ($instanceId != 0))
		$output = "$urlBase/index.php?menuaction=workflow.run_activity.go&activity_id=$nextActivityId&iid=$instanceId";
	else
		$output = "$urlBase/workflow/index.php?start_tab=0";

	return $output;
}
?>
