<?php
//Code to be executed before an activity by agents

//retrieve agents list
$activity_agents =& $this->activity->getAgents();
//for all agents we create ui_agent object to handle agents executing tasks
foreach ($activity_agents as $agent)
{
	//create an empty temp ui_agent object
	$ui_agent =& createObject('workflow.ui_agent_'.$agent['wf_agent_type']);
	//build this object
	$ui_agent->load($agent['wf_agent_id']);
	//At runtime agents need to handle relations with the instance and the activity
	$ui_agent->runtime($instance, $activity);
	//store it in an array
	$this->agents[$agent['wf_agent_type']] = $ui_agent;
	//delete the temp object
	unset($ui_agent);
}

if (!($GLOBALS['workflow']['__leave_activity']))
{
  foreach ($this->agents as $agent_type => $ui_agent)
  {
    if (!($ui_agent->run_activity_pre()))
    {
      galaxia_show_error($ui_agent->get_error(),false);
    }

  }
}
else
{
  foreach ($this->agents as $agent_type => $ui_agent)
  {
    if (!($ui_agent->run_leaving_activity_pre()))
    {
      galaxia_show_error($ui_agent->get_error(), false);
    }
  }
}
?>
