<?php			
	
	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader' => True,
		'nonavbar' => True,
		'currentapp' => 'preferences'		
	);
	
	include('../../../header.inc.php');
	include('../../../phpgwapi/templates/classic/head.inc.php');
	
	$acl_app =  $_SESSION['acl_app'];
	$owner	 =  $_SESSION['owner'];
	
	$GLOBALS['phpgw_info']['flags']['currentapp'] = $acl_app;
	
	if(!@is_object($GLOBALS['phpgw']->js))	{
		$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
	}
	
	$GLOBALS['phpgw']->js->validate_file('jscode','scripts','preferences');
	$GLOBALS['phpgw']->common->phpgw_header();	
	
	
	$t = &$GLOBALS['phpgw']->template;						
	// seta o Template
	$t->set_file(array('addUser_t' => '../../../preferences/templates/classic/listUsers.tpl'));
	
	$obj_account = CreateObject('phpgwapi.accounts',$this->bo->owner);

	$post_select_organization = $_POST['select_organization'];
	$post_select_sector = $_POST['select_sector'];
	$change_organization = $_POST['change_organization'];
	
	$obj_org_sector = CreateObject('phpgwapi.sector_search_ldap');
	if ((!$post_select_organization) && (!$post_select_sector)) //primeira vez
	{
		$user_org = $obj_account->get_organization($GLOBALS['phpgw_info']['user']['account_dn']);
		$user_sector = $obj_account->get_sector($GLOBALS['phpgw_info']['user']['account_dn']);
		$user_context = $obj_account->get_context($GLOBALS['phpgw_info']['user']['account_dn']);
		
		$organizations_info = $obj_org_sector->organization_search($GLOBALS['phpgw_info']['server']['ldap_context']);
		@asort($organizations_info);
		@reset($organizations_info);					
		$sectors_info = $obj_org_sector->sector_search('ou='.$user_org.','.$GLOBALS['phpgw_info']['server']['ldap_context']);
	}
	else //mudou uma das combos
	{
		$user_org = $post_select_organization;
		$user_sector = $post_select_sector;
		if ($change_organization == "True")
			$user_context = 'ou='.$user_org.','.$GLOBALS['phpgw_info']['server']['ldap_context'];
		else
			$user_context = $post_select_sector;
		$organizations_info = $obj_org_sector->organization_search($GLOBALS['phpgw_info']['server']['ldap_context']);
		@asort($organizations_info);
		@reset($organizations_info);					
		$sectors_info = $obj_org_sector->sector_search('ou='.$user_org.','.$GLOBALS['phpgw_info']['server']['ldap_context']);
	}
		
	foreach($organizations_info as $organization)
	{
		$combo_organization .= '<option value="' . $organization . '"'; 	
		
		if (!$post_select_organization)
		{
			if($organization == $user_org)
			{
				$combo_organization .= ' selected';
			}
		}
		else
		{
			if($organization == $post_select_organization)
			{
				$combo_organization .= ' selected';
			}
		}	
		$combo_organization .= '>' .$organization.'</option>'."\n";
	}
	
	$combo_sector .= '<option value="ou='.$user_org.','.$GLOBALS['phpgw_info']['server']['ldap_context'].'"> --------- </option>'."\n";
	foreach($sectors_info as $sector)
	{
		$combo_sector .= '<option value="' . $sector->sector_context . '"';

		if (!$post_select_sector)
		{
			if($sector->sector_name == $user_sector)
				$combo_sector .= ' selected';
		}
		else
		{
			if($sector->sector_context == $post_select_sector)
				$combo_sector .= ' selected';
		}
		$combo_sector .= '>' .$sector->sector_name.'</option>'."\n";
	}			
	
	// Monta lista de Grupos e Usuários
	$users = Array();
	$groups = Array();

	$ds = $GLOBALS['phpgw']->common->ldapConnect();
    if ($ds) 
    {
		$sr=ldap_list($ds, $user_context, ("(&(cn=*)(phpgwaccounttype=u))"));
		$info = ldap_get_entries($ds, $sr);
		for ($i=0; $i<$info["count"]; ++$i)
			$users[$uids=$info[$i]["uidnumber"][0]] = Array('name'	=>	$uids=$info[$i]["cn"][0], 'type'	=>	u);
	}
	ldap_close($ds);
    	
	@asort($users);
	@reset($users);	
	@asort($groups);
	@reset($groups);

	$options ='';

	foreach($users as $id => $user_array) {
		if($owner != $id){
			$newId = 'u_'.$acl_app.'['.$id;			
			$options .= '<option  value="'.$newId.'">'.utf8_decode($user_array['name']).$array_app.'</option>'."\n";
		}
	}

	$t->set_var ('lang_Organization',lang('Organization'));
	$t->set_var ('lang_Sector',lang('Sector'));
	$t->set_var ('lang_Calendar',lang('Calendar'));
	$t->set_var ('lang_Add_Participants',lang('Add Participants'));
	$t->set_var ('lang_to_Search',lang('to Search'));
	$t->set_var ('lang_Close',lang('Close'));
	$t->set_var ('lang_Add',lang('Add'));
	
	$t->set_var('options',$options);
	$t->set_var('combo_organization', $combo_organization);
	$t->set_var('combo_sector', $combo_sector);
	$t->parse('out','addUser_t',true);
	$t->p('out');
	$GLOBALS['phpgw']->common->phpgw_exit();	
?>
