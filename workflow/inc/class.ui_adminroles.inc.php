<?php
require_once(dirname(__FILE__) . SEP . 'class.WorkflowUtils.inc.php');
require_once('engine' . SEP . 'config.egw.inc.php');
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class ui_adminroles extends WorkflowUtils
{
	/**
	 * @var array $public_functions public functions
	 * @access public
	 */
	var $public_functions = array(
		'form'	=> true
	);

	/**
	 * @var object $process_manager Process manager object
	 * @access public
	 */
	var $process_manager;
	/**
	 * @var object $activity_manager Activity manager object
	 * @access public
	 */
	var $activity_manager;

	/**
	 * @var array $workflow_acl
	 * @access public
	 */
	var $workflow_acl;

	/**
	 * Constructor
	 * @access public
	 * @return object
	 */
	function ui_adminroles()
	{
		parent::WorkflowUtils();

		$this->workflow_acl = Factory::getInstance('workflow_acl');
		$denyAccess = true;
		if ($this->workflow_acl->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']))
		{
			/* the user is an Expresso/Workflow admin */
			$denyAccess = false;
		}
		else
		{
			if ($GLOBALS['phpgw']->acl->check('admin_workflow', 1, 'workflow'))
			{
				/* check if the user can admin the informed process */
				if ($this->wf_p_id != 0)
					$denyAccess = !$this->workflow_acl->check_process_access($GLOBALS['phpgw_info']['user']['account_id'], $this->wf_p_id);
				else
					$denyAccess = false;
			}
		}

		if ($denyAccess)
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminprocesses');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		$this->process_manager	= Factory::getInstance('workflow_processmanager');
		$this->activity_manager	= Factory::getInstance('workflow_activitymanager');
		$this->role_manager	= Factory::getInstance('workflow_rolemanager');
		$this->form_action = $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form');
		
	}
	/**
	 * Show the adminroles form
	 * @access public
	 * @return void
	 */
	function form()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Process Roles');
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$this->t->set_file('admin_roles', 'admin_roles.tpl');

		$this->order		= get_var('order', 'GET', 'wf_name');
		$this->sort			= get_var('sort', 'GET', 'asc');
		$this->sort_mode	= $this->order . '__'. $this->sort;
		$sort_mode2			= get_var('sort_mode2', 'any', 'wf_name__asc');
		$role_id				= (int)get_var('role_id', 'any', 0);

		if (!$this->wf_p_id) die(lang('No process indicated'));
		
		//do we need to check validity, warning high load on database
		$checkvalidity=false;

		// save new role
		if (isset($_POST['save']))
		{
			$this->save_role($role_id, $_POST['name'], $_POST['description']);
			$checkvalidity = true;
		}

		// save new mapping
		if (isset($_POST['save_map']))
		{
			$this->save_mapping($_POST['user'], $_POST['role']);
			$this->message[] = lang('New mapping added');
			$checkvalidity = true;
		}

		// delete roles
		if (isset($_POST['delete_roles']))
		{
			$this->delete_roles(array_keys($_POST['role']));
			$checkvalidity = true;
		}
		
		// delete mappings
		if (isset($_POST['delete_map'])) 
		{
			$this->delete_maps(array_keys($_POST['map']));
		}

		// retrieve process info
		$proc_info = $this->process_manager->get_process($this->wf_p_id);

		// check process validity and show errors if necessary
		if ($checkvalidity) $proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);

		// fill proc_bar
		$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));
		
		// retrieve role info
		if ($role_id || isset($_POST['new_role']))
		{
			$role_info = $this->role_manager->get_role($this->wf_p_id, $_GET['role_id']);
		}
		else
		{
			$role_info = array(
				'name'			=> '',
				'description'	=> '',
				'role_id'		=> 0
			);
		}

		// retrieve all roles info
		$all_roles = $this->role_manager->list_roles($this->wf_p_id, 0, -1, 'wf_name__asc', '');
		//echo "all_roles: <pre>";print_r($all_roles);echo "</pre>";
		
		//collect some messages from used objects
		$this->message[] = $this->activity_manager->get_error(false, _DEBUG);
		$this->message[] = $this->process_manager->get_error(false, _DEBUG);
		$this->message[] = $this->role_manager->get_error(false, _DEBUG);


		// fill the general varibles of the template
		$this->t->set_var(array(
			'message'				=> implode('<br>', array_filter($this->message)),
			'errors'				=> $error_str,
			'form_action_adminroles'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form'),
			'role_info_role_id'		=> $role_info['wf_role_id'],
			'role_info_name'		=> $role_info['wf_name'],
			'role_info_description'	=> $role_info['wf_description'],
			'p_id'					=> $this->wf_p_id,
			'start'					=> $this->start,
		));

		$this->show_process_roles_list($all_roles['data']);

		// build users and roles multiple select boxes
		$this->show_users_roles_selects($all_roles['data']);

		// retrieve and show mappings
		$this->show_mappings();

		$this->translate_template('admin_roles');
		$this->t->pparse('output', 'admin_roles');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	/**
	 * Save role
	 * @param int $role_id role identifier
	 * @param string $name role name
	 * @param string $description role description
	 * @access public
	 * @return void
	 */
	function save_role($role_id, $name, $description)
	{
		$vars = array(
			'wf_name'			=> $name,
			'wf_description'	=> $description,
		);
		if ($this->role_manager->replace_role($this->wf_p_id, $role_id, $vars))
		{
			$this->message[] = lang('Role saved');
		}
		else
		{
			$this->message[] = lang('Role not saved (maybe a name collision)');
		}
		
	}
 	/**
 	 * Delet selected roles
 	 * @param array $roles_ids 
 	 * @access public
 	 * @return void
 	 */
	function delete_roles($roles_ids)
	{
		foreach ($roles_ids as $role_id)
		{
			$this->role_manager->remove_role($this->wf_p_id, $role_id);
		}
		$this->message[] = lang('Roles deleted');
	}
	/**
	 * Delete mappings
	 * @param array $mappings
	 * @return void
	 * @access public
	 */
	function delete_maps($mappings)
	{
					foreach($mappings as $map)
					{
								 $pos = strpos($map,":::");
								 $user=substr($map,0,$pos);
								 $role_id=substr($map,$pos+3);
								 $this->role_manager->remove_mapping($user,$role_id);
					}
		$this->message[] = lang('Mappings deleted');
	}
	/**
	 * Show mappings
	 * @return void
	 * @access public
	 */
	function show_mappings()
	{
		$this->t->set_block('admin_roles', 'block_list_mappings', 'list_mappings');
		$mappings = $this->role_manager->list_mappings($this->wf_p_id, $this->start, -1, $this->sort_mode, '');
		//echo "mappings: <pre>";print_r($mappings);echo "</pre>";
		if (!count($mappings['data'])) {
			$this->t->set_var('list_mappings', '<tr><td colspan="3" align="center">'. lang('There are no mappings defined for this process')  .'</td></tr>');
		}
		else {
			/* load the LDAP information */
			$cachedLDAP = Factory::getInstance('CachedLDAP');
			$tmpLDAP = &Factory::getInstance('WorkflowLDAP');
			$userContext  = $tmpLDAP->getUserContext();
			$groupContext = $tmpLDAP->getGroupContext();

			foreach ($mappings['data'] as $mapping)
			{
				if ($mapping['wf_account_type'] == 'g') {
					$sri = ldap_search(Factory::getInstance('WorkflowObjects')->getLDAP(),
									$groupContext,
									'(&(gidnumber=' . (int)$mapping['wf_user'] . ')(phpgwAccountType=g))');

					$allValues = ldap_get_entries(Factory::getInstance('WorkflowObjects')->getLDAP(), $sri);

					$cname = $GLOBALS['phpgw']->translation->convert($allValues[0]['cn'][0],'utf-8');
					$glabel = '[' . lang('Group') . ']';
				}
				else {
					$entry = $cachedLDAP->getEntryByID($mapping['wf_user']);
					$cname = $GLOBALS['phpgw']->translation->convert($entry['cn'],'utf-8');
					$glabel = (!empty($entry['uid'])) ? '[' . $entry['uid'] . ']' : '';
					if (is_null($entry['last_update']))
						$glabel = '<font color="red">(inativo)</font>';
				}

				$this->t->set_var(array(
					'map_user_id'	=> $mapping['wf_user'],
					'map_role_id'	=> $mapping['wf_role_id'],
					'map_role_name'	=> $mapping['wf_name'],
					'map_user_name'	=> $cname . ' ' . $glabel,
				));
				$this->t->parse('list_mappings', 'block_list_mappings', true);
			}
		}
	}
	/**
	 * Save mapping
	 * @param array users list of users
	 * @param array $roles list of roles
	 * @access public
	 * @return void
	 */
	function save_mapping($users, $roles)
	{
		foreach ($users as $user)
		{
			$account_type   = $user{0};
			$user           = substr($user, 1);
			foreach ($roles as $role)
			{
				$this->role_manager->map_user_to_role($this->wf_p_id, $user, $role, $account_type);
			}
		}
	}
	/**
	 * Show users roles selects
	 * @param string $all_roles_data
	 * @return
	 */
	function show_users_roles_selects($all_roles_data)
	{
		$templateServer = &Factory::getInstance('TemplateServer');
		$imgaddusers = $templateServer->generateImageLink('add_group.png');
		$imgdelusers = $templateServer->generateImageLink('delete_group.png');

		$this->t->set_var(array(
			'src_img_add_users' => $imgaddusers,
			'src_img_del_users' => $imgdelusers
		));

		$this->t->set_block('admin_roles', 'block_select_roles', 'select_roles');
		foreach ($all_roles_data as $role)
		{
			$this->t->set_var(array(
				'select_role_id'	=> $role['wf_role_id'],
				'select_role_name'	=> $role['wf_name']
			));
			$this->t->parse('select_roles', 'block_select_roles', true);
		}
	}
	/**
	 * Show process roles list
	 * @param array $all_roles_data
	 * @return void
	 */
	function show_process_roles_list($all_roles_data)
	{
		$this->t->set_block('admin_roles', 'block_process_roles_list', 'process_roles_list');
		$this->translate_template('block_process_roles_list');

		foreach ($all_roles_data as $role)
		{
			$this->t->set_var(array(
				'all_roles_role_id'		=> $role['wf_role_id'],
				'all_roles_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&sort_mode='. $this->sort_mode .'&start='. $this->start .'&find='. $find .'&p_id='. $this->wf_p_id .'&sort_mode2='. $sort_mode2 .'&role_id='. $role['wf_role_id']),
				'all_roles_name'		=> $role['wf_name'],
				'all_roles_description'	=> $role['wf_description'],
				'color_line'			=> $this->nextmatchs->alternate_row_color($tr_color),
			));
			$this->t->parse('process_roles_list', 'block_process_roles_list', true);
		}
		if (!count($all_roles_data)) $this->t->set_var('process_roles_list', '<tr><td colspan="3">'. lang('There are no roles defined for this process') .'</td></tr>');

	}
}
?>
