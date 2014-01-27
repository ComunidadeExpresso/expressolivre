<?php

/* * ************************************************************************\
 * -------------------------------------------------------------------------*
 * This library is free software; you can redistribute it and/or modify it  *
 * under the terms of the GNU Lesser General Public License as published by *
 * the Free Software Foundation; either version 2.1 of the License,         *
 * or any later version.                                                    *
 * This library is distributed in the hope that it will be useful, but      *
 * WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
 * See the GNU Lesser General Public License for more details.              *
 * You should have received a copy of the GNU Lesser General Public License *
 * along with this library; if not, write to the Free Software Foundation,  *
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \************************************************************************* */

/* $Id: class.uifilemanager.inc.php 17511 2004-12-12 06:35:24Z dawnlinux $ */

class uifilemanager {

	var $public_functions = array(
		 'index' => True,
		 'help' => True,
		 'view' => True,
		 'export' => True,
		 'touch' => True,
		 'history' => True,
		 'edit' => True,
		 'fileModels' => True,
		 'getReturnExecuteForm' => True,
		 'dir_ls' => True,
		 'search' => True,
		 'setFileMaxSize' => True,
		 'get_folders_list' => True,
		 'showUploadboxes' => True,
		 'createdir' => True,
		 'removedir' => True,
		 'uploadModel' => True
	);
	//keep
	var $bo;
	var $vfs_functions;
	var $t; //template object
	var $dispath;
	var $cwd;
	var $lesspath;
	var $readable_groups;
	var $files_array;
	var $numoffiles;
	var $dispsep;
	var $target;
	var $prefs; //array
	var $groups_applications;
	var $current_config;
	var $dirs;
	var $to;
	var $changedir; // for switching dir.
	var $cdtodir; // for switching dir.
	var $newfile_or_dir;
	var $newfile_x;
	var $createfile_var;
	var $move_to_x;
	var $copy_to_x;
	var $edit_x;
	var $edit_file;
	var $edit_preview_x;
	var $edit_save_x;
	var $edit_save_done_x;
	var $edit_cancel_x;
	// this ones must be checked thorougly;
	var $fileman = Array();
	//var $fileman;
	var $path;
	var $file; // FIXME WHERE IS THIS FILLED?
	var $sortby;
	var $messages;
	var $limit; //for paging (paginacao)
	var $offset; //for paging (paginacao)
	var $now;

	function uifilemanager() {
		$this->messages = &$_SESSION['phpgw_info']['filemanager']['user']['messages'];

		$GLOBALS['phpgw']->browser = CreateObject('phpgwapi.browser');

		$this->now = date('Y-m-d');

		$this->bo = CreateObject('filemanager.bofilemanager');

		$this->vfs_functions = CreateObject('filemanager.vfs_functions');

		$this->t = $GLOBALS['phpgw']->template;
		$c = CreateObject('phpgwapi.config', 'filemanager');
		$c->read_repository();

		$this->current_config = $c->config_data;

		// here local vars are created from the HTTP vars
		@reset($GLOBALS['HTTP_POST_VARS']);
		while (list($name, ) = @each($GLOBALS['HTTP_POST_VARS'])) {
			$this->$name = $GLOBALS['HTTP_POST_VARS'][$name];
		}

		@reset($GLOBALS['HTTP_GET_VARS']);
		while (list($name, ) = @each($GLOBALS['HTTP_GET_VARS'])) {
			$$name = $GLOBALS['HTTP_GET_VARS'][$name];
			$this->$name = $GLOBALS['HTTP_GET_VARS'][$name];
		}

		$to_decode = array
			 (
			 'op' => array('op' => ''),
			 'path' => array('path' => ''),
			 'filename' => array('filename' => ''),
			 'file' => array('file' => ''),
			 'sortby' => array('sortby' => ''),
			 'messages' => array('messages' => ''),
			 'show_upload_boxes' => array('show_upload_boxes' => ''),
			 'from' => array('from' => ''),
			 'to' => array('to' => '')
		);

		reset($to_decode);
		while (list($var, $conditions) = each($to_decode)) {
			while (list($condvar, $condvalue) = each($conditions)) {
				if (isset($$condvar) && ($condvar == $var || $$condvar == $condvalue)) {
					$this->$var = stripslashes(base64_decode($$var));
				}
			}
		}

		// get appl. and user prefs
		$pref = CreateObject('phpgwapi.preferences', $this->bo->userinfo['username']);
		$pref->read_repository();
		//$GLOBALS['phpgw']->hooks->single('add_def_pref', $GLOBALS['appname']);
		$pref->save_repository(True);
		$pref_array = $pref->read_repository();
		$this->prefs = $pref_array[$this->bo->appname]; //FIXME check appname var in _debug_array
		//always show name

		$this->prefs[name] = 1;


		if ($this->prefs['viewinnewwin']) {
			$this->target = '_blank';
		}


		/*
		  Check for essential directories
		  admin must be able to disable these tests
		 */

		// check if basedir exist 
		$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->basedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		if ($test['mime_type'] != 'Directory') {
			die('Base directory does not exist, Ask adminstrator to check the global configuration.');
		}

		$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->basedir . $this->bo->fakebase, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		if ($test['mime_type'] != 'Directory') {
			$this->bo->vfs->override_acl = 1;

			$this->bo->vfs->mkdir(array(
				 'string' => $this->bo->fakebase,
				 'relatives' => array(RELATIVE_NONE)
			));

			$this->bo->vfs->override_acl = 0;

			//test one more time
			$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->basedir . $this->bo->fakebase, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

			if ($test['mime_type'] != 'Directory') {
				die('Fake Base directory does not exist and could not be created, please ask the administrator to check the global configuration.');
			} else {
				$this->messages[] = lang('Fake Base Dir did not exist, created a new one.');
			}
		}

		$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->basedir . $this->bo->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		if ($test['mime_type'] != 'Directory') {
			
			$c_admin = CreateObject('phpgwapi.config', 'expressoAdmin1_2');
			$c_admin->read_repository();
			if($c_admin->config_data['expressoAdmin_cotasOu'])
				$this->generate_error(lang("You have access to the application, but you have no quota. Please contact your administrator"));
			
			$this->bo->vfs->override_acl = 1;

			$this->bo->vfs->mkdir(array(
				 'string' => $this->bo->homedir,
				 'relatives' => array(RELATIVE_NONE)
			));
			$this->bo->vfs->set_quota(array(
				 'string' => $this->bo->homedir,
				 'relatives' => array(RELATIVE_NONE),
				 'new_quota' => $this->current_config['filemanager_quota_size']?$this->current_config['filemanager_quota_size']:0
			));

			$this->bo->vfs->override_acl = 0;

			//test one more time
			$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->basedir . $this->bo->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

			if ($test['mime_type'] != 'Directory') {
				die('Your Home Dir does not exist and could not be created, please ask the adminstrator to check the global configuration.');
			} else {
				$this->messages[] = lang('Your Home Dir did not exist, eGroupWare created a new one.');
				// FIXME we just created a fresh home dir so we know there nothing in it so we have to remove all existing content
			}
		}
		
	}

