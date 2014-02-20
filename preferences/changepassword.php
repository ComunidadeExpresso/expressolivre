<?php
	/**************************************************************************\
	* phpGroupWare - preferences                                               *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

function grava_senha_criptografada_com_certificado_no_ldap($aux_uid,$aux_senha)
   {
	require_once(PHPGW_SERVER_ROOT . '/security/classes/CertificadoB.php');

        if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
		(!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
		(!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
            {
	        $ldap_context = $GLOBALS['phpgw_info']['server']['ldap_context'];
                $ldap_servidor = $GLOBALS['phpgw_info']['server']['ldap_master_host'];
                $ldap_dn = $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'];
                $ldap_passwd = $GLOBALS['phpgw_info']['server']['ldap_master_root_pw'];
            }
        else
            {
                $ldap_context = $GLOBALS['phpgw_info']['server']['ldap_context'];
                $ldap_servidor = $GLOBALS['phpgw_info']['server']['ldap_host'];
                $ldap_dn = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                $ldap_passwd = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
            }

	$cc=ldap_connect($ldap_servidor);

        if($GLOBALS['phpgw_info']['server']['ldap_version3'])
            {
		ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);
            }

	$sr=ldap_bind($cc,$ldap_dn,$ldap_passwd);
	$filtro = 'uidNumber='.$aux_uid;
	$sr=ldap_search($cc, $ldap_context,$filtro);
	$info = ldap_get_entries($cc, $sr);

	if($info["count"]!=1)
            {
		ldap_close($cc);
		return false;
	    }

	if(!$info[0]["usercertificate"][0])
	    {
		ldap_close($cc);
		return false;
	    }

	$a = new certificadoB();
	$R = $a->encriptar_senha($aux_senha,$info[0]["usercertificate"][0]);

	if(!$R)
	    {
		return false;
	    }

	$user_info = array();
	$aux1 = $info[0]["dn"];
	$user_info['cryptpassword'] = $R;
	ldap_modify($cc,$aux1,$user_info);

	ldap_close($cc);

	return true;
   }


	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'preferences'
	);

	include('../header.inc.php');

	$a_passwd   = $_POST['a_passwd'];
	$n_passwd   = $_POST['n_passwd'];
	$n_passwd_2 = $_POST['n_passwd_2'];

	if(!$GLOBALS['phpgw']->acl->check('changepassword', 1) || $_POST['cancel'])
	{
		if(($GLOBALS['phpgw_info']['user']['lastpasswd_change'] == '0') && (!$_POST['cancel']))
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/login.php','cd=6'));

		if ($GLOBALS['phpgw_info']['server']['use_https'] == 1)
			Header('Location: http://' . $_SERVER['HTTP_HOST'] . $GLOBALS['phpgw_info']['server']['webserver_url'] . '/preferences/index.php');
		else
			$GLOBALS['phpgw']->redirect_link('/preferences/index.php');

		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'form' => 'changepassword.tpl'
	));
	$GLOBALS['phpgw']->template->set_var('lang_enter_actual_password',lang('Enter your actual password'));
	$GLOBALS['phpgw']->template->set_var('lang_enter_password',lang('Enter your new password'));
	$GLOBALS['phpgw']->template->set_var('lang_reenter_password',lang('Re-enter your password'));
	$GLOBALS['phpgw']->template->set_var('lang_change',lang('Change'));
	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
    	$GLOBALS['phpgw']->template->set_var('cancel_action' , $GLOBALS['phpgw']->link('/preferences/')); 
	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/preferences/changepassword.php'));

	if ($_GET['cd'] == 1)
	{
		$lang1 = lang('Your password has expired');
		$lang2 = lang('You must register a new password');
		$GLOBALS['phpgw']->template->set_var('messages',"<font color=red><h1>$lang1.<br />$lang2.</h1></font>");
	}

	if ($GLOBALS['phpgw_info']['server']['auth_type'] != 'ldap')
	{
		$GLOBALS['phpgw']->template->set_var('sql_message',lang('note: This feature does *not* change your email password. This will '
			. 'need to be done manually.'));
	}

	if ($_POST['change'])
            {
                include(personalize_include_path('preferences','changepassword'));
            }
	else
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Change your password');
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
?>
