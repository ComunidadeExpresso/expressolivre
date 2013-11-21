<?php
   /*****************************************************
	* Applet - JabberIt
 	* Colaboradores : Alexandre Correia / Rodrigo Souza
	*
	***************************************************/
	
	if( file_exists("../header.session.inc.php"))
		require_once("../header.session.inc.php");
	else if(file_exists("../../header.session.inc.php"))
		require_once("../../header.session.inc.php");
	
	$currentApp = ($_SESSION['phpgw_info']['expresso']['currentapp']) ? $_SESSION['phpgw_info']['expresso']['currentapp'] : "jabberit_messenger";

	$GLOBALS['phpgw_info']['flags'] = array(
							'currentapp' => $currentApp,
							'nonavbar'   => true,
							'noheader'   => true
						);
	require_once("../header.inc.php");
	
	require_once(dirname(__FILE__)."/inc/login.php");

?>
