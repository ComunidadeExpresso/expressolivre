<?php

	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'admin';
	include('../header.inc.php');
	$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . lang('CAs/CRLs Configuration ');

        if (file_exists('security.php'))
	  {
	       include('security.php');
	  }
	else
	  {
	       echo '<div><h4>' . lang('Administration data of AC\'s and RCL\'s not found') . '.</h4></div>';
	  }
?>
