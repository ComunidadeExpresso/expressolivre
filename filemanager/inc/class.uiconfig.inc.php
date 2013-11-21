<?php
	/**************************************************************************\
	* -------------------------------------------------------------------------*
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	\**************************************************************************/

require_once("class.notifications.inc.php");

class uiconfig
{
	private $bo;
	private $template;
	
	var $public_functions = array
	(
		'createFolder'		=> True,
		'folders' 			=> True,
		'groups_users' 		=> True,		
		'load_quota'			=> True,
		'notifyUploads'		=> True,
		'renameFolder'		=> True,
		'removeFolder'		=> True,
		'reconstructFolder'	=> True,
		'search_dir'			=> True,
		'set_owner'			=> True,		
		'search_user'		=> True,
		'set_permission'		=> True,
		'update_quota'		=> True,
		'quota'				=> True
	 );

	function uiconfig()
	{
		 $this->bo 			= CreateObject('filemanager.bofilemanager');
		 $this->template	= $GLOBALS['phpgw']->template;
		 
		 $GLOBALS['phpgw_info']['flags'] = array
			 (
				 'currentapp'    	=> 'filemanager',
				 'noheader'      	=> False,
				 'nonavbar' 		=> False,
				 'nofooter'      	=> False,
				 'noappheader'   	=> False,
				 'enable_browser_class'  => True
			 );
	}

	function vfs_functions(){}
	
	function folders()
	{
		$GLOBALS['phpgw']->common->phpgw_header();

		$this->template->set_file(array('config_list' => 'config_folders.tpl'));
		$this->template->set_block('config_list','body','body');
		
		$vars = array( 
			 			'lang_back'			=> lang('Back'),
						'lang_directory'		=> lang('directory'),
						'lang_search' 		=> lang('search'),
						'lang_remove' 		=> lang('remove'),
						'lang_rename' 		=> lang('rename'),
						'lang_create' 		=> lang('create'),
						'lang_reconstruct' 	=> lang('reconstruct'),
						'lang_Folder_ Management' => lang('Folder Management'),
						'path_filemanager'	=> $GLOBALS['phpgw_info']['flags']['currentapp']
					);

		$this->template->set_var($vars);
		$this->template->pparse('out','body');
        
        $GLOBALS['phpgw']->common->phpgw_footer();
        $GLOBALS['phpgw']->common->phpgw_exit();
        
	 }

	 function groups_users()
	 {
		$GLOBALS['phpgw']->common->phpgw_header();

		$this->template->set_file(array('config_list' => 'config_owner.tpl'));
		$this->template->set_block('config_list','body','body');
		
		$vars = array(
						'lang_Add'				=> lang('Add'),
						'lang_back'				=> lang('Back'),
						'lang_directory'			=> lang('directory'),
						'lang_Delete'				=> lang('Delete'),
						'lang_Edit'				=> lang('Edit'),
						'lang_Read'				=> lang('Read'),
						'lang_private'			=> lang('private'),		
						'lang_search'			=> lang('search'),
						'lang_setowner'			=> lang('set owner'),
						'lang_setperm'			=> lang('set permission'),
						'lang_Search_Folders'	=> lang('Search Folders'),
						'lang_Search_Users'		=> lang('Search Users'),	
						'lang_users_and_groups'	=> lang('Users and groups'),
						'lang_permissions_groups_users' => lang('Permissions of groups and users'),	
						'path_filemanager'		=> $GLOBALS['phpgw_info']['flags']['currentapp']
					);
		
		$this->template->set_var($vars);
		$this->template->pparse('out','body');

		$GLOBALS['phpgw']->common->phpgw_footer();
		$GLOBALS['phpgw']->common->phpgw_exit();
	 }

