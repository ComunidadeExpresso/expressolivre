<?php

require_once dirname(__FILE__) . SEP . 'class.WorkflowUtils.inc.php';
require_once 'engine' . SEP . 'config.egw.inc.php';
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class ui_adminactivities extends WorkflowUtils
{
	/**
	 * @var array $public_functions Array of public functions 
	 * @access public
	 */
	var $public_functions = array(
		'form'			=> true,
		'show_graph'	=> true
	);
/**
 * @var object $process_manager Process manager
 * @access public 
 */
	var $process_manager;		
/**
 * @var object $activity_manager
 * @access public 
 */
	var $activity_manager;
/**
 * @var object $role_manager
 * @access public 
 */
	var $role_manager;
/**
 * @var $where2 
 * @access public 
 */	
	var $where2;
	/**
	 * @var object $sort_mode2
	 * @access public 
	 */	
	var $sort_mode2;
	/**
	 * @var array $agents agents handling
	 * @access public
	 */
	var $agents=Array();
	/**
	 * @var object $workflow_acl
	 * @access public 
	 */
	var $workflow_acl;
	/**
	 * @var object $fs object for reading process folder  
	 * @access public
	 */
	var $fs; 
	/**
	 * @var array  $invalidFiles invalid files list 
	 * @access public
	 */
	var $invalidFiles = array();	 
	/**
	 * Constructor da classe ui_adminactivities
	 * 
	 * @access public
	 * @return void
	 */	
	function ui_adminactivities()
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

		/* allow regular users to see the process graph */
		if ($_GET['menuaction'] == "workflow.ui_adminactivities.show_graph")
			$denyAccess = false;

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
		$this->fs = &Factory::newInstance('FsUtils'); /* gets instance */

	}
		
	/**
	 * Build the form for Activities Administration
	 * @return void
	 * @access public
	 */
	function form()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Process Activities');
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$this->t->set_file('admin_activities', 'admin_activities.tpl');
		$this->t->set_block('admin_activities', 'block_select_type', 'select_type');
		$this->t->set_block('admin_activities', 'block_activity_roles', 'activity_roles');
		$this->t->set_block('admin_activities', 'block_process_roles', 'process_roles');
		$this->t->set_block('admin_activities', 'block_activity_agents', 'activity_agents');
		$this->t->set_block('admin_activities', 'block_default_roles', 'default_roles');
		$this->t->set_block('admin_activities', 'block_select_agents', 'select_agents');
	
		$proc_info =& $this->process_manager->get_process($this->wf_p_id);
	
		$activity_id		= (int)get_var('activity_id', 'any', 0);
		$name				= get_var('name', 'any', '');
					
		// TODO: not all variables below are still required.  clean up
		
		$description			= get_var('description', 'any', '');
		$type					= get_var('type', 'any', '');
		$is_interactive			= get_var('is_interactive', 'any', '');
		$is_autorouted			= get_var('is_autorouted', 'any', '');
		$default_user      		= get_var('default_user', 'any', '');
		$useagent				= get_var('useagent', 'POST', '');
		$where					= get_var('where', array('GET', 'POST'), '');
		$this->where2			= get_var('where2', 'any', '');
		$find					= get_var('find', 'any', '');
		$find2					= get_var('find2', 'any', '');
		$this->sort_mode2		= get_var('sort_mode2', 'any', '');
		$filter_trans_from		= get_var('filter_trans_from', 'any', '');
		$this->order			= get_var('order', 'GET', 'wf_flow_num');
		$this->sort				= get_var('sort', 'GET', 'asc');
		$this->sort_mode		= $this->order . '__'. $this->sort;
		$menu_path				= get_var('menu_path', 'any', '');
		//roles
		$activity_role_ro		= get_var('activity_role_ro','POST', Array());
		$activity_role_delete	= get_var('activity_role_delete','POST', Array());
		$remove_roles			= get_var('remove_roles', 'POST', false);
		$rolename				= get_var('rolename', 'POST', '');
		$roledescription		= get_var('roledescription', 'POST', '');
		$userole				= get_var('userole', 'POST', '');
		$userole_ro				= get_var('userole_ro', 'POST', 'off');
		$newrole_ro				= get_var('newrole_ro', 'POST', 'off');


		if (!$this->wf_p_id) die(lang('No process indicated'));

		// *************************************   START OF OPERATIONS COMMANDED BY THIS SAME FORM ******************
		// do we need to check validity? do it only if necessary, high load on database
		$checkvalidity = false;

		// add role to process roles
		if( !(empty($rolename)) )
		{
			$rolename = trim($rolename);
			if( strlen($rolename) > 0 ) 
			{
				//second parameter for read-only mode
				$newrole_id = $this->add_process_role($rolename, $roledescription);
				if ($newrole_id)
				{
					$this->message[] = lang('Role added to process');
					if ($activity_id)
					{
						$this->activity_manager->add_activity_role($activity_id, $newrole_id, ($newrole_ro=='on'));
						$this->message[] = lang('Role added to activity');
					}
					$checkvalidity = true;
				}
			}
			else 
			{
				$this->message[] = lang('Invalid role name');
			}
		}

		// remove activity role
		if (!!($remove_roles) && $activity_id)
		{
			foreach ($activity_role_delete as $role_id => $checked_on)
			{
				$this->activity_manager->remove_activity_role($activity_id, $role_id);
				$this->message[] = lang('Activity role #%1 removed', $role_id);
			}
			$this->message[] = $this->activity_manager->get_error(false, _DEBUG);
			$checkvalidity = true;
		}

		// remove activity agent
		if (isset($_GET['remove_agent']) && $activity_id)
		{
			$this->activity_manager->remove_activity_agent($activity_id, $_GET['remove_agent'],true);
			$this->message[] = lang('Activity agent removed');
			$this->message[] = $this->activity_manager->get_error(false, _DEBUG);
		}

		// TODO: activityname need to be valid.  Add a validity checking function?
		// save activity
		if (isset($_POST['save_act']))
		{
			$activity_id = $this->save_activity($activity_id, $name, $description, $type, $default_user, $is_interactive, $is_autorouted, $userole, $userole_ro, $useagent, $rolename, $menu_path);
			if( $activity_id ) 
			{
				if ($newrole_id)
				{
					$this->activity_manager->add_activity_role($activity_id, $newrole_id, ($newrole_ro=='on'));
					$this->message[] = lang('Role added to activity');
				}
				$this->message[] = lang('Activity saved');
			}
			//no checkvalidity, this is done already in ActivityManager
		}

		// delete activity
		if (isset($_POST['delete_act']))
		{
			if( isset($_POST['activities']) ) 
			{
				if ($this->delete_activities(array_keys($_POST['activities']))) $this->message[] = lang('Deletion successful');
				$checkvalidity = true;
			}
		}

		// add transitions
		if (isset($_POST['add_trans']))
		{ 
			$this->message[] = $this->add_transition($_POST['wf_act_from_id'], $_POST['wf_act_to_id']);
			$checkvalidity = true;
		}

		// delete transitions
		if (isset($_POST['delete_tran']))
		{
			$this->delete_transitions($_POST['transition']);
			$checkvalidity = true;
		}

		// *************************************   END OF OPERATIONS COMMANDED BY THIS SAME FORM ******************

		$expandTable = isset($_POST['new_activity']);
		// retrieve activity info and its roles and agents
		if (!$activity_id || isset($_POST['new_activity']))
		{
			$activity_info = array(
				'wf_name'		=> '',
				'wf_description'	=> '',
				'wf_activity_id'	=> 0,
				'wf_is_interactive'	=> true,
				'wf_is_autorouted'	=> false,
				'wf_default_user'       => '*',
				'wf_type'		=> 'activity',
				'wf_menu_path'	=> ''
			);
			$activity_roles = array();
			$activity_agents = array();
		}
		else
		{
			$expandTable = true;
			$activity_info =& $this->activity_manager->get_activity($activity_id);
			$activity_roles =& $this->activity_manager->get_activity_roles($activity_id);
			$activity_agents =& $this->activity_manager->get_activity_agents($activity_id);
			//for all agents we create ui_agent object to handle admin agents displays
			//this array can be already done by the save_activity function, in this case
			// we will just actualize most of the records
			foreach ($activity_agents as $agent)
			{
				if (empty($this->agents[$agent['wf_agent_type']]))
				{
					$ui_agent =& createObject('workflow.ui_agent_'.$agent['wf_agent_type']);
					$ui_agent->load($agent['wf_agent_id']);
					$this->agents[$agent['wf_agent_type']] = $ui_agent;
					unset($ui_agent);
				}
				else
				{
					$this->agents[$agent['wf_agent_type']]->load($agent['wf_agent_id']);
				}
			}
		}

		// fill type filter select box
		$activity_types = array('start', 'end', 'activity', 'switch', 'split', 'join', 'standalone', 'view');
		$filter_type = get_var('filter_type', 'any', '');
		$this->show_select_filter_type($activity_types, $filter_type);
		
		$filter_interactive		= get_var('filter_interactive', 'any', '');
		$activity_interactive = array('y' => lang('Interactive'), 'n'=>lang('Automatic'));
		$this->show_select_filter_interactive($activity_interactive, $filter_interactive);
		
		$filter_autoroute		= get_var('filter_autoroute', 'any', '');
		$activity_autoroute = array('y' => lang('Auto Routed'), 'n'=>lang('Manual'));
		$this->show_select_filter_autoroute($activity_autoroute, $filter_autoroute);
		
		$where = '';
		$wheres = array();
		if( !($filter_type == '') ) 
		{
					$wheres[] = "wf_type = '" .$filter_type. "'";
		}
		if( !($filter_interactive == '') ) 
		{
					$wheres[] = "wf_is_interactive = '" .$filter_interactive. "'";
		}
		if( !($filter_autoroute == '') ) 
		{
			$wheres[] = "wf_is_autorouted = '" .$filter_autoroute. "'";
		}
		if( count($wheres) > 0 ) 
		{
			$where = implode(' and ', $wheres);
		}
		
		if (empty($process_activities)) $process_activities =& $this->activity_manager->list_activities($this->wf_p_id, 0, -1, $this->sort_mode, $find, $where);
		$all_transition_activities_from =& $this->activity_manager->get_transition_activities($this->wf_p_id, 'end');
		$all_transition_activities_to =& $this->activity_manager->get_transition_activities($this->wf_p_id, 'start');
		if ($activity_id) $this->search_transitions_act($process_activities, $activity_id);
		$process_roles =& $this->role_manager->list_roles($this->wf_p_id, 0, -1, 'wf_name__asc', '');
		$agents_list =& $this->process_manager->get_agents();
		$all_process_transitions =& $this->activity_manager->get_process_transitions($this->wf_p_id);
		$process_transitions =& $this->activity_manager->get_process_transitions($this->wf_p_id, $filter_trans_from);
		$process_activities_with_transitions =& $this->activity_manager->get_process_activities_with_transitions($this->wf_p_id);

		// update activities
		if (isset($_POST['update_act']))
		{
			if( is_array($process_activities['data']) && count($process_activities['data']) > 0 )
			{
				$this->update_activities($process_activities, array_keys($_POST['activity_inter']), array_keys($_POST['activity_route']));
				$this->message[] = lang('Activities updated');
			}
		}

		// activate process
		if (isset($_GET['activate_proc']))
		{
			$this->process_manager->activate_process($_GET['activate_proc']);
			$proc_info['wf_is_active'] = 'y';
		}

		// deactivate process
		if (isset($_GET['deactivate_proc']))
		{
			$this->process_manager->deactivate_process($_GET['deactivate_proc']);
			$proc_info['wf_is_active'] = 'n';
		}

		/* warning, heavy database load! */
		/* check process validity and show errors if necessary */
		if ($checkvalidity) $proc_info['wf_is_valid'] = $this->show_errors($this->activity_manager, $error_str);

		// fill proc_bar
		$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));

		//collect some messages from used objects
		$this->message[] = $this->activity_manager->get_error(false, _DEBUG);
		$this->message[] = $this->process_manager->get_error(false, _DEBUG);
		$this->message[] = $this->role_manager->get_error(false, _DEBUG);

		$templateServer = &Factory::getInstance('TemplateServer');

		// fill the general variables of the template
		$this->t->set_var(array(
			'message'				=> implode('<br>', array_filter($this->message)),
			'errors'				=> $error_str,
			'form_details_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
			'form_list_transitions_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
			'p_id'					=> $this->wf_p_id,
			'where'					=> $where,
			'where2'				=> $this->where2,
			'sort_mode'				=> $this->sort_mode,
			'sort_mode2'			=> $this->sort_mode2,
			'find'				=> $find,
			'find2'				=> $find2,
			'expandTable'			=> ($expandTable ? 'true' : 'false'),
			'activity_id'			=> $activity_info['wf_activity_id'],
			'new_act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
			'name'					=> $activity_info['wf_name'],
			'description'			=> $activity_info['wf_description'],
			'checked_interactive'	=> ($activity_info['wf_is_interactive'])? 'checked="checked"' : '',
			'checked_autorouted'	=> ($activity_info['wf_is_autorouted'])? 'checked="checked"' : '',
			'menu_path'				=> $activity_info['wf_menu_path'],
			'img_transition_auto'           => '<img src="'.$templateServer->generateImageLink('transition_interactive.gif') .'" alt="'. lang('transition mode') .'" />',
			'img_interactive'               => '<img src="'.$templateServer->generateImageLink('mini_interactive.gif') .'" alt="'. lang('interactivity') .'" />',
			'img_transition'                => '<img src="'.$templateServer->generateImageLink('transition.gif') .'" alt="'. lang('transitions') .'" />',
			'img_transition_add'            => '<img src="'.$templateServer->generateImageLink('transition_add.gif') .'" alt="'. lang('add transition') .'" />',
			'img_transition_delete'         => '<img src="'.$templateServer->generateImageLink('transition_remove.gif') .'" alt="'. lang('delete transition') .'" />',
			'add_trans_from'		=> $this->build_select_transition('add_tran_from[]', $all_transition_activities_from['data'], true, false, 'from'),
			'add_trans_to'			=> $this->build_select_transition('add_tran_to[]', $all_transition_activities_to['data'], true, false, 'to'),
			'add_a_trans_from'		=> $this->build_select_transition('wf_act_from_id', $all_transition_activities_from['data'], false, false),
			'add_a_trans_to'		=> $this->build_select_transition('wf_act_to_id', $all_transition_activities_to['data'], false, false)
		));

		if( $filter_trans_from ) {
			$this->t->set_var('filter_trans_from', $this->build_select_transition_filtered('filter_trans_from', $process_activities_with_transitions['data'], false, true, $filter_trans_from));
			$this->t->set_var('filter_trans_from_value', $filter_trans_from);
		}
		else {
			$this->t->set_var('filter_trans_from', $this->build_select_transition_filtered('filter_trans_from', $process_activities_with_transitions['data'], false, true, false));				
			$this->t->set_var('filter_trans_from_value', '');
		}

		// show process activities table
		$this->show_process_activities($process_activities['data']);

		
		foreach ($activity_types as $type)
		{
			$this->t->set_var(array(
				'type_value'	=> $type,
				'type_selected'	=> ($activity_info['wf_type'] == $type)? 'selected="selected"' : '',
				'type_name'		=> $type
			));
			$this->t->parse('select_type', 'block_select_type', true);
		}

		// fill activity roles
		if (!$activity_roles)
		{
			$this->t->set_var('activity_roles', '<tr><td colspan="3">'.lang('No roles asociated with this activity').'</td></tr>');
		}
		else
		{
			foreach ($activity_roles as $role)
			{
				$this->t->set_var(array(
					'act_role_name'		=> $role['wf_name'],
					'act_role_id'		=> $role['wf_role_id'],
					'act_role_ro_checked'	=> ($role['wf_readonly'])? 'checked="checked"' : '',
					'act_role_href'		=> $GLOBALS['phpgw']->link('/index.php', array(
							'menuaction'	=> 'workflow.ui_adminactivities.form',
							'where2'	=> $where2,
							'sort_mode2'	=> $sort_mode2,
							'find'		=> $find,
							'where'		=> $where,
							'activity_id'	=> $activity_info['wf_activity_id'],
							'p_id'		=> $this->wf_p_id,
							'remove_role'	=> $role['wf_role_id'],
					)),
					 'lang_delete'		=> lang('delete'),
				));
				$this->t->parse('activity_roles', 'block_activity_roles', True);
			}
			
		}
		
		//general texts about roles
		$this->t->set_var(array(
			'txt_read_only'				=> lang('read-only'),
			'txt_role_name'				=> lang('Role Name'),
			'txt_Remove_selected_roles'		=> lang('remove selected roles'),
			'txt_Use_existing_roles'		=> lang('Use existing roles'),
			)
		);
		
		// fill activity agents
		if (!$activity_agents)
		{
			$this->t->set_var(array(
				'activity_agents'	=> lang('No agents associated with this activity'),
				'agents_config_rows' 	=> '',
			));
		}
		else
		{
			foreach ($activity_agents as $agent)
			{
				$this->t->set_var(array(
					'act_agent_type'	=> $agent['wf_agent_type'],
					'act_agent_href'	=> $GLOBALS['phpgw']->link('/index.php', array(
						'menuaction'	=> 'workflow.ui_adminactivities.form',
						'where2'	=> $where2,
						'sort_mode2'	=> $sort_mode2,
						'find'		=> $find,
						'where'		=> $where,
						'activity_id'	=> $activity_info['wf_activity_id'],
						'p_id'		=> $this->wf_p_id,
						'remove_agent'	=> $agent['wf_agent_id'],
					)),
					'lang_delete'		=> lang('delete'),
				));
				$this->t->parse('activity_agents', 'block_activity_agents', True);
			}
		}
		
		//display agents options
		$this->display_agents_rows();

		// if we have no default user
		if ($activity_info['wf_default_user'] == '*')
			$wf_default_user_desc = '';
		else
			$wf_default_user_desc = $GLOBALS['phpgw']->accounts->id2name($activity_info['wf_default_user'], 'account_lastname');

		$this->t->set_var(array(
			'wf_default_user' => $activity_info['wf_default_user'],
			'wf_default_user_desc' => $wf_default_user_desc
		));

		$this->t->set_var(array(
			'default_user_option_checked_0' => ($activity_info['wf_default_user'][0] != 'p')? "checked='checked'": '',
			'default_user_option_checked_1' => ($activity_info['wf_default_user'][0] == 'p')? "checked='checked'": '',
		));

		// fill process roles
		foreach ($process_roles['data'] as $role)
		{
			$this->t->set_var(array(
				'proc_roleId'	=> $role['wf_role_id'],
				'proc_roleName'	=> $role['wf_name']
			));
			$this->t->parse('process_roles', 'block_process_roles', True);
		}

		// fill process roles for activity default role
		foreach ($process_roles['data'] as $role)
		{
			$role_id = 'p' . $role['wf_role_id'];
			$this->t->set_var(array(
				'proc_roleId'			=> $role_id,
				'proc_role_selected'	=> ($role_id == $activity_info['wf_default_user'])? 'selected="selected"' : '',
				'proc_roleName'			=> $role['wf_name']
			));
			$this->t->parse('default_roles', 'block_default_roles', true);
		}

		// fill agents select
		foreach ($agents_list as $agent)
		{
			$this->t->set_var(array(
				'select_agentType'	=> $agent['wf_agent_type']
			));
			$this->t->parse('select_agents', 'block_select_agents', True);
		}

		// fill list of transitions table
		$this->show_transitions_table($process_transitions);
		$this->t->set_var('filter_type_value', $filter_type);
		$this->t->set_var('filter_interactive_value', $filter_interactive);
		$this->t->set_var('filter_autoroute_value', $filter_autoroute);
		$this->t->set_var('find_value', $find);

		// create graph
		$this->activity_manager->build_process_graph($this->wf_p_id);

		$this->translate_template('admin_activities');
		$this->t->pparse('output', 'admin_activities');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	/**
	* Show select filter type
	* @param array $all_activity_types
	* @param $filter_type 
	* @access public
	* @return void
	*/
	function show_select_filter_type($all_activity_types, $filter_type)
	{
		$this->t->set_block('admin_activities', 'block_select_filter_type', 'select_filter_type');
		$this->t->set_var('selected_filter_type_all', (!($filter_type))? 'selected="selected"' : '');

		foreach ($all_activity_types as $type)
		{
			$this->t->set_var(array(
				'selected_filter_type'	=> ($filter_type == $type)? 'selected="selected"' : '',
				'filter_type_name'	=> lang($type),
				'filter_type_en_name'	=> $type
			));
			$this->t->parse('select_filter_type', 'block_select_filter_type', true);
		}
	}
	
	/**
	* Show select filter type
	* @param array $all_activity_types
	* @param $filter_type 
	* @access public
	* @return void
	*/
	function show_select_filter_interactive($all_activity_interactive, $filter_interactive)
	{
		$this->t->set_block('admin_activities', 'block_select_filter_interactive', 'select_filter_interactive');
		$this->t->set_var('selected_filter_interactive_all', (!($filter_interactive))? 'selected="selected"' : '');

		foreach ($all_activity_interactive as $value=>$name)
		{
			$this->t->set_var(array(
				'selected_filter_interactive'	=> ($filter_interactive == $value)? 'selected="selected"' : '',
				'filter_interactive_name'		=> $name,
				'filter_interactive_value'		=> $value
			));
			$this->t->parse('select_filter_interactive', 'block_select_filter_interactive', true);
		}
	}
	/**
	* Show select filter type
	* @param array $all_activity_types
	* @param $filter_type 
	* @access public
	* @return void
	*/
	function show_select_filter_autoroute($all_activity_autoroute, $filter_autoroute)
	{
		$this->t->set_block('admin_activities', 'block_select_filter_autoroute', 'select_filter_autoroute');
		$this->t->set_var('selected_filter_autoroute_all', (!($filter_autoroute))? 'selected="selected"' : '');

		foreach ($all_activity_autoroute as $value=>$name)
		{
			$this->t->set_var(array(
				'selected_filter_autoroute'	=> ($filter_autoroute == $value)? 'selected="selected"' : '',
				'filter_autoroute_name'		=> $name,
				'filter_autoroute_value'		=> $value
			));
			$this->t->parse('select_filter_autoroute', 'block_select_filter_autoroute', true);
		}
	}
	
	/**
	* Show select filter type
	* @param array $all_activity_types
	* @param $filter_type 
	* @access public
	* @return void
	*/
	function update_activities(&$process_activities, $activities_inter, $activities_route)
	{
		$num_activities = count($process_activities['data']);
		for ($i=0; $i < $num_activities; ++$i)
		{
			$act_id = $process_activities['data'][$i]['wf_activity_id'];
			if ($process_activities['data'][$i]['wf_is_interactive'] == 'y' && !in_array($act_id, $activities_inter))
			{
				$process_activities['data'][$i]['wf_is_interactive'] = 'n';
				$this->activity_manager->set_interactivity($this->wf_p_id, $act_id, 'n');
			}
			if ($process_activities['data'][$i]['wf_is_interactive'] == 'n' && in_array($act_id, $activities_inter))
			{
				$process_activities['data'][$i]['wf_is_interactive'] = 'y';
				$this->activity_manager->set_interactivity($this->wf_p_id, $act_id, 'y');
			}
			if ($process_activities['data'][$i]['wf_is_autorouted'] == 'y' && !in_array($act_id, $activities_route))
			{
				$process_activities['data'][$i]['wf_is_autorouted'] = 'n';
				$this->activity_manager->set_autorouting($this->wf_p_id, $act_id, 'n');
			}
			if ($process_activities['data'][$i]['wf_is_autorouted'] == 'n' && in_array($act_id, $activities_route))
			{
				$process_activities['data'][$i]['wf_is_autorouted'] = 'y';
				$this->activity_manager->set_autorouting($this->wf_p_id, $act_id, 'y');
			}
		}
	}

	/**
	 *  Add a role to the process
	 *  @param $rolename is the role name
	 *  @param $roledescription is the role description
	 *  @return int new role id
	 */
	function add_process_role($rolename, $roledescription)
	{
		$vars = array(
			'wf_name'		=> $rolename,
			'wf_description'	=> $roledescription,
		);
		return $this->role_manager->replace_role($this->wf_p_id, 0, $vars);
	}
	/**
	 *  Search transitions activities
	 *  @param array $process_activities
	 *  @param int $act_id 
	 *  @return int new role id
	 */
	function search_transitions_act(&$process_activities, $act_id)
	{
		for ($i=0; $i < $process_activities['cant']; ++$i)
		{
			$id = $process_activities['data'][$i]['wf_activity_id'];
			$process_activities['data'][$i]['to'] = $this->activity_manager->transition_exists($this->wf_p_id, $act_id, $id)? 'y' : 'n';
			$process_activities['data'][$i]['from'] = $this->activity_manager->transition_exists($this->wf_p_id, $id, $act_id)? 'y' : 'n';
		}
	}
	/**
	 *  Show process activities data
	 *  @param $rolename is the role name
	 *  @param $roledescription is the role description
	 *  @return int new role id
	 */
	function show_process_activities($process_activities_data)
	{
		$this->t->set_block('admin_activities', 'block_process_activities', 'process_activities');
		$this->t->set_var(array(
			'form_process_activities_action'=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
			'left_arrow'		=> $this->nextmatchs->left('index.php', $this->start, $this->total),
			'right_arrow'		=> $this->nextmatchs->right('index.php', $this->start, $this->total),
		));
		$this->translate_template('block_process_activities');

		$this->t->set_block('admin_activities', 'block_process_activities_header', 'process_activities_header');
		$this->t->set_block('admin_activities', 'block_process_activities_footer', 'process_activities_footer');
		if( is_array($process_activities_data) && count($process_activities_data) > 0 )
		{
			$this->t->set_var(array(
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, 'index.php', lang('Name'), array('p_id'=>$this->wf_p_id)),
				'header_type'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_type', $this->order, 'index.php', lang('Type'), array('p_id'=>$this->wf_p_id)),
				'header_interactive'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_interactive', $this->order, 'index.php', lang('Interactive'),  array('p_id'=>$this->wf_p_id)),
				'header_route'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_autorouted', $this->order, 'index.php', lang('Auto routed'),  array('p_id'=>$this->wf_p_id)),
				'header_default_user'	=> lang('Default User')
			));
			$this->translate_template('block_process_activities_header');
			$this->t->parse('process_activities_header', 'block_process_activities_header', True);
			$templateServer = &Factory::getInstance('TemplateServer');
			foreach ($process_activities_data as $activity)
			{
				if($activity['wf_default_user'] == '*' )
				{
					$act_default_user = lang('None');
				}
				else if($activity['wf_default_user'] != '*')
				{
					$act_default_user = $GLOBALS['phpgw']->accounts->id2name($activity['wf_default_user']);
				}
				
				$this->t->set_var(array(
					'act_activity_id'	=> $activity['wf_activity_id'],
					'act_flowNum'		=> $activity['wf_flow_num'],
					'act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $activity['wf_activity_id']),
					'act_name'			=> $activity['wf_name'],
					'no_roles'			=> ($activity['wf_roles'] < 1)? '<small>('.lang('no roles').')</small>' : '',
					'act_icon'			=> $this->act_icon($activity['wf_type'],$activity['wf_is_interactive']),
					'act_inter_checked'	=> ($activity['wf_is_interactive'] == 'y')? 'checked="checked"' : '',
					'act_route_checked'	=> ($activity['wf_is_autorouted'] == 'y')? 'checked="checked"' : '',
					'act_default_user'      => $act_default_user,
					'act_href_edit'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $this->wf_p_id .'&activity_id='. $activity['wf_activity_id']),
					'act_template'		=> ($activity['wf_is_interactive'] == 'y')? '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $this->wf_p_id .'&activity_id='. $activity['wf_activity_id'] .'&template=1') .'"><img src="'. $templateServer->generateImageLink('template.png') .'" alt="' .lang('template') .'" title="' . lang('template') .'" /></a>' : '',
					'img_code'		=> $templateServer->generateImageLink('code.png'),
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),

				));
				$this->t->parse('process_activities', 'block_process_activities', True);
			}
			$this->translate_template('block_process_activities_footer');
			$this->t->parse('process_activities_footer', 'block_process_activities_footer', True);
		}
		else 
		{
			$this->t->set_var('process_activities_header', '');
			$this->t->set_var('process_activities', '<tr><td colspan="7" align="center">'. lang('There are no processes with the current filter')  .'</td></tr>');
			$this->t->set_var('process_activities_footer', '');
		}
	}
