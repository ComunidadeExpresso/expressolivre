<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	require_once(dirname(__FILE__) . SEP . 'common.inc.php'); 				 /* including common deifinitions */
	require_once(dirname(__FILE__) . SEP . 'class.WorkflowUtils.inc.php'  ); /* superclass source code       */
	require_once(dirname(__FILE__) . SEP . 'class.basecontroller.inc.php' ); /* module controller            */
	require_once(dirname(__FILE__) . SEP . 'class.basemodel.inc.php'      ); /* module logic                 */

	/**
	 * @package Workflow
	 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
	 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
	 * @author Carlos Eduardo Nogueira Goncalves - cadu.br@gmail.com
	 * @license GPL
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
     */
	class run_activity extends WorkflowUtils
	{  	
		/**
		 * @var array  $public_functions func
		 * @access public
		 */
		var $public_functions = array(
			'go'	=> true,
			'goAjax'  => true
		);
		/**
		 * @var object $runtime Runtime Object from the workflow engine
		 * @access public
		 */
		var $runtime;
		// This is the object we'll be running to obtain the rigth activity
		//var $base_activity;
		//This is the right activity object
		/**
		 * @var object $activity Activity engine object.
		 * @access public
		 */
		var $activity;
		/**
		 * @var object $process Process engine object. Used to retrieve at least paths and configuration values
		 * @access public
		 */
		var $process;	
		/**
		 * @var object $GUI  GUI engine object. Act carefully with it.
		 * @access public
		 */
		var $GUI;
		/**
		 * @var array $message a message array
		 * @access public
		 */
		var $message = Array();
		/**
		 * @var object $categories categorie object for categories 
		 * @access public
		 */
		var $categories;
		/**
		 * @var array $conf 
		 * @access public
		 */
		var $conf = array();	
		/**
		 * @var $wf_template local activity template
		 * @access public
		 */
		var $wf_template;
		/** 
		 * @var object $instance
		 * @access public
		 */
		var $instance;
		/** 
		 * @var object $activity_type The type of activity
		 * @access public
		 */
		var $activity_type;
		// then we retain all usefull vars as members, to make them avaible in user's source code
		// theses are data which can be set before the user code and which are not likely to change because of the user code
		/** 
		 * @var int $process_id The process id 
		 * @access public
		 */
		var $process_id;
		/** 
		 * @var int $activity_id The activity id 
		 * @access public
		 */
		var $activity_id;
		/** 
		 * @var int $process_name The process name
		 * @access public
		 */
		var $process_name;
		/** 
		 * @var int $process_version The process_version 
		 * @access public
		 */
		var $process_version;
		/** 
		 * @var int $activity_name The process id 
		 * @access public
		 */
		var $activity_name;
		/** 
		 * @var int $user_name The user name 
		 * @access public
		 */
		var $user_name;
		/** 
		 * @var int $view_activity activity id of the view activity avaible for this process 
		 * @access public
		 */
		var $view_activity;
		// theses 4 vars aren't avaible for the user code, they're set only after this user code was executed		
		/** 
		 * @var int $instance_id Instance id 
		 * @access public
		 */
		var $instance_id=0;
		/** 
		 * @var string $instance_name Instance name 
		 * @access public
		 */
		var $instance_name='';
		/** 
		 * @var int $instance_owner Instance owner id 
		 * @access public
		 */
		var $instance_owner=0;
		/** 
		 * @var string $owner_name Owner name 
		 * @access public
		 */
		var $owner_name='';
		
		/**
		 * @var bool $print_mode print mode
		 * @access public
		 */
		var $print_mode = false;
		/**
		 * @var bool $enable_print_mode print mode
		 * @access public
		 */
		var $enable_print_mode = false;
		
		/**
		 * @var array $act_role_names of roles associated with the activity, usefull for lists of users associated with theses roles
		 * @access public	
		 */
		var $act_role_names= Array();
		
		/**
		 * @var array $agents Array of ui_agent objects
		 * @access public
		 */
		var $agents = Array();

		/**
		 * @var object $smarty holds a Smarty instance
		 * @access public
		 */
		var $smarty;                
		/**
		 * @var array $wf holds a global environment vector
		 * @access public
		 */
		var $wf;                    
		/**
		 * @var $download_mode activates download mode
		 * @access public
		 */
		var $download_mode;        
		/**
		 * @var string $_template_name holds the template's file name
		 * @access public
		 */
		var $_template_name = null; 
		/**
		 * @var bool Indicates wether the current instance is a child instance or not
         * @access public
         */
		var $isChildInstance = false;
		/**
		 * @var object Stores a 'workflow_smarty' object
         * @access private
         */
		private $workflowSmarty = null;
		/**
		 * @var object Log Object
         * @access private
         */
		private $logger = null;

		/**
		 * Constructor
		 * 
		 * @access public 
		 */
		function run_activity()
		{
			parent::WorkflowUtils();

			/**
			 * We should always use newInstance to instantiate
			 * 'workflow_wfruntime'
			 */
			$this->runtime			= &Factory::newInstance('workflow_wfruntime');
			$this->runtime->setDebug(_DEBUG);
			$this->GUI				= &Factory::getInstance('workflow_gui');
			$this->categories 		= &Factory::getInstance('categories');

			$this->workflowSmarty 	= &Factory::getInstance('workflow_smarty', false);

			// never configure a log of type "firebug" here. This will make goAjax stop running well =(
			$this->logger 			= &Factory::getInstance('Logger', array('file'));

			// TODO: open a new connection to the database under a different username to allow privilege handling on tables
			unset($this->db);
		}

		/**
		  * This function is used to run all activities for specified instances. it could be interactive activities
		  * or automatic activities. this second case is the reason why we return some values
		  * @param int $activityId is the activity_id it run
		  * @param int $iid is the instance id it run for
		  * @param $auto is true by default
		  * @return mixed AN ARRAY, or at least true or false. This array can contain :
		  * a key 'failure' with an error string the engine will retrieve in instance error messages in case of
		  *	failure (this will mark your execution as Bad),
		  * a key 'debug' with a debug string the engine will retrieve in instance error messages,
		  */
		function go($activity_id=0, $iid=0, $auto=0)
		{
			$totalTime = microtime(true);

			$result=Array();

			if ($iid)
			{
				$_REQUEST['iid'] = $iid;
			}
			$iid = $_REQUEST['iid'];

			//$activity_id is set when we are in auto mode. In interactive mode we get if from POST or GET
			if (!$activity_id)
			{
				$activity_id	= (int)get_var('activity_id', array('GET','POST'), 0);
			}

			// load activity and instance
			if (!$activity_id)
			{
				$result['failure'] =  $this->runtime->fail(lang('Cannot run unknown activity'), true, _DEBUG, $auto);
				return $result;
			}

			//initalising activity and instance objects inside the WfRuntime object
			if (!($this->runtime->loadRuntime($activity_id,$iid)))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity'), true, _DEBUG, $auto);
				return $result;
			}

			$activity =& $this->runtime->getActivity($activity_id, true, true);
			$this->activity =& $activity;
			// the instance is avaible with $instance or $this->instance
			// note that for standalone activities this instance can be an empty instance object, but false is a bad value
			//$this->instance =& $this->runtime->loadInstance($iid);

			// HERE IS A BIG POINT: we map the instance to a runtime object
			// user code will manipulate a stance, thinking it's an instance, but it is
			// in fact a WfRuntime object, mapping all instance functions
			$this->instance =& $this->runtime;
			$instance =& $this->instance;
			$GLOBALS['workflow']['wf_runtime'] =& $this->runtime;
			if (!($instance))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity without instance'), true, _DEBUG, $auto);
				return $result;
			}
			$this->instance_id = $instance->getInstanceId();

			// load process
			$this->process =& $this->runtime->getProcess();
			if (!($this->process))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity without her process').$instance, true, _DEBUG, $auto);
				return $result;
			}

			//set some global variables needed
			$GLOBALS['workflow']['__leave_activity']=false;
			$GLOBALS['user'] = $GLOBALS['phpgw_info']['user']['account_id'];

			//load role names, just an information
			$this->act_role_names = $activity->getActivityRoleNames();

			//set some other usefull vars
			$this->activity_type	= $activity->getType();
			$this->process_id 	= $activity->getProcessId();
			$this->activity_id 	= $activity_id;
			$this->process_name	= $this->process->getName();
			$this->process_version	= $this->process->getVersion();
			$this->activity_name	= $activity->getName();
			$this->user_name   		= $GLOBALS['phpgw']->accounts->id2name($GLOBALS['user']);
			$this->view_activity	= $this->GUI->gui_get_process_view_activity($this->process_id);

			//we set them in $GLOBALS['workflow'] as well
			$GLOBALS['workflow']['wf_activity_type']			=& $this->activity_type;
			$GLOBALS['workflow']['wf_process_id'] 				=& $this->process_id;
			$GLOBALS['workflow']['wf_activity_id'] 				=& $this->activity_id;
			$GLOBALS['workflow']['wf_process_name']				=& $this->process_name;
			$GLOBALS['workflow']['wf_normalized_name']			=  $this->process->getNormalizedName();
			$GLOBALS['workflow']['wf_process_version']			=& $this->process_version;
			$GLOBALS['workflow']['wf_activity_name']			=& $this->activity_name;
			$GLOBALS['workflow']['wf_user_name']				=& $this->user_name;
			$GLOBALS['workflow']['wf_user_id']					=& $GLOBALS['user'];
			$GLOBALS['workflow']['wf_view_activity']			=& $this->view_activity;
			$GLOBALS['workflow']['wf_webserver_url'] 			= $GLOBALS['phpgw_info']['server']['webserver_url'];
			$GLOBALS['workflow']['wf_workflow_path']			= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow';
			$GLOBALS['workflow']['wf_resources_path']			= $GLOBALS['phpgw_info']['server']['webserver_url'] . SEP . 'workflow/redirect.php?pid=' . $this->process_id . '&file=';
			$GLOBALS['workflow']['wf_default_resources_path']	= Factory::getInstance('TemplateServer')->generateLink('processes');
			$GLOBALS['workflow']['wf_workflow_resources_path']	= Factory::getInstance('TemplateServer')->generateLink('');
			$GLOBALS['workflow']['wf_activity_url']				= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'index.php?menuaction=workflow.'.get_class($this).'.go&activity_id='.$activity_id;
			$GLOBALS['workflow']['wf_user_cnname']				= Factory::getInstance('WorkflowLDAP')->getName($GLOBALS['user']);
			$GLOBALS['workflow']['wf_back_link']				= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow'.SEP.'index.php?start_tab=1';
			$GLOBALS['workflow']['wf_js_path']					= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow'.SEP.'js'.SEP.'jscode';
			$GLOBALS['workflow']['wf_user_activities']			= $this->GUI->gui_list_user_activities($GLOBALS['user'], '0', '-1', 'ga.wf_name__ASC', '', '', false, true, true, true, '');
			if ($iid)
				$GLOBALS['workflow']['wf_instance_url']	= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'index.php?menuaction=workflow.'.get_class($this).'.go&activity_id='.$activity_id."&iid=".$iid;
			else
				unset($GLOBALS['workflow']['wf_instance_url']);
			$wf =& $GLOBALS['workflow'];

			/* path to the local functions developed by Celepar */
			$functions = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'local' . SEP . 'functions' . SEP . 'local.functions.php';

			/* activate local functions */
			require_once($functions);

			//get configuration options with default values if no init was done before
			$myconf = array(
				'execute_activities_in_debug_mode'	=> 0,
				'execute_activities_using_secure_connection' => 0
			);
			//this will give use asked options and som others used by WfRuntime
			$this->conf =& $this->runtime->getConfigValues($myconf);
			if ($this->conf['execute_activities_using_secure_connection'])
			{
				if (($GLOBALS['phpgw_info']['server']['use_https'] > 0) && ($_SERVER['HTTPS'] != 'on') && (!isset($GLOBALS['workflow']['job']['processID'])))
				{
					header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
					exit;
				}
			}

			if ($auto && (!$this->isChildInstance) && $activity->isInteractive())
			{
				$actualUser = $GLOBALS['user'];
				$actualUserGroups = $_SESSION['phpgw_info']['workflow']['user_groups'];
				foreach ($instance->instance->activities as $actTmp)
				{
					if ($actTmp['wf_activity_id'] == $activity_id)
					{
						$newUser = $actTmp['wf_user'];
						break;
					}
				}

				/* pretend to be the user */
				$_SESSION['phpgw_info']['workflow']['user_groups'] = galaxia_retrieve_user_groups($newUser);
				$_SESSION['phpgw_info']['workflow']['account_id'] = $newUser;
				$GLOBALS['phpgw_info']['user']['account_id'] = $newUser;
				$GLOBALS['workflow']['wf_user_id'] = $newUser;
				$GLOBALS['user'] = $newUser;

				/* check some permissions */
				if (($newUser == '*') || (!$this->runtime->checkUserRun($newUser)) || (Factory::getInstance('WorkflowLDAP')->getName($newUser) === false))
				{
					$_SESSION['phpgw_info']['workflow']['user_groups'] = $actualUserGroups;
					$_SESSION['phpgw_info']['workflow']['account_id'] = $actualUser;
					$GLOBALS['phpgw_info']['user']['account_id'] = $actualUser;
					$GLOBALS['workflow']['wf_user_id'] = $actualUser;
					$GLOBALS['user'] = $actualUser;

					return false;
				}
				$smarty = Factory::getInstance('process_smarty');
			}

			// run the activity
			//interactive section
			if (!$auto && $activity->isInteractive())
			{

				$this->print_mode = get_var('print_mode', array('POST','GET'), false);
				$this->download_mode = get_var('download_mode', array('POST','GET'), false);

				$smarty = Factory::getInstance('process_smarty');

				$smarty->template_dir  = GALAXIA_PROCESSES.SEP.$this->process->getNormalizedName().SEP.'code'.SEP.'templates';
				$smarty->compile_dir   = GALAXIA_PROCESSES.SEP.$this->process->getNormalizedName().SEP.'smarty'.SEP.'compiled';
				$smarty->config_dir    = GALAXIA_PROCESSES.SEP.$this->process->getNormalizedName().SEP.'code'.SEP.'templates';
				$smarty->cache_dir     = GALAXIA_PROCESSES.SEP.$this->process->getNormalizedName().SEP.'smarty'.SEP.'cache';
				$smarty->plugins_dir[] = PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'smarty'.SEP.'wf_plugins';

				$GLOBALS['phpgw']->template =& $smarty;
				$this->wf_template =& $smarty;
				$_template_name = null;

				//set resource path to use in templates
				$smarty->assign('wf_resources_path',$GLOBALS['workflow']['wf_resources_path']);
				$smarty->assign('wf_default_resources_path', $GLOBALS['workflow']['wf_default_resources_path']);
				$smarty->assign('wf_workflow_resources_path', $GLOBALS['workflow']['wf_workflow_resources_path']);
				$smarty->assign('wf_workflow_path',$GLOBALS['workflow']['wf_workflow_path']);
				$smarty->assign('wf_js_path',$GLOBALS['workflow']['wf_js_path']);
				$smarty->assign('wf_back_link',$GLOBALS['workflow']['wf_back_link']);
				$smarty->assign('wf_activity_url',$GLOBALS['workflow']['wf_activity_url']);

				/* register the prefilter smarty plugin wf_default_template */
				$smarty->load_filter('pre', 'wf_default_template');
			}

			if ($this->conf['execute_activities_in_debug_mode'])
			{
				ini_set('display_errors',true);
				error_reporting(E_ALL & ~E_NOTICE);
			} else {
				ini_set('display_errors',false);
			}

			/* BEGIN WORKFLOW MVC SETTINGS */
			$env = array();                                            //create settings vector
			$env['view']          =& $smarty;                          //view layer instance
			$env['template_file'] =& $_template_name;                  //template file to be shown
			$env['dao']			  =& Factory::newInstance('wf_db');    //data access object instance
			$env['workflow']      =& $GLOBALS['workflow'];             //workflow environment information
			$env['instance']      =& $instance;                        //process manager instance
			$env['activity']      =& $activity;                        //activity manager instance
			$security             =& Factory::newInstance('SecurityUtils');              //input sanitizer class
			$env['request']       =& $security->process($_REQUEST);    //sanitizes input data from client
			$env['factory']       =& Factory::getInstance('ProcessWrapperFactory');  //instantiation controller class
			$env['natural']		  =& Factory::newInstance('wf_natural');   //data access object instance for mainframe
			/* END WORKFLOW MVC SETTINGS */

			$GLOBALS['workflow_env'] = &$env;

			/**
			 * [__leave_activity] is setted if needed in the xxx_pre code or by the user in his code
			 * HERE the user code is 'executed'. Note that we do not use include_once or require_once because
			 * it could the same code several times with automatic activities looping in the graph and it still
			 * need to be executed
			 */
			$_engineProcessCodeDirectory = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'code';
			$_engineCompilerDirectory = GALAXIA_LIBRARY . SEP . 'compiler';
			$_engineFiles = array();

			/* generate the list of needed files */
			$_engineFiles[] = "{$_engineCompilerDirectory}/_shared_pre.php";
			$_engineFiles[] = "{$_engineProcessCodeDirectory}/shared.php";
			$_engineFiles[] = "{$_engineCompilerDirectory}/{$activity->getType()}_pre.php";
			if ($activity->getAgents() !== false)
				$_engineFiles[] = "{$_engineCompilerDirectory}/agents_pre.php";
			$_engineFiles[] = "{$_engineProcessCodeDirectory}/activities/{$activity->getNormalizedName()}.php";
			$_engineFiles[] = "{$_engineCompilerDirectory}/{$activity->getType()}_pos.php";
			if ($activity->getAgents() !== false)
				$_engineFiles[] = "{$_engineCompilerDirectory}/agents_pos.php";
			$_engineFiles[] = "{$_engineCompilerDirectory}/_shared_pos.php";

			/* check if the required files exists */
			foreach ($_engineFiles as $_engineFile)
				if (!file_exists($_engineFile))
					return array('failure' => $this->runtime->fail(lang('the following file could not be found: %1', $_engineFile), true, _DEBUG));

			/* activate the security policy */
			Factory::getInstance('WorkflowSecurity')->enableSecurityPolicy();

			/**
			 * Here we are going to use our new Security static class.
			 * From now, the factory frontend (static) will forward
			 * the messages for the process factory instead of Workflow
			 * factory.
			*/
			Security::enable();

			/* include the files */
			$processTime = microtime(true);

			foreach ($_engineFiles as $_engineFile)
				require $_engineFile;

			$processTime = (microtime(true) - $processTime);

			unset($GLOBALS['workflow_env']);

			/* check if the developer wants to user the download mode */
			if (isset($GLOBALS['workflow']['downloadMode']) && ($GLOBALS['workflow']['downloadMode'] == true))
				$this->download_mode = true;

			if ($auto && (!$this->isChildInstance) && $activity->isInteractive() && (!empty($actualUser)))
			{
				$_SESSION['phpgw_info']['workflow']['user_groups'] = $actualUserGroups;
				$_SESSION['phpgw_info']['workflow']['account_id'] = $actualUser;
				$GLOBALS['phpgw_info']['user']['account_id'] = $actualUser;
				$GLOBALS['workflow']['wf_user_id'] = $actualUser;
				$GLOBALS['user'] = $actualUser;
			}

			//Now that the instance is ready and that user code has maybe change some things
			// we can catch some others usefull vars
			$this->instance_id	= $instance->getInstanceId();
			$this->instance_name	= $instance->getName();
			$this->instance_owner	= $instance->getOwner();
			$this->owner_name	= $GLOBALS['phpgw']->accounts->id2name($this->instance_owner);
			if ($this->owner_name == '')
			{
				$this->owner_name = lang('Nobody');
			}
			$GLOBALS['workflow']['wf_instance_id'] 	=& $this->instance_id;
			$GLOBALS['workflow']['wf_instance_name']=& $this->instance_name;
			$GLOBALS['workflow']['wf_instance_owner']=& $this->instance_owner;
			$GLOBALS['workflow']['wf_owner_name']=& $this->owner_name;

			//was template changed?
			if ($_template_name)
			{
				$this->_template_name = $_template_name;
			}
			else
			{
				$this->_template_name = $this->activity->getNormalizedName().'.tpl';
			}

			$totalTime = (microtime(true) - $totalTime);

			$logTime = sprintf("GO [pid=%s,iid=%s,uid=%s,aid=%s] [eng=%ss,proc=%ss]",
								$this->process_id,
								$this->instance_id,
								$GLOBALS['user'],
								$this->activity_id,
								number_format(($totalTime - $processTime),3),
								number_format($processTime,3) );

			$this->logger->debug($logTime);

			// TODO: process instance comments
			$instructions = $this->runtime->handle_postUserCode(_DEBUG);
			switch($instructions['action'])
			{
				//interactive activity completed
				case 'completed':
					// re-retrieve instance data which could have been modified by an automatic activity
					$this->instance_id                = $instance->getInstanceId();
					$this->instance_name              = $instance->getName();
					$this->activityCompleteMessage    = $instance->getActivityCompleteMessage();

					if (!$auto)
					{
						$this->assignCommonVariables();
						// and display completed template
						if ($GLOBALS['phpgw_info']['user']['preferences']['workflow']['show_activity_complete_page'] === '0')
							header('Location: workflow/index.php');
						else
							$this->showCompletedPage();
					}
					break;
				//interactive activity still in interactive mode
				case 'loop':
					if (!$auto)
					{
						$this->assignCommonVariables();
						$this->showForm();
					}
					break;
				//nothing more
				case 'leaving':
					if (!$auto)
					{
						$this->assignCommonVariables();
						$this->showCancelledPage();
					}
					break;
				//non-interactive activities, auto-mode
				case 'return':
					$result=Array();
					$this->message[] = $this->GUI->get_error(false, _DEBUG);
					$this->message[] = $this->runtime->get_error(false, _DEBUG);
					//$this->message[] = $this->process->get_error(false, _DEBUG);
					$result =& $instructions['engine_info'];
					$this->message[] = $result['debug'];
					$result['debug'] = implode('<br />',array_filter($this->message));
					return $result;
					break;
				default:
					return $this->runtime->fail(lang('unknown instruction from the workflow engine: %1', $instructions['action']), true, _DEBUG);
					break;
			}
		}

		/**
		 * goajax
		 *
		 * @param int $activity_id
		 * @param int $iid
		 * @param bool $auto
		 * @return array
		 */
		function goAjax($activity_id=0, $iid=0, $auto=0)
		{
			$totalTime = microtime(true);

			$result=Array();

			if ($iid)
				$_REQUEST['iid'] = $iid;
			$iid = $_REQUEST['iid'];

			//$activity_id is set when we are in auto mode. In interactive mode we get if from POST or GET
			if (!$activity_id)
				$activity_id	= (int)get_var('activity_id', array('GET','POST'), 0);

			// load activity and instance
			if (!$activity_id)
			{
				$result['failure'] =  $this->runtime->fail(lang('Cannot run unknown activity'), true, _DEBUG, $auto);
				return $result;
			}

			//initalising activity and instance objects inside the WfRuntime object
			if (!($this->runtime->loadRuntime($activity_id,$iid)))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity'), true, _DEBUG, $auto);
				return $result;
			}

			$activity =& $this->runtime->getActivity($activity_id, true, true);
			$this->activity =& $activity;
			// the instance is avaible with $instance or $this->instance
			// note that for standalone activities this instance can be an empty instance object, but false is a bad value
			// HERE IS A BIG POINT: we map the instance to a runtime object
			// user code will manipulate a stance, thinking it's an instance, but it is
			// in fact a WfRuntime object, mapping all instance functions
			$this->instance =& $this->runtime;
			$instance =& $this->instance;
			$GLOBALS['workflow']['wf_runtime'] =& $this->runtime;
			if (!($instance))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity without instance'), true, _DEBUG, $auto);
				return $result;
			}
			$this->instance_id = $instance->getInstanceId();

			// load process
			$this->process =& $this->runtime->getProcess();
			if (!($this->process))
			{
				$result['failure'] = $this->runtime->fail(lang('Cannot run the activity without her process').$instance, true, _DEBUG, $auto);
				return $result;
			}

			//set some global variables needed
			$GLOBALS['user'] = $GLOBALS['phpgw_info']['user']['account_id'];

			//load role names, just an information
			$this->act_role_names = $activity->getActivityRoleNames();

			//set some other usefull vars
			$this->activity_type	= $activity->getType();
			$this->process_id 	= $activity->getProcessId();
			$this->activity_id 	= $activity_id;
			$this->process_name	= $this->process->getName();
			$this->process_version	= $this->process->getVersion();
			$this->activity_name	= $activity->getName();
			$this->user_name	= $GLOBALS['phpgw']->accounts->id2name($GLOBALS['user']);
			$this->view_activity	= $this->GUI->gui_get_process_view_activity($this->process_id);

			//we set them in $GLOBALS['workflow'] as well
			$GLOBALS['workflow']['wf_activity_type']			=& $this->activity_type;
			$GLOBALS['workflow']['wf_process_id'] 				=& $this->process_id;
			$GLOBALS['workflow']['wf_activity_id']		 		=& $this->activity_id;
			$GLOBALS['workflow']['wf_process_name']				=& $this->process_name;
			$GLOBALS['workflow']['wf_normalized_name']			=  $this->process->getNormalizedName();
			$GLOBALS['workflow']['wf_process_version']			=& $this->process_version;
			$GLOBALS['workflow']['wf_activity_name']			=& $this->activity_name;
			$GLOBALS['workflow']['wf_user_name']				=& $this->user_name;
			$GLOBALS['workflow']['wf_user_id']					=& $GLOBALS['user'];
			$GLOBALS['workflow']['wf_view_activity']			=& $this->view_activity;
			$GLOBALS['workflow']['wf_workflow_path']			= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow';
			$GLOBALS['workflow']['wf_resources_path']			= $GLOBALS['phpgw_info']['server']['webserver_url'] . SEP . 'workflow/redirect.php?pid=' . $this->process_id . '&file=';
			$GLOBALS['workflow']['wf_default_resources_path']	= Factory::getInstance('TemplateServer')->generateLink('processes');
			$GLOBALS['workflow']['wf_workflow_resources_path']	= Factory::getInstance('TemplateServer')->generateLink('');
			$GLOBALS['workflow']['wf_activity_url']				= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'index.php?menuaction=workflow.'.get_class($this).'.go&activity_id='.$activity_id;
			$GLOBALS['workflow']['wf_user_cnname']				= Factory::getInstance('WorkflowLDAP')->getName($GLOBALS['user']);
			$GLOBALS['workflow']['wf_back_link']				= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow'.SEP.'index.php?start_tab=1';
			$GLOBALS['workflow']['wf_js_path']					= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'workflow'.SEP.'js'.SEP.'jscode';
			$GLOBALS['workflow']['wf_user_activities']			= $this->GUI->gui_list_user_activities($GLOBALS['user'], '0', '-1', 'ga.wf_name__ASC', '', '', false, true, true, true, '');
			if ($iid)
				$GLOBALS['workflow']['wf_instance_url']	= $GLOBALS['phpgw_info']['server']['webserver_url'].SEP.'index.php?menuaction=workflow.'.get_class($this).'.go&activity_id='.$activity_id."&iid=".$iid;
			else
				unset($GLOBALS['workflow']['wf_instance_url']);

			/* activate local functions */
			require_once(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'local' . SEP . 'functions' . SEP . 'local.functions.php');

			//get configuration options with default values if no init was done before
			$myconf = array(
				'execute_activities_in_debug_mode'	=> 0,
				'execute_activities_using_secure_connection' => 0
			);
			//this will give use asked options and som others used by WfRuntime
			$this->conf =& $this->runtime->getConfigValues($myconf);

			// run the activity
			if ($this->conf['execute_activities_in_debug_mode'])
			{
				ini_set('display_errors',true);
				error_reporting(E_ALL & ~E_NOTICE);
			} else {
				ini_set('display_errors',false);
			}

			/* BEGIN WORKFLOW MVC SETTINGS */
			$env = array();                                            //create settings vector
			$env['dao']			  =& Factory::newInstance('wf_db');    //data access object instance
			$env['workflow']      =& $GLOBALS['workflow'];             //workflow environment information
			$env['instance']      =& $instance;                        //process manager instance
			$env['activity']      =& $activity;                        //activity manager instance
			$security             =& Factory::newInstance('SecurityUtils'); //input sanitizer class
			$env['request']       =& $security->process($_REQUEST); //sanitizes input data from client
			$env['factory']       =& Factory::newInstance('ProcessWrapperFactory');  //instantiation controller class
			$env['natural']		  =& Factory::newInstance('wf_natural');   //data access object instance for mainframe
			/* END WORKFLOW MVC SETTINGS */

			require_once(dirname(__FILE__) . SEP . 'nano' . SEP . 'JSON.php');
			require_once(dirname(__FILE__) . SEP . 'nano' . SEP . 'NanoUtil.class.php');
			require_once(dirname(__FILE__) . SEP . 'nano' . SEP . 'NanoJsonConverter.class.php');
			require_once(dirname(__FILE__) . SEP . 'nano' . SEP . 'NanoRequest.class.php');
			require_once(dirname(__FILE__) . SEP . 'nano' . SEP . 'NanoController.class.php');

			/* activate the security policy */
			Factory::getInstance('WorkflowSecurity')->enableSecurityPolicy();

			/**
			 * here we are going to use our new Security static class.
			 * From now, the factory frontend (static) will forward
			 * the messages for the process factory instead of Workflow
			 * factory. Note that this is the same comment as the previous
			 * function... boooring..
			 */
			Security::enable();

			$GLOBALS['workflow_env'] = &$env;
			$nc = &Factory::newInstance('NanoController');
			$nc->setClassPath(GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'code');

			$processTime = microtime(true);
			$nc->iterateOverVirtualRequests();
			$processTime = microtime(true) - $processTime;

			$nc->outputResultData();
			unset($GLOBALS['workflow_env']);

			if (!is_null($iid))
				$instance->instance->sync();

			$totalTime = microtime(true) - $totalTime;

			$logTime = sprintf("GOAJAX [pid=%s,iid=%s,uid=%s,aid=%s] [eng=%ss,proc=%ss]",
								$this->process_id,
								$this->instance_id,
								$GLOBALS['user'],
								$this->activity_id,
								number_format(($totalTime - $processTime),3),
								number_format($processTime,3) );

			$this->logger->debug($logTime);
		}

		/**
		 * Create a child instance
		 *
		 * @param int $activityID
		 * @param mixed $properties
		 * @param string $user
		 * @param bool $parentLock
		 * @return int The instance ID of the just created instance
		 * @access public
		 */
		function goChildInstance($activityID, $properties, $user, $parentLock)
		{
			$this->isChildInstance = true;
			$this->runtime->instance->isChildInstance = true;
			$this->runtime->instance->activityID = $activityID;
			$this->runtime->instance->parentLock = $parentLock;
			$this->runtime->instance->setProperties($properties);
			if ($user != '*')
				$this->runtime->setNextUser($user);

			/* run the selected activity */
			ob_start();
			$this->go($activityID, 0, true);
			ob_end_clean();

			/* return the just created child instance */
			return $this->runtime->instance_id;
		}

		/**
		 * Show the page avaible when completing an activity
		 * @return void
		 * @access public
		 */
		function showCompletedPage()
		{
			$this->workflowSmarty->assign('activityEvent', 'completed');
			$this->showAfterRunningPage();
		}

		/**
		 * Show the page avaible when leaving an activity
		 * @return void
		 * @access public
		 */
		function showCancelledPage()
		{
			$this->workflowSmarty->assign('activityEvent', 'cancelled');
			$this->showAfterRunningPage();
		}

		/**
		 * Common code of pages showed after activity pages
		 * @return void
		 * @access public
		 */
		function showAfterRunningPage()
		{
			/* get the header/footer */
			$this->assignHeader();

			/* generate the activity list */
			$processActivities = array_filter($GLOBALS['workflow']['wf_user_activities']['data'], create_function('$a', 'return ($a["wf_p_id"] == ' . $GLOBALS['workflow']['wf_process_id'] . ');'));
			$activityList = array(0 => '-- Selecione uma atividade --');
			foreach ($processActivities as $processActivity)
				$activityList[$processActivity['wf_activity_id']] = $processActivity['wf_name'];

			/* assign some variables */
			$this->workflowSmarty->assign('processName', $this->process_name);
			$this->workflowSmarty->assign('processVersion', $this->process_version);
			$this->workflowSmarty->assign('activityName', $this->activity_name);
			$this->workflowSmarty->assign('activityCompleteMessage', $this->activityCompleteMessage);
			$this->workflowSmarty->assign('activityBaseURL', $GLOBALS['phpgw_info']['server']['webserver_url']);
			$this->workflowSmarty->assign('activityList', $activityList);

			/* display the template */
			$this->workflowSmarty->display('after_running.tpl');
		}

		/**
		 * Assign common information of interactive forms (e.g., error messages)
		 * @return void
		 * @access public
		 */
		function assignCommonVariables()
		{
			$this->message[] = $this->GUI->get_error(false, _DEBUG);
			$this->message[] = $this->runtime->get_error(false, _DEBUG);
			$activityErrors = array_filter(array_merge(explode('<br />', $this->message[0]), explode('<br />', $this->message[1])));
			$this->workflowSmarty->assign('activityErrors', $activityErrors);
		}

		/**
		 * Show the activity page (workflow template and activity template)
		 * @return void
		 * @access public
		 */
		function showForm()
		{
			/* define the header */
			$this->assignHeader();

			/* define the variables */
			$activityOutput = $this->wf_template->fetch($this->_template_name);
			$actionURL = isset($GLOBALS['workflow']['wf_instance_url']) ? $GLOBALS['workflow']['wf_instance_url'] : $GLOBALS['workflow']['wf_activity_url'];
			$CSSLink = $this->get_css_link('run_activity', $this->print_mode);
			$CSSMedia = $this->print_mode ? 'print' : 'all';

			/* assign the variables to smarty */
			$this->workflowSmarty->assign('activityOutput', $activityOutput);
			$this->workflowSmarty->assign('actionURL', $actionURL);
			$this->workflowSmarty->assign('CSSLink', $CSSLink);
			$this->workflowSmarty->assign('CSSMedia', $CSSMedia);

			$this->workflowSmarty->display('run_activity.tpl');
			unset($smarty);
		}

		/**
		 * Define if the header, footer and navigation bar will be shown
		 * @return void
		 * @access public
		 */
		function assignHeader()
		{
			$headerConfig = 0;
			if (!$this->download_mode and !$this->print_mode)
			{
				$headerConfig |= workflow_smarty::SHOW_HEADER | workflow_smarty::SHOW_FOOTER;
				if ($this->runtime->activity->child_name != 'View')
					$headerConfig |= workflow_smarty::SHOW_NAVIGATION_BAR;
			}

			$this->workflowSmarty->setHeader($headerConfig);
			$this->workflowSmarty->assign('header', $this->workflowSmarty->expressoHeader);
			$this->workflowSmarty->assign('footer', $this->workflowSmarty->expressoFooter);
		}
	}
?>
