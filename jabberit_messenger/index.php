<?php
	/*******************************************************
	 * Im - JabberIt
	 * Colaboradores : Alexandre Correia / Rodrigo Souza
	 *
	 * ****************************************************/

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'jabberit_messenger';

	include("../header.inc.php");

	$jabberit_version = $GLOBALS['phpgw_info']['flags']['jabberit_version'];
	
	$template = CreateObject('phpgwapi.Template', PHPGW_APP_TPL);

	// Verifica qual será o módulo a ser carregado.
	$flag = false;
	
	$groupsJmessenger = unserialize( $GLOBALS['phpgw_info']['server']['groups_jmessenger_jabberit'] );
	
	if( is_array($groupsJmessenger) )
	{
	
		foreach( $groupsJmessenger as $tmp )
		{
			$_explode = explode( ":", $tmp );
			$groups[] = $_explode[1];
		}
	
		foreach( $GLOBALS['phpgw']->accounts->membership() as $idx => $group )
		{
			if( array_search($group['account_name'], $groups) !== FALSE )
				$flag = true;
		}
	}
	
	if( $flag )
		$template->set_file(Array('jabberit_messenger' => 'indexIM.tpl'));
	else
		$template->set_file(Array('jabberit_messenger' => 'indexIM_JAVA.tpl'));

	$template->set_block('jabberit_messenger','index');
	$template->set_var( 'url', $GLOBALS['phpgw']->link( '/jabberit_messenger/' ) );
	$template->pfp('out','index');

	$GLOBALS['phpgw']->common->phpgw_footer();

?>