/** 
 * Show transitions table
 * @var array $process_transition 
 * @access public
 * @return void
 */
	function show_transitions_table($process_transitions)
	{
		$this->t->set_block('admin_activities', 'block_transitions_table', 'transitions_table');
		$this->t->set_block('admin_activities', 'block_transitions_table_footer', 'transitions_table_footer');
		$this->translate_template('block_transitions_table');
		$this->translate_template('block_transitions_table_footer');

		foreach ($process_transitions as $transition)
		{
			$this->t->set_var(array(
				'trans_actFromId'	=> $transition['wf_act_from_id'],
				'trans_actToId'		=> $transition['wf_act_to_id'],
				'trans_href_from'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $transition['wf_act_from_id']),
				'trans_actFromName'	=> $transition['wf_act_from_name'],
				'trans_arrow'		=> Factory::getInstance('TemplateServer')->generateImageLink('next.gif'),
				'trans_href_to'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $transition['wf_act_to_id']),
				'trans_actToName'	=> $transition['wf_act_to_name'],
				'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
			));
			$this->t->parse('transitions_table', 'block_transitions_table', true);
		}
		if (!count($process_transitions))
		{
			$this->t->set_var('transitions_table', '<tr><td colspan="2" align="center">'. lang('There are no transitions defined')  .'</td></tr>');
			$this->t->set_var('transitions_table_footer', '');
		} 
		else 
		{
			$this->t->parse('transitions_table_footer', 'block_transitions_table_footer', true);
		}
	}

	/**
	 * Build select box with all transitions activities
	 * @param string  $var_name		
	 * @param array   $process_activities_data array of process data
	 * @param boolean $multiple 
	 * @param boolean $show_all show all list 
	 * @param boolean $from_or_to 
	 * @access public
	 * @return string html code with select box
	 */
	function build_select_transition($var_name, $process_activities_data, $multiple=false, 
	$show_all=true, $from_or_to=false)
	{
		//echo "process_activities_data: <pre>";print_r($process_activities_data);echo "</pre>";
		$select_str = "<select name='$var_name'" . (($multiple)? " multiple='multiple' size='5'" : "" ) . ">";
		if ($show_all) $select_str .= '<option value="">'. lang('All') .'</option>';
		
		foreach ($process_activities_data as $activity)
		{
			if ($from_or_to && isset($activity[$from_or_to]) && $activity[$from_or_to] == 'y')
			{
				$selected = 'selected="selected"';
			}
			else
			{
				$selected = '';
			}
			$select_str .= '<option value="'. $activity['wf_activity_id'] .'" '. $selected .'>'. $activity['wf_name'] .'</option>';
		}
		$select_str .= "</select>\n";
		return $select_str;
	}
	
	/**
	 * Build select box with all transitions activities filtered
	 * @param string  $var_name		
	 * @param array   $process_activities_data array of process data
	 * @param boolean $multiple 
	 * @param boolean $show_all show all list 
	 * @param boolean $from_or_to 
	 * @access public
	 * @return string html code with select box
	 */	
	function build_select_transition_filtered($var_name, $process_activities_data, $multiple=false, $show_all=true, $from=false)
	{
		$select_str = "<select name='$var_name'" . (($multiple)? " multiple='multiple' size='5'" : "" ) . ">";
		if ($show_all) $select_str .= '<option value="">'. lang('All') .'</option>';
		
		if( is_array($process_activities_data) && count($process_activities_data) > 0 ) 
		{
			foreach ($process_activities_data as $activity)
			{
				if ($from && $activity['wf_activity_id'] == $from )
				{
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				$select_str .= '<option value="'. $activity['wf_activity_id'] .'" '. $selected .'>'. $activity['wf_name'] .'</option>';
			}
		}
		$select_str .= "</select>\n";
		return $select_str;
	}

	/**
	 * Save the edited activity. 
	 * 
	 * @param int $activity_id
	 * @param string $name
	 * @param string $description
	 * @param string $type
	 * @param string $default_user
	 * @param bool $is_interative
	 * @param bool $is_autorouted 
	 * @return mixed Return the activity_id or false in case of error, $this->message is set in case of error
	 * @access public
	 */
	function save_activity($activity_id, $name, $description, $type, $default_user, $is_interactive, $is_autorouted, $userole, $userole_ro, $useagent, $rolename, $menu_path)
	{
		$is_interactive = ($is_interactive == 'on') ? 'y' : 'n';
		$is_autorouted = ($is_autorouted == 'on') ? 'y' : 'n';
		$vars = array(
			'wf_name' => $name,
			'wf_description' => $description,
			'wf_activity_id' => $activity_id,
			'wf_is_interactive' => $is_interactive,
			'wf_is_autorouted' => $is_autorouted,
			'wf_default_user' => $default_user,
			'wf_type' => $type,
			'wf_menu_path' => $menu_path
		);

		if( strlen($name) > 0 )
		{
			if ($this->activity_manager->activity_name_exists($this->wf_p_id, $name, $activity_id))
			{
				$this->message[] = ($name . ': '. lang('activity name already exists'));
				return false;
			}
		}
		else
		{
			$this->message[] = lang('Enter an activity name');
			return false;
		}

		$activity_id = $this->activity_manager->replace_activity($this->wf_p_id, $activity_id, $vars);
		
		// assign role to activity
		if ($userole) 
		{
			$this->activity_manager->add_activity_role($activity_id, $userole, ($userole_ro=='on'));
		}
		
		// assign agent to activity
		if ($useagent) 
		{
			$this->activity_manager->add_activity_agent($activity_id, $useagent);
		}

		//save agent configuration datas if any
		if (isset($_POST['wf_agent']))
		{
			$agents_conf =& $_POST['wf_agent'];
			
			//retrieve agents list
			$activity_agents =& $this->activity_manager->get_activity_agents($activity_id);
			//for all agents we create ui_agent object to handle admin agents displays and savings
			foreach ($activity_agents as $agent)
			{
				//create an empty temp ui_agent object
				$ui_agent =& createObject('workflow.ui_agent_'.$agent['wf_agent_type']);
				//build this object BUT without loading actual data
				//because we will save next values soon
				$ui_agent->load($agent['wf_agent_id'],false);
				//store it in an array
				$this->agents[$agent['wf_agent_type']] = $ui_agent;
				//delete the temp object
				unset($ui_agent);
			}
			// now we save the data obtained from the form in theses agents
			foreach ($agents_conf as $typeagent => $confarray)
			{
				$this->agents[$typeagent]->save($confarray);
			}
		}

		// add activity transitions
		if (isset($_POST['add_tran_from']))
		{
			foreach ($_POST['add_tran_from'] as $act_from)
			{
				$this->activity_manager->add_transition($this->wf_p_id, $act_from, $activity_id);
			}
		}
		if (isset($_POST['add_tran_to']))
		{
			foreach ($_POST['add_tran_to'] as $act_to)
			{
				$this->activity_manager->add_transition($this->wf_p_id, $activity_id, $act_to);
			}
		}
		$this->activity_manager->validate_process_activities($this->wf_p_id);
		
		
		return $activity_id;
	}
	/**
	 * Delete selected activities
	 * @param array $activities_ids
	 * @return bool true always
	 * @access public
	 */
	function delete_activities($activities_ids)
	{
		foreach ($activities_ids as $act_id)
		{
			$this->activity_manager->remove_activity($this->wf_p_id, $act_id);
		}
		return true;
	}

	function delete_transitions($transitions)
	{
		if( is_array($transitions) && count($transitions) > 0 ) {
			foreach (array_keys($transitions) as $transition)
			{
				$parts = explode('_', $transition);
				$this->activity_manager->remove_transition($parts[0], $parts[1]);
			}
			$this->message[] = lang('Transitions removed successfully');
		}
		else 
		{
			$this->message[] = lang('Select a transition to remove');
		}
	}
	/**
	 * Add a transition activity
	 * @param int $from from activity 
	 * @param int $to   next activity
	 * @return string Error or success message
	 * @access public
	 */
	function add_transition($from, $to)
	{
		if ($this->activity_manager->add_transition($this->wf_p_id, $from, $to))
		{
			$this->activity_manager->validate_process_activities($this->wf_p_id);
			return lang('New transition added');
		}
		$error_msg =  $this->activity_manager->get_error(false, _DEBUG);
		return lang("Couldn't add transition"). '; '. $error_msg[0];
	}
	/**
	 * Show actual process graph
	 * @access public
	 */
	function show_graph()
	{
		$proc_info = $this->process_manager->get_process($this->wf_p_id);
		$image_name = $proc_info['wf_normalized_name'] . SEP . 'graph' . SEP . $proc_info['wf_normalized_name'] . '.png';
		$image = GALAXIA_PROCESSES . SEP . $image_name;
		if (file_exists($image))
		{
			if (Factory::getInstance('BrowserInfo')->isOpera())
				$maximumDimension = 1000000;
			else
				$maximumDimension = 2500;

			$dims = getimagesize($image);
			list($originalWidth, $originalHeight) = $dims;

			/* the image must be resized */
			if (max($originalWidth, $originalHeight) > $maximumDimension)
			{
				/* define the new width and height */
				$newWidth = $newHeight = $maximumDimension;
				if ($originalWidth > $originalHeight)
					$newHeight = $maximumDimension * ($originalHeight / $originalWidth);
				else
					$newWidth = $maximumDimension * ($originalWidth / $originalHeight);

				/* create the new image and send to the browser */
				$smallerImage = imagecreatetruecolor($newWidth, $newHeight);
				imagecopyresampled($smallerImage, imagecreatefrompng($image), 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
				header('content-disposition: inline; filename=' . $image_name);
				header('content-type: ' . $dims['mime']);
				imagepng($smallerImage);
			}
			else
			{
				header('content-disposition: inline; filename=' . $image_name);
				header('content-type: ' . $dims['mime']);
				header('content-length: ' . filesize($image));
				readfile($image);
			}
		}
		// if there is no graph, show "graph not found" message
		else
		{
			header ('Content-type: image/png');
			$text = 'GRAFICO NAO ENCONTRADO';
			$font_size = 5;
			$width = imagefontwidth($font_size) * strlen($text);
			$height = imagefontheight($font_size);
			$im = imagecreate($width, $height);
			$text_color = imagecolorallocate($im, 0, 0, 0);		// black
			$bg_color = imagecolorallocate($im, 255, 255, 255);	// white
			imagefill($im, 0, 0, $bg_color);
			imagestring($im, $font_size, 0, 0, $text, $text_color);
			imagepng($im);
			imagedestroy($im);
		}
	}

	/**
	 * Dislays the activity agents config rows
	 * @return void
	 * @access public
	 */
	function display_agents_rows()
	{
		if (empty($this->agents))
		{
			$this->t->set_var(array('agents_config_rows' => ''));
		}
		else
		{
			$this->t->set_file('admin_agents', 'admin_agents.tpl');
			foreach ($this->agents as $ui_agent)
			{
				//this is parsing the agent's admin template in the given template var
				$ui_agent->showAdminActivityOptions('each_agent_rows');
			}
			$this->translate_template('admin_agents');
			$this->t->parse('agents_config_rows', 'admin_agents');
		}
	}
}
?>
