#!/usr/bin/php -q
<?php
	$path_to_egroupware = realpath(dirname(__FILE__).'/../..');	//  need to be adapted if this script is moved somewhere else

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'login',
		'noapi'      => True		// this stops header.inc.php to include phpgwapi/inc/function.inc.php
	);
	if (!is_readable($path_to_egroupware.'/header.inc.php'))
	{
		echo $msg = "Could not find '$path_to_egroupware/header.inc.php', exiting !!!\n";
		exit(1);
	}

	include($path_to_egroupware.'/header.inc.php');
	unset($GLOBALS['phpgw_info']['flags']['noapi']);

	include(PHPGW_API_INC.'/functions.inc.php');

	if ($GLOBALS['phpgw_info']['server']['max_access_log_age'])
		$max_age = time() - ($GLOBALS['phpgw_info']['server']['max_access_log_age'] * 24 * 60 * 60);
	else
		$max_age = time() - (30 * 24 * 60 * 60);

	$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_access_log WHERE li < $max_age");

	$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_log *");

	$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_log_msg *");

	exit;
?>
