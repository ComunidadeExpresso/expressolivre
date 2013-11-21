<?php
	/*******************************************************
	 * Im - JabberIt
	 * Colaboradores : Alexandre Correia / Rodrigo Souza
	 *
	 * ****************************************************/


	$GLOBALS['phpgw_info']['flags'] = array(
						'currentapp' => 'jabberit_messenger',
						'nonavbar'   => true,
						'noheader'   => false,
						'jabberit_version' => '0.7.7',
						);

	include("../header.inc.php");

	$jabberit_version = $GLOBALS['phpgw_info']['flags']['jabberit_version'];

	$template = CreateObject('phpgwapi.Template', PHPGW_APP_TPL);
	$template->set_file(Array('jabberit_messenger' => 'index.tpl'));
	$template->set_var('template_default', $GLOBALS['phpgw_info']['user']['preferences']['common']['template_set']);
	$template->set_var('template_set', 'default');	
	$template->set_block('jabberit_messenger','index');
	$template->pfp('out','index');


?>
