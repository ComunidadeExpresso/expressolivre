<?php
	/**************************************************************************\
	* phpGroupWare - Preferences                                               *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	class uiaclprefs
	{
		var $acl;		
		var $template;

		var $public_functions = array('index' => True);

		function uiaclprefs()
		{
			$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
		}
		
		function index()
		{
			$acl_app	= get_var('acl_app',array('POST','GET'));
			$owner		= get_var('owner',array('POST','GET'));

			if (! $acl_app)
			{
				$acl_app            = 'preferences';
				$acl_app_not_passed = True;
			}
			else
			{
				$GLOBALS['phpgw']->translation->add_app($acl_app);
			}
						
			$_SESSION['acl_app'] = $acl_app;

			
			$GLOBALS['phpgw_info']['flags']['currentapp'] = $acl_app;

			if ($acl_app_not_passed)
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadmenuactionVariable, failed to pass acl_app.',
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}
			}

			if ($GLOBALS['phpgw_info']['server']['deny_user_grants_access'] && !isset($GLOBALS['phpgw_info']['user']['apps']['admin']))
			{
				echo '<center><b>' . lang('Access not permitted') . '</b></center>';
				$GLOBALS['phpgw']->common->phpgw_exit(True);
			}
			
			
			if((!isset($owner) || empty($owner)) || !$GLOBALS['phpgw_info']['user']['apps']['admin'])
				$owner = $GLOBALS['phpgw_info']['user']['account_id'];
			
			$_SESSION['owner'] = $owner;
			
			
			$acct = CreateObject('phpgwapi.accounts',$owner);									
			$owner_name = $acct->id2name($owner);		// get owner name for title
			
			if($is_group = $acct->get_type($owner) == 'g')			
				$owner_name = lang('Group').' ('.$owner_name.')';
						
			$this->acl = CreateObject('phpgwapi.acl',(int)$owner);
			
			// begin jakjr
			$repository = $this->acl->read_repository(); //get all lines of the owner
			$cont = 0;
			$just_owner_array = array();
			foreach($repository as $repository) 
			{		
				// Pega os valores do array que são da aplicação corrente, do dono corrente e verifica se a camplo acl_location é diferente de run, que não é necessário aqui.
				if (($repository['appname'] == $GLOBALS['phpgw_info']['flags']['currentapp']) && ($repository['account'] == $owner) && ($repository['locations'] != 'run'))
				{
					$just_owner_array[$cont] = $repository;
					++$cont;
				}
			}
			//echo '<pre>';
			//print_r($just_owner_array);
			//echo '</pre>';
			// end jakjr

			if ($_POST['submit'])	{
				$processed = $_POST['processed'];
								
				$to_remove = unserialize(urldecode($processed));

				/* User records */
				$user_variable = $_POST['u_'.$GLOBALS['phpgw_info']['flags']['currentapp']];
				/* Group records */
				$group_variable = $_POST['g_'.$GLOBALS['phpgw_info']['flags']['currentapp']];
				$keys_to_keep = array();
				if(!empty($user_variable)) {
					foreach($user_variable as $key_user=>$value) {
						$temp = explode("_",$key_user);
						$keys_to_keep[(int)$temp[0]] = 1;
					}
				}

				$to_remove_count = count($to_remove);
				for($i=0;$i<$to_remove_count;++$i) {
					
					if(!array_key_exists((int)$to_remove[$i],$keys_to_keep)) {
						$this->acl->persist_shared_groups($to_remove[$i]);
					}		
					$this->acl->delete($GLOBALS['phpgw_info']['flags']['currentapp'],$to_remove[$i]);
				}
				
				/* Group records */
				//$group_variable = $_POST['g_'.$GLOBALS['phpgw_info']['flags']['currentapp']];

				if (!$group_variable)				
					$group_variable = array();
				
				@reset($group_variable);
				$totalacl = array();
				while(list($rowinfo,$perm) = each($group_variable))	{
					list($group_id,$rights) = preg_split('/_/',$rowinfo);
					$totalacl[$group_id] += $rights;
				}
				@reset($totalacl);
				while(list($group_id,$rights) = @each($totalacl))	{
					if($is_group)
						$rights &= ~PHPGW_ACL_PRIVATE;
					if(array_key_exists($user_id,$keys_to_keep))
						if(($rights & 1) == 0) {
							$this->acl->persist_shared_groups($user_id);
						}
					$this->acl->add($GLOBALS['phpgw_info']['flags']['currentapp'],$group_id,$rights);
				}

				/* User records */
				//$user_variable = $_POST['u_'.$GLOBALS['phpgw_info']['flags']['currentapp']];

				if (!$user_variable)				
					$user_variable = array();
				
				@reset($user_variable);
				$totalacl = array();
				while(list($rowinfo,$perm) = each($user_variable))	{
					list($user_id,$rights) = preg_split('/_/',$rowinfo);
					$totalacl[$user_id] += $rights;
				}
				
				@reset($totalacl);
				while(list($user_id,$rights) = @each($totalacl)) {
					if($is_group)					
						$rights &= ~ PHPGW_ACL_PRIVATE;					
					
					$this->acl->add($GLOBALS['phpgw_info']['flags']['currentapp'],$user_id,$rights);
				}
				
				$this->acl->save_repository();
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('../'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/index.php'));
			}

			$processed = Array();
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('%1 - Preferences',$GLOBALS['phpgw_info']['apps'][$acl_app]['title']).' - '.lang('acl').': '.$owner_name;
			
			if(!@is_object($GLOBALS['phpgw']->js))	{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}			
			
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			
			$this->template = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('preferences'));
			$templates = Array (
					'preferences' => 'preference_acl.tpl',				
					'acl_row'     => 'preference_acl_row.tpl',
					'acl_hidden'  => 'preference_acl_hidden.tpl'
				);
			
			$this->template->set_file($templates);

			$this->template->set_var("users_list", lang("Users List"));
			$this->template->set_var("attributes", lang("Attributes"));
			$this->template->set_var("read", lang("Read"));	
			$this->template->set_var("add", lang("Add"));
			$this->template->set_var("edit", lang("Edit"));
			$this->template->set_var("delete", lang("Delete"));
			$this->template->set_var("private", lang("Private"));
			$this->template->set_var("remove", lang("Remove"));
			$this->template->set_var("cancel", lang("Cancel"));
			
			if($acl_app=='contactcenter') {
				$this->template->set_var("private_invisible", "style='display:none'");				
			}
			else {
				$this->template->set_var("add_invisible", "");
				$this->template->set_var("private_invisible", "");				
			}

			if ($submit)
				$this->template->set_var('errors',lang('ACL grants have been updated'));

			$common_hidden_vars =
				  '     <input type="hidden" name="owner" value="'.$owner.'">'."\n"
				. '     <input type="hidden" name="acl_app" value="'.$acl_app.'">'."\n";

			$var = Array(
				'errors'      => '',
				'title'       => '<br>',
				'action_url'  => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app=' . $acl_app),
				'bg_color'    => $GLOBALS['phpgw_info']['theme']['th_bg'],
				'submit_lang' => lang('Ok'),
				'common_hidden_vars_form' => $common_hidden_vars
			);

			$this->template->set_var($var);
			$this->template->set_var('common_hidden_vars',$common_hidden_vars);
						
			// begin jakjr
			foreach($just_owner_array as $just_owner_array) {
				$id = $just_owner_array['location'];
				$rights = $this->acl->get_rights($id,$GLOBALS['phpgw_info']['flags']['currentapp']);
				$acct->get_account_name($id, $lid, $fname, $lname);
				
				if (($acct->get_type($id) == 'u') && ($owner != $id && $rights)) 
				{
					$user_array['name'] = $fname . ' ' . $lname;
					$this->display_option('u_',$id,$user_array['name'],$is_group);
					$processed[] = $id;
				}	
				else if(($acl_app == 'calendar') && ($acct->get_type($id) == 'g') && ($owner != $id && $rights))
				{
					$group_array['name'] = '(G) ' . $fname;
					$this->display_option('g_',$id,$group_array['name'],$is_group);
					$processed[] = $id;
				}	
			}
			
			unset($acct);
			//end jakjr
			
			$extra_parms = 'menuaction=preferences.uiaclprefs.index'
				. '&acl_app=' . $acl_app . '&owner='.$owner;

			$var = Array(
				'search'       => lang('search'),
				'processed'    => urlencode(serialize($processed))
			);

			$this->template->set_var($var);
			$this->template->pfp('out','preferences');
		}

		
		function check_acl($label,$id,$acl,$rights,$right,$is_group=False)	{
			$this->template->set_var($acl,$label.$GLOBALS['phpgw_info']['flags']['currentapp'].'['.$id.'_'.$right.']');
			$rights_set = (($rights & $right)?'':'disabled');
			$this->template->set_var($acl.'_selected',$rights_set);
		}		
		
	
		function display_option($label,$id,$name,$is_group)
		{
			
			$rights = $this->acl->get_rights($id,$GLOBALS['phpgw_info']['flags']['currentapp']);
			$this->template->set_var('user',$name);
			$this->template->set_var('id',$label.$GLOBALS['phpgw_info']['flags']['currentapp'].'['.$id);						
						
			
			// vv This is new
			$grantors = $this->acl->get_ids_for_location($id,$rights,$GLOBALS['phpgw_info']['flags']['currentapp']);
			
			while(@$grantors && list($key,$grantor) = each($grantors))
			{
				if($GLOBALS['phpgw']->accounts->get_type($grantor) == 'g')
				{
					$is_group_set = True;
				}
			}			

						
			$this->check_acl($label,$id,'read',$rights,PHPGW_ACL_READ,($is_group_set && ($rights & PHPGW_ACL_READ) && !$is_group?$is_group_set:False));
			$this->check_acl($label,$id,'add',$rights,PHPGW_ACL_ADD,($is_group_set && ($rights & PHPGW_ACL_ADD && !$is_group)?$is_group_set:False));
			$this->check_acl($label,$id,'edit',$rights,PHPGW_ACL_EDIT,($is_group_set && ($rights & PHPGW_ACL_EDIT && !$is_group)?$is_group_set:False));
			$this->check_acl($label,$id,'delete',$rights,PHPGW_ACL_DELETE,($is_group_set && ($rights & PHPGW_ACL_DELETE && !$is_group)?$is_group_set:False));
			$this->check_acl($label,$id,'private',$rights,PHPGW_ACL_PRIVATE,$is_group);

			$this->check_acl($label,$id,'custom_1',$rights,PHPGW_ACL_CUSTOM_1,($is_group_set && ($rights & PHPGW_ACL_CUSTOM_1) && !$is_group?$is_group_set:False));
			$this->check_acl($label,$id,'custom_2',$rights,PHPGW_ACL_CUSTOM_2,($is_group_set && ($rights & PHPGW_ACL_CUSTOM_2) && !$is_group?$is_group_set:False));
			$this->check_acl($label,$id,'custom_3',$rights,PHPGW_ACL_CUSTOM_3,($is_group_set && ($rights & PHPGW_ACL_CUSTOM_3) && !$is_group?$is_group_set:False));
			
			
			$this->template->parse('hiddens','acl_hidden',True);			
			$this->template->parse('row','acl_row',True);	
			
		}
					
	}	
?>