	function createFolder()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			 
		 $this->bo = CreateObject('filemanager.bofilemanager');
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['name']));
		 if (strlen($name) < 2)
			 return false;
		 $c = CreateObject('phpgwapi.config','filemanager');
		 $c->read_repository();
		 $current_config = $c->config_data;

		 $this->bo->vfs->override_acl = 1;

		 if ( $this->bo->vfs->mkdir(array(
			 'string' => $name,
			 'relatives' => array(RELATIVE_NONE)
		 )) )
		 if ( $this->bo->vfs->set_quota(array(
			 'string' => $name,
			 'relatives' => array(RELATIVE_NONE),
			 'new_quota' => $current_config['filemanager_quota_size']
		 )) )
		 $return = True;

		 $this->bo->vfs->override_acl = 0;
		 if ($return){
			 echo "Folder created";
		 }
		 else
			 echo "Error";
	 }

	 function removeFolder()
	 {
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			 
		 $this->bo = CreateObject('filemanager.bofilemanager');
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_POST['dir']));
		 if (strlen($name) < 2)
			 return false;
		 if (	 $this->bo->vfs->delete(array(
			 'string' => $name,
			 'relatives' => array(RELATIVE_NONE)
		 )) )
		 {
			 /* Clean the log */
			 $GLOBALS['phpgw']->db->query('DELETE FROM phpgw_vfs WHERE directory = \''.$name.'\'',__LINE__,__FILE__);
			 if ($GLOBALS['phpgw']->db->Error)
				 echo "Erro";
			 else
			 {		 
				 $GLOBALS['phpgw']->db->query('DELETE FROM phpgw_vfs_quota WHERE directory = \''.$name.'\'',__LINE__,__FILE__);
				 if (!$GLOBALS['phpgw']->db->Error)
					 echo lang('directory removed sucessfully');
				 else
					 echo "Erro";
			 }
		 }
		 else
			 echo lang("No permission to delete the folder %1", $name );

	}
	
	function reconstructFolder()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			 
		 $this->bo = CreateObject('filemanager.bofilemanager');
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_POST['dir']));
		 if (strlen($name) < 2)
			 return false;
		 $this->bo->vfs->update_real(array(
			 'string'        => $name,
			 'relatives'     => array(RELATIVE_NONE)
		 ),True);
		 $this->bo->vfs->flush_journal(array(
			 'string' => $name,
			 'relatives' => array(RELATIVE_NONE),
			 'deleteall' => True
		 ));
		 echo lang('Your operation was successfully executed');
	}

	function renameFolder()
	{
		 $GLOBALS['phpgw_info']['flags'] = array
			 (
				 'currentapp'    => 'filemanager',
				 'noheader'      => True,
				 'nonavbar' => True,
				 'nofooter'      => True,
				 'noappheader'   => True,
				 'enable_browser_class'  => True
			 );
		 $this->bo = CreateObject('filemanager.bofilemanager');
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['dir']));
		 $to = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['to']));
		 if (strlen($name) < 2)
			 return false;
		if ( $this->bo->vfs->mv(array(
			 'from'        => $name,
			 'to'	=> $to,
			 'relatives'     => array(RELATIVE_NONE)
		 )) ){
			 $this->bo->vfs->flush_journal(array(
				 'string' => $name,
				 'relatives' => array(RELATIVE_NONE),
				 'deleteall' => True
			 ));
			 echo lang('Your operation was successfully executed');
		 }
		else
			echo lang('Error');
	}

	function quota()
	{
		$GLOBALS['phpgw']->common->phpgw_header();

		$this->template->set_file(array('config_list' => 'config_quota.tpl'));
		$this->template->set_block('config_list','body','body');

		$vars = array(
			'lang_back'				=> lang('Back'),
			'lang_directory'			=> lang('directory'),
			'lang_save'				=>lang('save'),						
			'lang_search'			=> lang('search'),
			'lang_Management_Quota'	=> lang('Management Quota'),	
			'path_filemanager'	=> $GLOBALS['phpgw_info']['flags']['currentapp']
		);

		$this->template->set_var($vars);
		$this->template->pparse('out','body');

		$GLOBALS['phpgw']->common->phpgw_footer();
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	 
	function notifyUploads()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Filemanager') ." - " . lang("Email notify uploads");
		$GLOBALS['phpgw']->common->phpgw_header();
		
		$notify 		= new notifications();
		$value_email_to = "";

		if( $_POST['button_add'] || $_GET['editUser'] )
		{
			if( $_GET['editUser'] )
			{
				$result = $notify->SearchId($_GET['editUser']);
				$emails_to = explode(",", $result[0]['email_to']);

                $emails_to_count = count($emails_to);
				for( $i = 0 ; $i < $emails_to_count; ++$i )
				{
					$value_email_to .= "<tr>";
					$value_email_to .= "<td>".$emails_to[$i]."</td>";
					$value_email_to .= "<td align='center'>";
					$value_email_to .= "<a href='javascript:void();' onclick='notify.deleteEmail(\"".$emails_to[$i]."\",this);'>Remover</a>";
					$value_email_to .= "</td>";
					$value_email_to .= "</tr>";
				}
			}
			
			$vars = array(
							'action_url_back'	=> "./index.php?menuaction=filemanager.uiconfig.notifyUploads",
							'attr_readonly'		=> ( $result[0]['email_from'] ) ? 'readonly="readonly"' : "",
							'lang_Add'			=> lang("Add"),
							'lang_Back'			=> lang("Back"),
							'lang_Email'		=> lang("Email"),
							'lang_Excluir'		=> lang("Delete"),
							'lang_legend1'		=> lang("When the user with the email send a file"),
							'lang_legend2'		=> lang("Notify email"),
							'lang_legend3'		=> lang("Emails reported"),
							'lang_from'			=> lang("From"),
							'lang_to'			=> lang("To"),
							'value_email_from'	=> ( $result[0]['email_from'] ) ? $result[0]['email_from'] : "",
							'value_email_to'	=> $value_email_to
			);
			
			$handle = "AddEmail";
		}
		else
		{	
			if( trim( $_POST['search_email'] ) != "" )
			{
				$limit	= 10;
				$offset	= 1;
				
				if( $_POST['bt_next'] )
					$offset = $_POST['button_next'] + 1 ; 				

				if( $_POST['bt_previous'] && $_POST['button_next'] > 1)
				{
					$offset = $_POST['button_next'] - 1;
				}
				
				$result = $notify->SearchEmail( $_POST['search_email'], $limit, $offset );

				foreach( $result as $tmp )
				{
					$value_email_to .= '<tr>';
					$value_email_to .= '<td align="left" width="40%">'.$tmp['email_from'].'</td>';
					$value_email_to .= '<td align="left" width="40%">'.str_replace(",", "<br/>", $tmp['email_to']).'</td>';
					$value_email_to .= '<td align="center" width="10%"><a href="./index.php?menuaction=filemanager.uiconfig.notifyUploads&editUser='.$tmp['filemanager_id'].'">'.lang("Edit").'</a></td>';
					$value_email_to .= '<td align="center" width="10%"><a href="javascript:void();" onclick="notify.deleteEmailUser(\''.$tmp['filemanager_id'].'\', this);">'.lang("Delete").'</a></td>';
					$value_email_to .= '</tr>';
				}
			}
			
			$vars = array(
							'action_url'			=> "./index.php?menuaction=filemanager.uiconfig.notifyUploads",
							'action_url_back'		=> "./admin",
							'display_bt_previous'	=> ( $offset > 1 ) ? "line" : "none",
							'display_bt_next'		=> ( count($result) < $limit ) ? "none" : "line",
							'lang_Add'				=> lang("Add"),
							'lang_Back'				=> lang("Back"),
							'lang_Delete'			=> lang("Delete"),
							'lang_Edit'				=> lang("Edit"),
							'lang_From'				=> lang("From"),
							'lang_To'				=> lang("To"),
							'lang_search'			=> lang("Search"),
							'lang_next'				=> lang("Next"),
							'lang_previous'			=> lang("Previous"),
							'value_search_email'	=> $_POST['search_email'],
							'value_email_to'		=> $value_email_to,
							'value_next'			=> $offset,							
							'value_previous'		=> $limit
						 );
			
			$handle = "index";
		}

		$this->template->set_file(array('config_email' => 'notify_upload.tpl'));
		$this->template->set_block('config_email',$handle);
		$this->template->set_var($vars);
		$this->template->pparse('out',$handle);

        $GLOBALS['phpgw']->common->phpgw_footer();
        $GLOBALS['phpgw']->common->phpgw_exit();
	}
	 
	function search_dir()
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		$GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		$GLOBALS['phpgw_info']['flags']['noappheader'] = True;

		$_options = "";
		$query = "";

		$name = $GLOBALS['phpgw']->db->db_addslashes($_POST['name']);

		$query = 'SELECT directory,name from phpgw_vfs WHERE directory = \'/home\' and name like \'%'.$name.'%\' LIMIT 1';

		$GLOBALS['phpgw']->db->query( $query,__LINE__,__FILE__ );

		while ( $GLOBALS['phpgw']->db->next_record() )
		{
			$val = $GLOBALS['phpgw']->db->row();
			$_options .= "<option>".$val['directory']."/".$val['name']."</option>";
		}

		echo $_options;
	}
	
	function search_user()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
		
		 $_options = "";
		 
		 $account_info = $GLOBALS['phpgw']->accounts->get_list('both',0,'','',$_POST['name'],'all');

		 foreach($account_info as $val)
			 $_options .= "<option value='".$val['account_id']."'>".$val['account_lid']."</option>";
		 
		echo $_options;
	}
	
	function set_permission()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;

		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['dir']));
		 $perms = ($_GET['perms'])*1;
		 $owner = ($_GET['owner'])*1;
		 $dirs=explode('/',$name);
		 $GLOBALS['phpgw']->db->query('SELECT owner_id  from phpgw_vfs  WHERE directory = \'/'.$dirs[1].'\' and name=\''.$dirs[2].'\' LIMIT 1',__LINE__,__FILE__);
		 if ($GLOBALS['phpgw']->db->next_record()){
			 $val = $GLOBALS['phpgw']->db->row();
			 $owner_id = $val['owner_id'];
		 }

		 $query = "SELECT count(*) FROM phpgw_acl WHERE acl_appname = 'filemanager' and acl_account = '".$owner_id."' and acl_location='".$owner."'";
		 if ($GLOBALS['phpgw']->db->query($query) && $GLOBALS['phpgw']->db->next_record())
			 $val = $GLOBALS['phpgw']->db->row();
		 else
		 {
			 echo $GLOBALS['phpgw']->db->error;
			 return false;
		 }
		 if ($val['count'] == '1')
			$GLOBALS['phpgw']->db->query("UPDATE phpgw_acl SET acl_rights = ".$perms." where acl_appname = 'filemanager' and acl_account = '".$owner_id."' AND acl_location = '".$owner."'",__LINE__,__FILE__);
		 else
			 $GLOBALS['phpgw']->db->query("INSERT INTO phpgw_acl values('filemanager','".$owner."','".$owner_id."',".$perms.")",__LINE__,__FILE__);
		 if ($GLOBALS['phpgw']->db->Error)
			 echo "Erro";
		 else
		 {
			echo lang('entry updated sucessfully');
		 }
		 return;
	}


	function set_owner()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
		 
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['dir']));
		 $owner = ($_GET['owner'])*1;

		 $GLOBALS['phpgw']->db->query('UPDATE phpgw_vfs SET owner_id = '.$owner.' WHERE directory = \''.$name.'\'',__LINE__,__FILE__);
		 if ($GLOBALS['phpgw']->db->Error)
			 echo "Erro";
		 else
		 {
			 $dirs=explode('/',$name);
			 $GLOBALS['phpgw']->db->query('UPDATE phpgw_vfs SET owner_id = '.$owner.' WHERE directory = \'/'.$dirs[1].'\' and name=\''.$dirs[2].'\'',__LINE__,__FILE__);
			 if ($GLOBALS['phpgw']->db->Error)
				 echo "Erro";
			 else
			 {
				 echo lang('entry updated sucessfully');
			 }
		 }
		 return;
	}

	function update_quota()
	{
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;

		 $name	= $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_POST['dir']));
		 $size	= ($_POST['val']) * 1;

		 $eAdminDbFunctions = CreateObject('expressoAdmin1_2.db_functions');
		 if($eAdminDbFunctions->use_cota_control()) {
		 	$eAdminFunctions = CreateObject('expressoAdmin1_2.functions');
						
			if(!$eAdminFunctions->has_file_disk_quota($name,$size)){
				echo lang("not enough quota");
				return;
			}

		 }
		 
		 /* See if quota exists or not */
		 $query = "SELECT count(directory) FROM phpgw_vfs_quota WHERE directory = '".$name."' LIMIT 1";
		 if ($GLOBALS['phpgw']->db->query($query) && $GLOBALS['phpgw']->db->next_record())
			 $val = $GLOBALS['phpgw']->db->row();
		 else
		 {
			 echo $GLOBALS['phpgw']->db->error;
			 return false;
		 }
		 if ($val['count'] == '1')
		 {
			 $GLOBALS['phpgw']->db->query('UPDATE phpgw_vfs_quota SET quota_size = '.$size.' WHERE directory = \''.$name.'\'',__LINE__,__FILE__);
			 if ($GLOBALS['phpgw']->db->Error)
				 echo "Erro";
			 else
				 echo lang('entry updated sucessfully');
		 }
		 else
		 {
			 /*preferences does not exist*/
			 $query = "INSERT INTO phpgw_vfs_quota values ('".$name."',".$size.")";
			 if (!$GLOBALS['phpgw']->db->query($query))
				 echo $GLOBALS['phpgw']->db->error;
			 else
				 echo lang('entry updated sucessfully');
		 }
	 }
	 
	 function load_quota()
	 {
		 $GLOBALS['phpgw_info']['flags']['noheader'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar'] 	= True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']	= True;
		 $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			 
		 $name = $GLOBALS['phpgw']->db->db_addslashes(base64_decode($_GET['name']));
		 $GLOBALS['phpgw']->db->query('SELECT quota_size FROM phpgw_vfs_quota WHERE directory = \''.$name.'\' LIMIT 1',__LINE__,__FILE__);
		 $GLOBALS['phpgw']->db->next_record();
		 $val =$GLOBALS['phpgw']->db->row();
		 echo $val['quota_size'];
		 return;

	 }
}

?>
