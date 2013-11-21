<?php
//Code to be executed before the end activity

$this->runtime->StartRun();

//tests for access rights and lock some rows--------------------------
if (!($this->runtime->checkUserRun($GLOBALS['user'])))
{
  return $this->runtime->fail(lang('You have not permission to execute this activity'), true);
}
if (!($GLOBALS['workflow']['__leave_activity']))
{
  // Set the current user for this activity
    $this->runtime->setActivityUser();
}

$this->runtime->EndStartRun();

?>