	function generate_error($error) {
		$template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/filemanager/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
		$template->set_file(
				Array(
					'error_model' => 'errors.tpl'
				)
			);
		$template->set_block('error_model','error_page');
		$template->set_var("errors",$error);
		$GLOBALS['phpgw_info']['flags'] = array
			 (
			 'currentapp' => 'filemanager',
			 'noheader' => False,
			 'nonavbar' => False,
			 'nofooter' => False,
			 'noappheader' => False,
			 'enable_browser_class' => True
		);
		$GLOBALS['phpgw']->common->phpgw_header();
		$template->pfp('out','error_page');
		$GLOBALS['phpgw']->common->phpgw_footer();
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	function fileModels() {
		$GLOBALS['phpgw_info']['flags'] = array
			 (
			 'currentapp' => 'filemanager',
			 'noheader' => False,
			 'nonavbar' => False,
			 'nofooter' => False,
			 'noappheader' => False,
			 'enable_browser_class' => True
		);

		$GLOBALS['phpgw']->common->phpgw_header();
		$this->t->set_file(array('models' => 'fileModels.tpl'));
		$this->t->set_block('models', 'header', 'header');
		$this->t->set_block('models', 'body', 'body');
		$this->t->set_block('models', 'footer', 'footer');
		$this->t->set_var('url_1', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=article');
		$this->t->set_var('model_1', 'article');
		$this->t->set_var('lang_1', lang('article'));

		$this->t->set_var('url_2', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=calendar');
		$this->t->set_var('model_2', 'calendar');
		$this->t->set_var('lang_2', lang('calendar'));

		$this->t->set_var('url_3', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=todo');
		$this->t->set_var('model_3', 'todo');
		$this->t->set_var('lang_3', lang('todo'));

		$this->t->set_var('url_4', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=slide');
		$this->t->set_var('model_4', 'slide');
		$this->t->set_var('lang_4', lang('slide'));

		$this->t->set_var('url_5', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=cards');
		$this->t->set_var('model_5', 'cards');
		$this->t->set_var('lang_5', lang('cards'));

		$this->t->set_var('url_6', './index.php?menuaction=filemanager.uifilemanager.uploadModel&model=resume');
		$this->t->set_var('model_6', 'resume');
		$this->t->set_var('lang_6', lang('resume'));


		$this->t->pparse('out', 'models');
	}

	function uploadModel() {
		$GLOBALS['phpgw_info']['flags'] = array
			 (
			 'currentapp' => 'filemanager',
			 'noheader' => False,
			 'nonavbar' => False,
			 'nofooter' => False,
			 'noappheader' => False,
			 'enable_browser_class' => True
		);

		$GLOBALS['phpgw']->common->phpgw_header();

		$filename = lang('new') . "_" . lang($this->model) . rand(0, 1000) . ".html";
		$this->bo->vfs->cp(array(
			 'from' => PHPGW_SERVER_ROOT . '/filemanager/templates/default/' . $this->model . '.html',
			 'to' => $filename,
			 'relatives' => array(RELATIVE_NONE | VFS_REAL, RELATIVE_ALL)
		));

		$this->bo->vfs->set_attributes(array(
			 'string' => $filename,
			 'relatives' => array(RELATIVE_ALL),
			 'attributes' => array(
				  'mime_type' => "text/html",
				  'comment' => ""
			 )
		));
		$this->filename = $filename;
		$this->edit();
	}

	function index() {
		$GLOBALS['phpgw_info']['flags'] = array
			 (
			 'currentapp' => 'filemanager',
			 'noheader' => False,
			 'nonavbar' => False,
			 'nofooter' => False,
			 'noappheader' => False,
			 'enable_browser_class' => True
		);

		$GLOBALS['phpgw']->common->phpgw_header();

		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/inc/load_lang.php'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/global.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/main.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/common_functions.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/connector.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/draw_api.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/drag_area.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/js/handler.js'></script>";
		
		// Temas Expresso
		$theme = "window_" . $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'] . ".css";

		if (!file_exists('filemanager/tp/expressowindow/css/' . $theme))
			$theme = "window_default.css";

		// Path FileManager
		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = (!empty($webserver_url) ) ? $webserver_url : '/';

		if (strrpos($webserver_url, '/') === false || strrpos($webserver_url, '/') != (strlen($webserver_url) - 1))
			$webserver_url .= '/';

		$webserver_url = $webserver_url . 'filemanager/';

		$js = "var path_filemanager	= '" . $webserver_url . "';";
		$js .= "var my_home_filemanager	= '" . trim($GLOBALS['uifilemanager']->bo->vfs->my_home) . "';";

		echo "<script type='text/javascript'>" . $js . "</script>";

		// Expresso Window - CSS
		print '<link rel="stylesheet" type="text/css" href="' . $webserver_url . 'tp/expressowindow/css/' . $theme . '" />';

		// Expresso Window - JS 
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/tp/expressowindow/js/xtools.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/tp/expressowindow/js/jsloader.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/tp/expressowindow/js/makeW.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/tp/expressowindow/js/dragdrop.js'></script>";
		echo "<script src='" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/tp/expressowindow/js/show_hidden.js'></script>";

		echo "<script src='./phpgwapi/js/dftree/dftree.js'></script>";

		# Page to process users
		# Code is fairly hackish at the beginning, but it gets better
		# Highly suggest turning wrapping off due to long SQL queries
		###
		# Some hacks to set and display directory paths correctly
		###
		// new method for switching to a new dir.
		if ($this->changedir == 'true' && $this->cdtodir || $this->goto_x) {
			$this->path = $this->cdtodir;
		}

		if (!$this->path) {
			$this->path = $this->bo->vfs->pwd();

			if (!$this->path || $this->bo->vfs->pwd(array('full' => False)) == '') {
				$this->path = $this->bo->homedir;
			}
		}

		$this->bo->vfs->cd(array('string' => False, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		$this->bo->vfs->cd(array('string' => $this->path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

		$pwd = $this->bo->vfs->pwd();

		if (!$this->cwd = substr($this->path, strlen($this->bo->homedir) + 1)) {
			$this->cwd = '/';
		} else {
			$this->cwd = substr($pwd, strrpos($pwd, '/') + 1);
		}

		$this->disppath = $this->path;

		/* This just prevents // in some cases */
		if ($this->path == '/') {
			$this->dispsep = '';
		} else {
			$this->dispsep = '/';
		}

		if (!($this->lesspath = substr($this->path, 0, strrpos($this->path, '/')))) {
			$this->lesspath = '/';
		}

		/* Check permission */
		if ($this->bo->vfs->acl_check(array(
						'string' => $this->path,
						'relatives' => array(RELATIVE_NONE),
						'operation' => PHPGW_ACL_READ
				  ))) {
			$this->can_read = True;
		}


		if ( $_SESSION['phpgw_info']['user']['filemanager']['flush'] != 'flushed' ) {
			/* Flush journal-deleted */
			$this->bo->vfs->flush_journal(array(
				 'string' => $this->path,
				 'relatives' => array(RELATIVE_NONE),
				 'deleteall' => True
			));
			$_SESSION['phpgw_info']['user']['filemanager']['flush'] = 'flushed';
		}



		# if is different path than home and no permission allowed
		if ($this->path != $this->bo->homedir && $this->path != $this->bo->fakebase && $this->path != '/' && !$this->can_read) {
			$this->messages[] = lang('You do not have access to %1', $this->path);
			$this->path = $this->homedir;
			$this->bo->vfs->cd(array('string' => $this->path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			$GLOBALS['phpgw']->common->phpgw_footer();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		$this->bo->userinfo['working_id'] = $this->bo->vfs->working_id;
		$this->bo->userinfo['working_lid'] = $GLOBALS['phpgw']->accounts->id2name($this->bo->userinfo['working_id']);

		# Verify path is real
		if ($this->path != $this->bo->homedir && $this->path != '/' && $this->path != $this->bo->fakebase) {
			if (!$this->bo->vfs->file_exists(array(
							'string' => $this->path,
							'relatives' => array(RELATIVE_NONE)
					  ))) {
				$this->messages[] = lang('Error:') . lang('Directory %1 does not exist', $this->path);
				$this->path = $this->homedir;
				$this->bo->vfs->cd(array('string' => $this->path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
				$GLOBALS['phpgw']->common->phpgw_footer();
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}


		# Default is to sort by name
		if (!$this->sortby) {
			$this->sortby = 'name';
		}
		if ($this->update_x == 1) {
			$this->bo->vfs->update_real(array(
				 'string' => $this->path,
				 'relatives' => array(RELATIVE_NONE)
			));
			header('Location:' . $this->encode_href('index.php?menuaction=filemanager.uifilemanager.index', '&path=' . base64_encode($this->bo->homedir)));
		} elseif ($this->newfile_x && $this->newfile_or_dir) { // create new textfile
			$this->createfile();
		} elseif ($this->edit_cancel_x) {
			$this->readFilesInfo();
			$this->fileListing();
		} elseif ($this->edit_x || $this->edit_preview_x || $this->edit_save_x || $this->edit_save_done_x) {
			$this->edit();
		} else {
			//$this->readFilesInfo();
			$this->fileListing();
		}
	}

	function setFileMaxSize()
	{
		$maxSize	= $_POST['maxFileSize'];
		$file		= "setFileMaxSize.php";
		$writeFile = "<?php $"."SET_FILE_MAX_SIZE="."\"".base64_encode(serialize($maxSize))."\""." ?>";
		$filename = dirname(__FILE__).'/'.$file;
		$content = $writeFile;
			
		if ( !$handle = fopen($filename, 'w+') )
			return false;
		
		if (fwrite($handle, $content) === FALSE)
			return false;

		fclose($handle);
		
		return true;
	}

	function get_permissions() {
		/* get permissions */
		if ((preg_match('+^' . $this->bo->fakebase . '\/(.*)(\/|$)+U', $this->path, $matches)) && $matches[1] != $this->bo->userinfo['account_lid']) { //FIXME matches not defined
			$this->bo->vfs->working_id = $GLOBALS['phpgw']->accounts->name2id($matches[1]); //FIXME matches not defined
		} else {
			$this->bo->vfs->working_id = $this->bo->userinfo['username'];
		}

		# Check available permissions for $this->path, so we can disable unusable operations in user interface
		$path = explode('/', $this->path);
		$owner_id = $this->bo->vfs->ownerOf($this->bo->fakebase, $path[2]);
		$user_id = $GLOBALS['phpgw_info']['user']['account_id'];
		if ($owner_id == $user_id) {
			$rights = 31;
		} else {
			$acl = CreateObject('phpgwapi.acl', $owner_id);
			$acl->account_id = $owner_id;
			$acl->read_repository();
			$rights = $acl->get_rights($user_id);
		}
		return $rights;
	}

	function dir_ls()
	{
		// change dir to this->path
		$this->bo->vfs->cd(array('string' => $this->path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		$return['permissions'] = $this->get_permissions();
		/*$return['quota']['usedSpace'] = $this->bo->vfs->get_size(array(
						'string' => $this->path,
						'relatives' => array(RELATIVE_NONE)
				  ));*/
		
		$return['quota']['usedSpace'] = $this->bo->vfs->get_size_all( $GLOBALS['phpgw_info']['user']['account_id'] );
		$return['files_count'] = $this->bo->vfs->count_files(array( 'string' => $this->path ));
		
		$quota = $this->bo->vfs->get_quota(array('string' => $this->bo->homedir ));
		
		reset($this->files_array);
		$this->readFilesInfo();

		for ($i = 0; $i != $this->numoffiles; ++$i)
		{
			$files = $this->files_array[$i];

			if ($files['mime_type'] == "Directory") {
				continue;
			}
			/* small keys to safe bandwidth */
			$tuple['name'] = htmlentities($files['name']);
			if ($_SESSION['phpgw_info']['user']['preferences']['filemanager']['viewIcons'] == 1) 
			{
				if ($files['mime_type'] == 'image/png' ||
						  $files['mime_type'] == 'image/gif' ||
						  $files['mime_type'] == 'image/jpg')
				{
					$filename = str_replace('=', '', base64_encode($tuple['name']));
					$pathname = str_replace('=', '', base64_encode($this->path));
					$tuple['icon'] = './index.php?menuaction=filemanager.vfs_functions.summary&file=' . $filename . '&path=' . $pathname;
				}
				else
					$tuple['icon'] = $this->mime_icon($files['mime_type'], 64);
			}
			else
				$tuple['icon'] = $this->mime_icon($files['mime_type']);

			$tuple['type']			= $files['type'];
			$tuple['created']		= $this->vfs_functions->dateString2timeStamp($files['created']);
			$tuple['modified']		= ( $files['modified'] != "" ) ? $this->vfs_functions->dateString2timeStamp($files['modified']) : "";
			$tuple['size']			= $files['size'];
			$tuple['mime_type']		= $files['mime_type'];
			$tuple['pub']			= $files['type'];
			$tuple['createdby_id'] 	= $GLOBALS['phpgw']->accounts->id2name($files['createdby_id']);
			$tuple['modifiedby_id'] = $files['modifiedby_id'] ? $GLOBALS['phpgw']->accounts->id2name($files['modifiedby_id']) : '';
			$tuple['owner']			= $GLOBALS['phpgw']->accounts->id2name($files['owner_id']);
			$tuple['comment']		= $files['comment'];
			$tuple['version']		= $files['version'];
			
			$output[] = $tuple;
		}
		$return['files'] = $output;
		$return['quota']['quotaSize'] = ($quota * 1024 * 1024);
		echo serialize($return);
	}

	function get_folders_list() {
		$this->update_groups();
		$this->groups_applications = array();

		$user_groups = $GLOBALS['phpgw']->accounts->membership();
		foreach ($user_groups as $val) {
			$account_name = $GLOBALS['phpgw']->accounts->id2name($val['account_id']);
			$this->readable_groups[$account_name] = array(
				 'account_id' => $val['account_id'],
				 'account_name' => $account_name
			);
		}

		foreach ($this->readable_groups as $value) {
			$applications = CreateObject('phpgwapi.applications', $value['account_id']);
			$this->groups_applications[$value['account_name']] = $applications->read_account_specific();
		}


		// selectbox for change/move/and copy to
		$this->dirs = $this->all_other_directories();
		foreach ($this->dirs as $dir)
			$return[] = $dir['directory'] . $dir['name'];
		sort(&$return, SORT_STRING);

		echo serialize($return);
	}

	function fileListing() {
		$this->t->set_file(array('filemanager_list_t' => 'main.tpl'));
		$this->t->set_block('filemanager_list_t', 'filemanager_header', 'filemanager_header');
		$this->t->set_block('filemanager_list_t', 'filemanager_footer', 'filemanager_footer');

		if ($this->numoffiles || $this->cwd) {
			$vars['path'] = '<input type="hidden" id="currentPath" value="' . $this->path . '">';
			$vars['css'] = '<link rel="stylesheet" type="text/css" href="filemanager/templates/default/main.css"/>';
			$vars['css'].='<link rel="stylesheet" type="text/css" href="phpgwapi/js/dftree/dftree.css"/>';
			$_SESSION['phpgw_info']['user']['preferences']['filemanager']['lid'] = $GLOBALS['phpgw_info']['user']['account_lid'];
			$vars['preferences'] = '<input type="hidden" id="userPreferences" value=\'' . serialize($_SESSION['phpgw_info']['user']['preferences']['filemanager']) . '\'>';
			// Used for important operations that needs security
			for ($key = ""; strlen($key) < 150; $key .= chr(rand(48, 95)))
				;
			$_SESSION['phpgw_info']['filemanager']['user']['sec_key'] = $key;
			$vars['sec_key'] = '<input type="hidden" id="userKey" value=\'' . $key . '\'>';
			$vars['script'] = '<script>initDrawApi();</script>';

			$vars['new_button'] = $this->toolButton('new', 'createfile', lang('New...'));
			$vars['new_button'].='<input type="hidden" id="newfile_or_dir" name="newfile_or_dir" value="" />';

			// reload button with this url
			$vars['refresh_button'] = $this->toolButton('reload', 'reload', lang('reload'));

			// go up icon when we're not at the top, dont allow to go outside /home = fakebase
			if ($this->path != '/' && $this->path != $this->bo->fakebase) {
				$vars['tools_button'] = $this->toolButton('tools', 'tools', lang('tools'));
			}
			else
				$vars['tools_button'] = "";

			$vars['toolbar1'] = $toolbar;

			if (count($this->messages) > 0) {
				foreach ($this->messages as $msg) {
					$messages.='<span>' . $msg . '</span>';
				}
			}
			$this->messages = NULL;

			$vars['messages'] = $messages;

			$this->t->set_var($vars);
			$this->t->pparse('out', 'filemanager_header');
		}

		$this->t->set_var($vars);
		$this->t->pparse('out', 'filemanager_footer');

		$GLOBALS['phpgw']->common->phpgw_footer();
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	function readFilesInfo() {
		// start files info
		# Read in file info from database to use in the rest of the script
		# $fakebase is a special directory.  In that directory, we list the user's
		# home directory and the directories for the groups they're in
		$this->numoffiles = 0;
		if ($this->path == $this->bo->fakebase) {
			// FIXME this test can be removed
			if (!$this->bo->vfs->file_exists(array('string' => $this->bo->homedir, 'relatives' => array(RELATIVE_NONE)))) {
				$this->bo->vfs->mkdir(array('string' => $this->bo->homedir, 'relatives' => array(RELATIVE_NONE)));
			}

			$ls_array = $this->bo->vfs->ls(array(
							'string' => $this->bo->homedir,
							'relatives' => array(RELATIVE_NONE),
							'checksubdirs' => False,
							'nofiles' => True
					  ));

			$this->files_array[] = $ls_array[0];
			$this->numoffiles++;

			reset($this->readable_groups);
			while (list($num, $group_array) = each($this->readable_groups)) {
				# If the group doesn't have access to this app, we don't show it
				/* if(!$this->groups_applications[$group_array['account_name']][$this->bo->appname]['enabled'])
				  {
				  continue;
				  }
				 */


				if (!$this->bo->vfs->file_exists(array('string' => $this->bo->fakebase . '/' . $group_array['account_name'], 'relatives' => array(RELATIVE_NONE)))) {
					$this->bo->vfs->override_acl = 1;
					$this->bo->vfs->mkdir(array(
						 'string' => $this->bo->fakebase . '/' . $group_array['account_name'],
						 'relatives' => array(RELATIVE_NONE)
					));

					// FIXME we just created a fresh group dir so we know there nothing in it so we have to remove all existing content


					$this->bo->vfs->override_acl = 0;

					$this->bo->vfs->set_attributes(array('string' => $this->bo->fakebase . '/' . $group_array['account_name'], 'relatives' => array(RELATIVE_NONE), 'attributes' => array('owner_id' => $group_array['account_id'], 'createdby_id' => $group_array['account_id'])));
				}

				$ls_array = $this->bo->vfs->ls(array('string' => $this->bo->fakebase . '/' . $group_array['account_name'], 'relatives' => array(RELATIVE_NONE), 'checksubdirs' => False, 'nofiles' => True));

				$this->files_array[] = $ls_array[0];

				$this->numoffiles++;
			}
		} else {
			$ls_array = $this->bo->vfs->ls(array(
							'string' => $this->path,
							'relatives' => array(RELATIVE_NONE),
							'checksubdirs' => False,
							'nofiles' => False,
							'orderby' => $this->criteria,
							'otype' => $this->otype,
							'limit' => $this->limit,
							'offset' => $this->offset
					  ));

			while (list($num, $file_array) = each($ls_array)) {
				$this->numoffiles++;
				$this->files_array[] = $file_array;
			}
		}

		if (!is_array($this->files_array)) {
			$this->files_array = array();
		}
		// end file count
	}

	function removedir() {
		//$toRemove = $this->path ^ $_SESSION['phpgw_info']['filemanager']['user']['sec_key'];
		$toRemove = $this->path;

		if ($this->bo->vfs->rm(array('string' => $toRemove, 'relatives' => array(RELATIVE_NONE))))
			echo "True";
		else
			echo "False";
	}

	function createdir()
	{
		if ($this->bo->badchar = $this->bo->bad_chars($this->filename, True, True)) {
			echo lang('Error:') . $this->bo->html_encode(lang('Directory names cannot contain "%1"', $badchar), 1);
			return;
		}
		/* TODO is this right or should it be a single $ ? */
		if ($this->filename[strlen($this->filename) - 1] == ' ' || $this->filename[0] == ' ') {
			echo lang('Error:') . lang('Cannot create directory because it begins or ends in a space');
		}

		$ls_array = $this->bo->vfs->ls(array(
						'string' => $this->path . '/' . $this->filename,
						'relatives' => array(RELATIVE_NONE),
						'checksubdirs' => False,
						'nofiles' => True
				  ));

		$fileinfo = $ls_array[0];

		if ($fileinfo['name'])
		{
			if ($fileinfo['mime_type'] != 'Directory') {
				echo lang('Error:') . lang('%1 already exists as a file', $fileinfo['name']);
			} else {
				echo lang('Error:') . lang('Directory %1 already exists', $fileinfo['name']);
			}
		}
		else
		{
			$this->bo->vfs->cd(array('string' => $this->path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

			if ($this->bo->vfs->mkdir(array('string' => $this->filename)))
			{
				echo "True";
				//echo lang('Created directory %1', $this->disppath . '/' . $this->filename);
			}
			else
			{
				echo lang('Error:') . lang('Could not create %1', $this->disppath . '/' . $this->filename);
			}
		}
		
	}

	function getReturnExecuteForm() {
		$response = $_SESSION['response'];

		unset($_SESSION['response']);

		echo $response;
	}

	function showUploadboxes()
	{
		$notify = CreateObject('filemanager.notifications');

		$var = array(
			 'change_upload_boxes'	=> lang('Show'),
			 'form_action'			=> $GLOBALS['phpgw']->link('/filemanager/inc/upload.php'),
			 'emails_to'				=> $notify->EmailsToSend($GLOBALS['phpgw']->preferences->values['email']),
			 'lang_file'				=> lang('File(s)'),
			 'lang_comment'			=> lang('Comment(s)'),
			 'lang_advanced_upload'	=> lang('Advanced Upload'),
			 'lang_delete'			=> lang('delete'),
			 'lang_upload'			=> lang('Upload files'),
			 'max_size'				=> lang('The maximum size for each file is %1MB', ($this->current_config['filemanager_Max_file_size'])),
			 'path'					=> $this->path
		);

		print( serialize($var));
	}

	/* create textfile */
	function createfile() 
	{
		$this->filename = $this->newfile_or_dir;
		if ($this->filename) {
			if ($badchar = $this->bo->bad_chars($this->filename, True, True)) {
				$this->messages[] = lang('Error:') . lang('File names cannot contain "%1"', $badchar);

				$this->fileListing();
			}

			if ($this->bo->vfs->file_exists(array(
							'string' => $this->filename,
							'relatives' => array(RELATIVE_ALL)
					  ))) {
				$this->messages[] = lang('Error:') . lang('File %1 already exists. Please edit it or delete it first.', $this->filename);
				$this->fileListing();
			}

			if ($this->bo->vfs->touch(array(
							'string' => $this->filename,
							'relatives' => array(RELATIVE_ALL)
					  ))) {
				$this->edit = 1;
				$this->numoffiles++;
				$this->edit();
			} else {
				$this->messages[] = lang('Error:') . lang('File %1 could not be created.', $this->filename);
				$this->fileListing();
			}
		}
	}

	# Handle Editing files

	function edit() {
		if ($this->filename) {
			if (!$this->vfs_functions->verifyLock($this->path . '/' . $this->filename, RELATIVE_NONE)) {
				$GLOBALS['phpgw']->redirect('/index.php');
			}
			$ls_array = $this->bo->vfs->ls(array(
							'string' => $this->path . '/' . $this->filename,
							'relatives' => array(RELATIVE_NONE),
							'checksubdirs' => False,
							'nofiles' => True
					  ));
			$this->bo->vfs->touch(array(
				 'string' => $this->path . '/' . $this->filename,
				 'relatives' => array(RELATIVE_NONE)
			));


			if ($ls_array[0]['mime_type']) {
				$mime_type = $ls_array[0]['mime_type'];
			} elseif ($this->prefs['viewtextplain']) {
				$mime_type = 'text/plain';
			}
			$editable = array('', 'text/plain', 'text/csv', 'text/html', 'text/text', 'message/rfc822');

			if (!in_array($mime_type, $editable)) {
				$this->messages[] = lang('Error:') . lang('Impossible to edit this file');
				$this->readFilesInfo();
				$this->fileListing();
				return;
			}
		}

		$this->readFilesInfo();

		if ($mime_type == 'text/html')
			$this->t->set_file(array('filemanager_edit' => 'edit_html.tpl'));
		else
			$this->t->set_file(array('filemanager_edit' => 'edit_file.tpl'));

		$this->t->set_block('filemanager_edit', 'row', 'row');


		$vars['refresh_script'] = "<script src='filemanager/js/refresh.js'></script>";

		$vars['preview_content'] = '';
		if ($this->edit_file) {
			$this->edit_file_content = stripslashes($this->edit_file_content);
		}

		if ($this->edit_preview_x) {
			$content = $this->edit_file_content;

			$vars['lang_preview_of'] = lang('Preview of %1', $this->path . '/' . $edit_file);

			$vars['preview_content'] = nl2br($content);
		} elseif ($this->edit_save_x || $this->edit_save_done_x) {
			$content = $this->edit_file_content;
			//die( $content);
			if ($this->bo->vfs->write(array(
							'string' => $this->path . '/' . $this->edit_file,
							'relatives' => array(RELATIVE_NONE),
							'content' => $content
					  ))) {
				$this->messages[] = lang('Saved %1', $this->path . '/' . $this->edit_file);

				if ($this->edit_save_done_x) {
					$this->readFilesInfo();
					$this->fileListing();
					exit;
				}
			} else {
				$this->messages[] = lang('Could not save %1', $this->path . '/' . $this->edit_file);
			}
		}

		# If we're in preview or save mode, we only show the file
		# being previewed or saved
		if ($this->edit_file && ($this->filename != $this->edit_file)) {
			continue;
		}

		if ($this->filename && $this->bo->vfs->file_exists(array(
						'string' => $this->filename,
						'relatives' => array(RELATIVE_ALL)
				  ))) {
			if ($this->edit_file) {
				$content = stripslashes($this->edit_file_content);
			} else {
				$content = $this->bo->vfs->read(array('string' => $this->filename));
			}
			$vars['form_action'] = $GLOBALS['phpgw']->link('/index.php', 'menuaction=filemanager.uifilemanager.index', 'path=' . $this->path);
			$vars['edit_file'] = $this->filename;
			# We need to include all of the fileman entries for each file's form,
			# so we loop through again
			for ($i = 0; $i != $this->numoffiles; ++$i) {
				if ($this->filename)
					$value = 'value="' . $this->filename . '"';
				$vars['filemans_hidden'] = '<input type="hidden" name="filename" ' . $value . ' />';
			}
			$vars['file_content'] = $content;

			$vars['buttonPreview'] = $this->inputButton('edit_preview', 'edit_preview', lang('Preview %1', $this->bo->html_encode($this->fileman[0], 1)));
			$vars['buttonSave'] = $this->inputButton('edit_save', 'save', lang('Save %1', $this->bo->html_encode($this->filename, 1)));
			$vars['buttonDone'] = $this->inputButton('edit_save_done', 'ok', lang('Save %1, and go back to file listing ', $this->bo->html_encode($this->filename, 1)));
			$vars['buttonCancel'] = $this->inputButton('edit_cancel', 'cancel', lang('Cancel editing %1 without saving', $this->bo->html_encode($this->filename, 1)));

			if ($mime_type == 'text/html') {
				$vars['fck_edit'] = '<script type="text/javascript" src="./library/ckeditor/ckeditor.js"></script>
					<textarea cols="80" id="edit_file_content" name="edit_file_content" rows="10">' . $content . '</textarea>
						<script type="text/javascript"> CKEDITOR.replace( \'edit_file_content\',{
removePlugins : \'elementspath\',
skin : \'moono_blue\',
toolbar : [["Source","Preview","-","Cut","Copy","Paste","-","Print",
"Undo","Redo","-","Find","Replace","-","SelectAll" ],
["Table","HorizontalRule","Smiley","SpecialChar","PageBreak","-","Bold",
"Italic","Underline","Strike","-","Subscript","Superscript",
"NumberedList","BulletedList","-","Outdent","Indent","Blockquote",
"JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock",
"Link", "TextColor","BGColor","Maximize"],
["Styles","Format","Font","FontSize"]]
				});</script>';
			}


			$this->t->set_var($vars);
			$this->t->parse('rows', 'row');
			$this->t->pparse('out', 'row');
		}
	}

	function history() 
	{
		if ( $this->file)
		{ 
			// FIXME this-file is never defined
			$journal_array = $this->bo->vfs->get_journal(array(
							'string' => $this->file, //FIXME
							'relatives' => array(RELATIVE_ALL)
		
			 ));

			if ( is_array($journal_array) ) 
			{
				$historyFile = array();

				while ( list($num, $journal_entry) = each($journal_array) ) 
				{
					$historyFile[] = array(
											"created"	=> $this->vfs_functions->dateString2timeStamp($journal_entry['created']),
											"version"	=> $journal_entry['version'],
											"who"		=> $GLOBALS['phpgw']->accounts->id2name($journal_entry['owner_id']),
											"operation"	=> 	$journal_entry['comment']
					);
				}
				
			echo serialize( $historyFile );
				$GLOBALS['phpgw']->common->phpgw_footer();
				$GLOBALS['phpgw']->common->phpgw_exit();
			} 
			else
			{
				echo lang('No version history for this file/directory');
			}
		}
	}

	function view() {
		if (!$this->bo->vfs->acl_check(array(
						'string' => $this->path,
						'relatives' => array(RELATIVE_NONE),
						'operation' => PHPGW_ACL_READ
				  ))) {
			$this->messages[] = lang("You have no permission to access this file");
			header('Location:' . $this->encode_href('inc/index.php?menuaction=filemanager.uifilemanager.index', '&path=' . base64_encode($this->bo->homedir)));

			return;
		}
		if ($this->file) { //FIXME
			$ls_array = $this->bo->vfs->ls(array(
							'string' => $this->path . '/' . $this->file, //FIXME
							'relatives' => array(RELATIVE_NONE),
							'checksubdirs' => False,
							'nofiles' => True
					  ));
			if ($ls_array[0]['mime_type']) {
				$mime_type = $ls_array[0]['mime_type'];
			} elseif ($this->prefs['viewtextplain']) {
				$mime_type = 'text/plain';
			}
			$viewable = array('text/plain', 'text/csv', 'text/html',
				 'text/text', 'image/jpeg', 'image/png', 'image/gif',
				 'audio/mpeg', 'video/mpeg');

			if (in_array($mime_type, $viewable)) {
				/* Note: if you put application/octet-stream you force download */
				header('Content-type: ' . $mime_type);
				header('Content-disposition: filename="' . addslashes($this->file) . '"');
				Header("Pragma: public");
			} else {
				$GLOBALS['phpgw']->browser->content_header($this->file, $mime_type, $ls_array[0]['size']);
			}
			if ($ls_array[0]['size'] < 10240) {
				echo $this->bo->vfs->read(array(
					 'string' => $this->path . '/' . $this->file, //FIXME
					 'relatives' => array(RELATIVE_NONE)
				));
			} else {
				$this->bo->vfs->print_content(array(
					 'string' => $this->path . '/' . $this->file,
					 'relatives' => array(RELATIVE_NONE)
						  )
				);
			}
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}

	function export()
	{
		if ($this->file)
		{
			$ls_array = $this->bo->vfs->ls(array(
							'string' => $this->path . '/' . $this->file,
							'relatives' => array(RELATIVE_NONE),
							'checksubdirs' => False,
							'nofiles' => True
					  ));

			$mime_type = $ls_array[0]['mime_type'];
			
			$formats = array('text/html');
			
			if (!in_array($mime_type, $formats))
			{
				echo lang('Impossible to export this file');
				return False;
			}
			
			$content = $this->bo->vfs->read(array('string' => $this->path . '/' . $this->file,
							'relatives' => array(RELATIVE_NONE)
					  ));

			include_once('filemanager/tp/dompdf/dompdf_config.inc.php');
			
			$dompdf = new DOMPDF();
			$dompdf->load_html($content);
			$dompdf->set_paper($this->prefs['pdf_paper_type'], $this->prefs['pdf_type']);
			/* Would be nice to implement 'Title','Subject','Author','Creator','CreationDate' */
			$dompdf->render();
			$dompdf->stream(strtok($this->file, '.') . ".pdf");
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}

	//give back an array with all directories except current and dirs that are not accessable
	function all_other_directories() {
		# First we get the directories in their home directory
		$dirs = array();
		$dirs[] = array('directory' => $this->bo->fakebase, 'name' => $this->bo->userinfo['account_lid']);

		$tmp_arr = array(
			 'string' => $this->bo->homedir,
			 'relatives' => array(RELATIVE_NONE),
			 'checksubdirs' => True,
			 'mime_type' => 'Directory'
		);

		$ls_array = $this->bo->vfs->ls($tmp_arr, True);

		while (list($num, $dir) = each($ls_array)) {
			$dirs[] = $dir;
		}


		# Then we get the directories in their readable groups' home directories
		reset($this->readable_groups);
		while (list($num, $group_array) = each($this->readable_groups)) {
			// Don't list directories for groups that don't exists
			$test = $this->bo->vfs->get_real_info(array('string' => $this->bo->fakebase . '/' . $group_array['account_name'],
							'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if ($test['mime_type'] != 'Directory') {
				continue;
			}

			$dirs[] = array('directory' => $this->bo->fakebase, 'name' => $group_array['account_name']);

			$tmp_arr = array(
				 'string' => $this->bo->fakebase . '/' . $group_array['account_name'],
				 'relatives' => array(RELATIVE_NONE),
				 'checksubdirs' => True,
				 'mime_type' => 'Directory'
			);

			$ls_array = $this->bo->vfs->ls($tmp_arr, True);
			while (list($num, $dir) = each($ls_array)) {
				$dirs[] = $dir;
			}
		}
		reset($dirs);
		while (list($num, $dir) = each($dirs)) {
			if (!$dir['directory']) {
				continue;
			}

			# So we don't display //
			if ($dir['directory'] != '/') {
				$dir['directory'] .= '/';
			}
			$return[] = $dir;
		}
		return $return;
	}

	function update_groups() {
		# Get their readable groups to be used throughout the script
		$acl = array();
		$groups = array();
		$acl = $GLOBALS['phpgw']->acl->get_ids_for_location($GLOBALS['phpgw_info']['user']['account_id'], 1, 'filemanager');
		if (is_array($acl))
			foreach ($acl as $key => $value) {
				$info = array();
				$info = $GLOBALS['phpgw']->accounts->get_account_data($value);
				$groups[$key]['account_id'] = $value;
				$groups[$key]['account_lid'] = $info[$value]['lid'];
				$groups[$key]['account_name'] = $info[$value]['firstname'];
				$groups[$key]['account_lastname'] = $info[$value]['lastname'];
				$groups[$key]['account_fullname'] = $info[$value]['fullname'];
			}
		$this->readable_groups = array();
		while (list($num, $account) = each($groups)) {
			if ($this->bo->vfs->acl_check(array('owner_id' => $account['account_id'], 'operation' => PHPGW_ACL_READ))) {
				$this->readable_groups[$account['account_lid']] = Array('account_id' => $account['account_id'], 'account_name' => $account['account_lid']);
			}
		}
	}

	function search() {
		/* TODO this is a primitive search */
		$this->update_groups();
		$this->dirs = $this->all_other_directories();
		$path = $this->path;
		if (strlen($this->text) > 3) {
			$this->text = strtoupper($this->text);
			foreach ($this->dirs as $elem) {
				$this->path = $elem['directory'] . $elem['name'];
				reset($this->files_array);
				$this->readFilesInfo();
                $files_array_count = count($this->files_array);
				for ($i = 0; $i < $files_array_count; ++$i) {
					$comment = strtoupper($this->files_array[$i]['comment']);
					$name = strtoupper($this->files_array[$i]['name']);
					if (strstr($name, $this->text) ||
							  strstr($comment, $this->text)) {
						$return[$this->files_array[$i]['directory'] . $name] = $this->files_array[$i];
						$return[$this->files_array[$i]['directory'] . $name]['icon'] = $this->mime_icon($this->files_array[$i]['mime_type']);
					}
				}
				if (count($return) > 50) {
					$return = array_slice($return, 0, 50);
					break;
				}
			}
		}
		echo serialize(array_values($return));
	}

	/* seek icon for mimetype else return an unknown icon */

	function mime_icon($mime_type, $size=16) {
		if (!$mime_type)
			$mime_type = 'unknown';

		$mime_type = str_replace('/', '_', $mime_type);

		$img = $GLOBALS['phpgw']->common->image('filemanager', 'mime' . $size . '_' . strtolower($mime_type));
		if (!$img)
			$img = $GLOBALS['phpgw']->common->image('filemanager', 'mime' . $size . '_unknown');

		return $img;
	}

	function toolButton($link, $img='', $description='') {
		$image = $GLOBALS['phpgw']->common->image('filemanager', 'button_' . strtolower($img));

		if ($img) {
			return '<div name="' . $link . '" class="toolButton" onclick="toolbar.control(\'' . $link . '\',this);" title="' . $description . '"><img src="' . $image . '" alt="' . $description . '"/><small>' . $description . '</small></div>';
		}
	}

	function inputButton($name, $img='', $description='') {
		$image = $GLOBALS['phpgw']->common->image('filemanager', 'button_' . strtolower($img));

		if ($img) {
			return '<td class="" align="center" valign="middle" height="28" width="70">
			<input title="' . $description . '" name="' . $name . '" type="image" alt="' . $name . '" src="' . $image . '" value="clicked" /><br><small>' . $description . '</small>
			</td>';
		}
	}

	function html_form_input($type = NULL, $name = NULL, $value = NULL, $maxlength = NULL, $size = NULL, $checked = NULL, $string = '', $return = 1) {
		$text = ' ';
		if ($type != NULL && $type) {
			if ($type == 'checkbox') {
				$value = $this->bo->string_encode($value, 1);
			}
			$text .= 'type="' . $type . '" ';
		}
		if ($name != NULL && $name) {
			$text .= 'name="' . $name . '" ';
		}
		if ($value != NULL && $value) {
			$text .= 'value="' . $value . '" ';
		}
		if (is_int($maxlength) && $maxlength >= 0) {
			$text .= 'maxlength="' . $maxlength . '" ';
		}
		if (is_int($size) && $size >= 0) {
			$text .= 'size="' . $size . '" ';
		}
		if ($checked != NULL && $checked) {
			$text .= 'checked ';
		}

		return '<input' . $text . $string . '>';
	}

	function html_form_option($value = NULL, $displayed = NULL, $selected = NULL, $return = 0) {
		$text = ' ';
		if ($value != NULL && $value) {
			$text .= ' value="' . $value . '" ';
		}
		if ($selected != NULL && $selected) {
			$text .= ' selected';
		}
		return '<option' . $text . '>' . $displayed . '</option>';
	}

	function encode_href($href = NULL, $args = NULL, $extra_args) {
		$href = $this->bo->string_encode($href, 1);
		$all_args = $args . '&' . $this->bo->string_encode($extra_args, 1);

		$address = $GLOBALS['phpgw']->link($href, $all_args);

		return $address;
	}

	function html_link($href = NULL, $args = NULL, $extra_args, $text = NULL, $return = 1, $encode = 1, $linkonly = 0, $target = NULL) {
		//	unset($encode);
		if ($encode) {
			$href = $this->bo->string_encode($href, 1);
			$all_args = $args . '&' . $this->bo->string_encode($extra_args, 1);
		} else {
			//				$href = $this->bo->string_encode($href, 1);
			$all_args = $args . '&' . $extra_args;
		}
		###
		# This decodes / back to normal
		###
		//			$all_args = preg_replace("/%2F/", "/", $all_args);
		//			$href = preg_replace("/%2F/", "/", $href);

		/* Auto-detect and don't disturb absolute links */
		if (!preg_match("|^http(.{0,1})://|", $href)) {
			//Only add an extra / if there isn't already one there
			// die(SEP);
			if (!($href[0] == SEP)) {
				$href = SEP . $href;
			}

			/* $phpgw->link requires that the extra vars be passed separately */
			//				$link_parts = explode("?", $href);
			$address = $GLOBALS['phpgw']->link($href, $all_args);
			//				$address = $GLOBALS['phpgw']->link($href);
		} else {
			$address = $href;
		}

		/* If $linkonly is set, don't add any HTML */
		if ($linkonly) {
			$rstring = $address;
		} else {
			if ($target) {
				$target = 'target=' . $target;
			}

			$text = trim($text);
			$rstring = '<a href="' . $address . '" ' . $target . '>' . $text . '</a>';
		}

		return($this->bo->eor($rstring, $return));
	}

	function html_table_begin($width = NULL, $border = NULL, $cellspacing = NULL, $cellpadding = NULL, $rules = NULL, $string = '', $return = 0) {
		if ($width != NULL && $width) {
			$width = "width=$width";
		}
		if (is_int($border) && $border >= 0) {
			$border = "border=$border";
		}
		if (is_int($cellspacing) && $cellspacing >= 0) {
			$cellspacing = "cellspacing=$cellspacing";
		}
		if (is_int($cellpadding) && $cellpadding >= 0) {
			$cellpadding = "cellpadding=$cellpadding";
		}
		if ($rules != NULL && $rules) {
			$rules = "rules=$rules";
		}

		$rstring = "<table $width $border $cellspacing $cellpadding $rules $string>";
		return($this->bo->eor($rstring, $return));
	}

	function html_table_end($return = 0) {
		$rstring = "</table>";
		return($this->bo->eor($rstring, $return));
	}

	function html_table_row_begin($align = NULL, $halign = NULL, $valign = NULL, $bgcolor = NULL, $string = '', $return = 0) {
		if ($align != NULL && $align) {
			$align = "align=$align";
		}
		if ($halign != NULL && $halign) {
			$halign = "halign=$halign";
		}
		if ($valign != NULL && $valign) {
			$valign = "valign=$valign";
		}
		if ($bgcolor != NULL && $bgcolor) {
			$bgcolor = "bgcolor=$bgcolor";
		}
		$rstring = "<tr $align $halign $valign $bgcolor $string>";
		return($this->bo->eor($rstring, $return));
	}

	function html_table_row_end($return = 0) {
		$rstring = "</tr>";
		return($this->bo->eor($rstring, $return));
	}

	function html_table_col_begin($align = NULL, $halign = NULL, $valign = NULL, $rowspan = NULL, $colspan = NULL, $string = '', $return = 0) {
		if ($align != NULL && $align) {
			$align = "align=$align";
		}
		if ($halign != NULL && $halign) {
			$halign = "halign=$halign";
		}
		if ($valign != NULL && $valign) {
			$valign = "valign=$valign";
		}
		if (is_int($rowspan) && $rowspan >= 0) {
			$rowspan = "rowspan=$rowspan";
		}
		if (is_int($colspan) && $colspan >= 0) {
			$colspan = "colspan=$colspan";
		}

		$rstring = "<td $align $halign $valign $rowspan $colspan $string>";
		return($this->bo->eor($rstring, $return));
	}

	function html_table_col_end($return = 0) {
		$rstring = "</td>";
		return($this->bo->eor($rstring, $return));
	}

}
