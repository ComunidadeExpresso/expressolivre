<?php
//Code to be executed after a start activity
// we save name and others only when the instance
// has been completed
if ($instance->getActivityCompleted())
{
  if(isset($_REQUEST['wf_name']))
  {
    $instance->setName($_REQUEST['wf_name']);
  }
  if(isset($_REQUEST['wf_priority']))
  {
    $instance->setPriority((int)$_REQUEST['wf_priority']);
  }
  if(isset($_REQUEST['wf_set_next_user']))
  {
    $instance->setNextUser((int)$_REQUEST['wf_set_next_user']);
  }
  if(isset($_REQUEST['wf_set_owner']))
  {
    $instance->setOwner((int)$_REQUEST['wf_set_owner']);
  }
  if(isset($_REQUEST['wf_category']))
  {
    $instance->setCategory((int)$_REQUEST['wf_category']);
  }

}
?>
