<?php
//Code shared by all the activities (pre)
if (isset($_REQUEST['__Cancel']))
{
  // user want to leave this activity,
  $GLOBALS['workflow']['__leave_activity']=true;
}
          
?>
