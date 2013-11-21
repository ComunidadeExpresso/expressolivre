<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  *  	- JETI - http://jeti-im.org/										  *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	define('PHPGW_API_INC','../../phpgwapi/inc');
	require_once(PHPGW_API_INC . '/class.Template.inc.php');

	if(isset($_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit']))
    {
		// Path Server
		$path = $_SESSION['phpgw_info']['jabberit_messenger']['webserver_url'];
		$SERVER_EXPRESSO = $_SERVER['HTTP_HOST'] . $path;

		/** 
		 ******  Type Protocol http / https **********************************************************
		 *	Esta variável é carregada no header.inc.php, onde é possível definir
		 * 	se será com http ou https. Verifique em seu arquivo header.inc.php como está configurada
		 *	a variável $GLOBALS['phpgw_info']['server']['use_https']. 
		 *	Abaixo segue esquema :
		 *	Expresso 0 = Sem https
		 *	Expresso 1 = Com https apenas no login
		 * 	Expresso 2 = Completo
		 *********************************************************************************************
		 **/

		$PROTOCOL = trim("http");
		if( $_SESSION['phpgw_info']['jabberit_messenger']['use_https'] === 2 )
			$PROTOCOL = trim("https");

		// Define Attribute Ldap
		$attribute = "uid";
		if ( file_exists('inc/attributeLdap.php') )
		{
			require_once('attributeLdap.php');
			$attribute = trim($attributeTypeName);
		}

		// Uid user
		if( $attribute === "uid" )
		{
			$uid = $_SESSION['phpgw_info']['jabberit_messenger']['user_jabber'];
		}
		else
		{
			$uid = "DEFINA AQUI A VARIAVEL DE SESSÃO QUE CONTEM O ATRIBUTO DE AUTENTICAÇÂO";
		}
		
		// FirstName
		$CnName = explode(" ",$_SESSION['phpgw_info']['jabberit_messenger']['fullname']);
		
		//Enable/Disable VoIP Service -> Voip Server Config
		$voip_enabled = false;
		$voip_groups = array();	
		if( $GLOBALS['phpgw_info']['server']['voip_groups'] )
		{
			$emailVoip = false;
			foreach(explode(",",$GLOBALS['phpgw_info']['server']['voip_groups']) as $i => $voip_group)
			{
				$a_voip = explode(";",$voip_group);			
				$voip_groups[] = $a_voip[1];
			}
			foreach($GLOBALS['phpgw']->accounts->membership() as $idx => $group){			
				if(array_search($group['account_name'],$voip_groups) !== FALSE)
				{		 
					$voip_enabled = true;
					$emailVoip = $GLOBALS['phpgw_info']['server']['voip_email_redirect'];
				}
			}
		}

		// Load Applet ( Java )
		$javaFiles = $path . "applet.jar,";
		$javaPlugins = "";
				
		// Enable Plugins Java;
		$pluginsJava[] = "xhtml.jar";
		$pluginsJava[] = "filetransfer.jar";
		
		if( $voip_enabled )
			$pluginsJava[] = "callVoip.jar";

        $pluginsJava_count = count($pluginsJava);
		for( $i = 0; $i < $pluginsJava_count; ++$i )
		{
			$javaFiles	 .= $path . "plugins/" . $pluginsJava[$i] . ",";
			$javaPlugins .= substr($pluginsJava[$i], 0, strpos($pluginsJava[$i],".")).","; 
		}
		
		$javaPlugins = trim(substr($javaPlugins, 0, strlen($javaPlugins)-1));

		// Code Base Java;
		$codeBase = 'nu.fw.jeti.applet.Jeti.class';

		// Lang Expresso
		$lang = explode("-", $_SESSION['phpgw_info']['jabberit_messenger']['applet_lang']); 
		$country = strtoupper($lang[1]);
		$language = $lang[0];
		
		// Porta/(SSL)? 		
		$conn_SSL = "false";
		$port_jabber = "5222";
		
		if( $_SESSION['phpgw_info']['jabberit_messenger']['port_1_jabberit'] === "true" )
		{	
			$conn_SSL = "true";
			$port_jabber = "5223";
		}
				
		if( trim($_SESSION['phpgw_info']['jabberit_messenger']['port_2_jabberit']) )
		{		
			$port_jabber = $_SESSION['phpgw_info']['jabberit_messenger']['port_2_jabberit'];
		}
		
		// Crypt Password
		if( function_exists('mcrypt_module_open') )		
		{
			require_once("crypto.php");
			$CRYPT_JAVA = "DefaultJava1234@";

			// Load Template;
			$template = new Template('templates/default');
			$template->set_var("path", $path);
			$template->set_var("java_files", $javaFiles);
			$template->set_var("value_cnname", encrypt($CnName[0],$CRYPT_JAVA));
			$template->set_var("value_codeBase", $codeBase);
			$template->set_var("value_company", encrypt($_SESSION['phpgw_info']['jabberit_messenger']['name_company'],$CRYPT_JAVA));
			$template->set_var("value_country", encrypt($country,$CRYPT_JAVA));
			$template->set_var("value_expresso", encrypt($SERVER_EXPRESSO,$CRYPT_JAVA));		
			$template->set_var("value_host", encrypt($_SESSION['phpgw_info']['jabberit_messenger']['ip_server_jabberit'], $CRYPT_JAVA));
			$template->set_var("value_javaPlugins", $javaPlugins);
			$template->set_var("value_language", encrypt($language,$CRYPT_JAVA));			
			$template->set_var("value_password", encrypt($_SESSION['phpgw_info']['jabberit_messenger']['passwd'],$CRYPT_JAVA));
			$template->set_var("value_port", encrypt($port_jabber,$CRYPT_JAVA));
			$template->set_var("value_resource", encrypt($_SESSION['phpgw_info']['jabberit_messenger']['resource_jabberit'],$CRYPT_JAVA));
			$template->set_var("value_server", encrypt($_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit'],$CRYPT_JAVA));
			$template->set_var("value_ssl", encrypt($conn_SSL,$CRYPT_JAVA));
			$template->set_var("value_use_https", encrypt($PROTOCOL,$CRYPT_JAVA));
			$template->set_var("value_user", encrypt($uid,$CRYPT_JAVA));
			$template->set_var("value_mc", "true");
		}
		else
		{
			// Load Template;
			$template = new Template('templates/default');
			$template->set_var("path", $path);
			$template->set_var("java_files", $javaFiles);
			$template->set_var("value_cnname", $CnName[0] );
			$template->set_var("value_codeBase", $codeBase);
			$template->set_var("value_company", $_SESSION['phpgw_info']['jabberit_messenger']['name_company'] );
			$template->set_var("value_country", $country );
			$template->set_var("value_expresso", $SERVER_EXPRESSO );		
			$template->set_var("value_host", $_SESSION['phpgw_info']['jabberit_messenger']['ip_server_jabberit'] );
			$template->set_var("value_javaPlugins", $javaPlugins);
			$template->set_var("value_language", $language );			
			$template->set_var("value_password", $_SESSION['phpgw_info']['jabberit_messenger']['passwd'] );
			$template->set_var("value_port", $port_jabber );
			$template->set_var("value_resource", $_SESSION['phpgw_info']['jabberit_messenger']['resource_jabberit'] );
			$template->set_var("value_server", $_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit'] );
			$template->set_var("value_ssl", $conn_SSL );
			$template->set_var("value_use_https", $PROTOCOL );
			$template->set_var("value_user", $uid );
			$template->set_var("value_mc", "false");
		}

		if( strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") )
			$template->set_file(Array('jabberit_messenger' => 'jabberIM_IE.tpl'));
		else
			$template->set_file(Array('jabberit_messenger' => 'jabberIM.tpl'));
			
		$template->set_block('jabberit_messenger','index');
		$template->pfp('out','index');
    }
?>