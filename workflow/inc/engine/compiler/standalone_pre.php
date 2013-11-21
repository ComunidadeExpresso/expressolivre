<?php
//Code to be executed before a standalone activity

$this->runtime->StartRun();

//tests for access rights and lock some rows--------------------------
if (!($this->runtime->checkUserRun($GLOBALS['user'])))
{
  return $this->runtime->fail(lang('You have not permission to execute this activity'), true);
}

$this->runtime->EndStartRun();
?>
