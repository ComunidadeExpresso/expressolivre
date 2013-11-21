<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');

/**
 * Handles most security issues in the engine
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class WfSecurity extends Base {
  
  /**
   * @var array processes config values cached for this object life duration, init is done at first use for each process
   * @access public
   */
  var $processesConfig= Array();
      
  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object WfSecurity
   * @access public
   */
  function WfSecurity() 
  {
    $this->child_name = 'WfSecurity';
    parent::Base();
  }

  /**
   * Loads config values for a given process. 
   * Config values for a given process are cached while this WfSecurity object stay alive
   * 
   * @param int $pId Process id
   * @access private
   * @return void 
   */
  function loadConfigValues($pId)
  {
    //check if we already have the config values for this processId
    if (!(isset($this->processesConfig[$pId])))
    {
      //define conf values we need
      $arrayConf=array(
        'ownership_give_abort_right'		=>1,
        'ownership_give_exception_right'	=>1,
        'ownership_give_release_right'		=>1,
        'role_give_abort_right'           	=>0,
        'role_give_release_right'		=>0,
        'role_give_exception_right'		=>0,
        'disable_advanced_actions'		=>0,
		'iframe_view_height'			=>-1,
		'execute_activities_using_secure_connection' =>0
      );
      //check theses values for this process and store the result for this object life duration
      $myProcess = &Factory::newInstance('Process');
      $myProcess->getProcess($pId);
      $this->processesConfig[$pId] = $myProcess->getConfigValues($arrayConf);
      unset($myProcess);
    }    
  }

  /**
   * Checks if a user has a access to an activity, use it at runtime.
   * To do so it checks if the user is in the users having the roles associated with the activity
   * or if he is in the groups having roles associated with the activity
   * 
   * @access public
   * @param int $user User id
   * @param int $activityId Activity id
   * @param bool $readonly False by default. If true we only check read-only access level for the user on this activity
   * @return bool True if access is granted false in other case. Errors are stored in the object
   */
  function checkUserAccess($user, $activity_id, $readonly=false) 
  {
	/* if activity is non-interactive is not necessary checking user access */
	$activity = &Factory::getInstance('BaseActivity')->getActivity($activity_id);
	if (!$activity->isInteractive())
		return true;

    //group mapping, warning groups and user can have the same id
    if ($user[0] != 'p')
        $groups = galaxia_retrieve_user_groups($user);
    else
        $groups = false;
        
    $query = 'select count(*) from '.GALAXIA_TABLE_PREFIX.'activity_roles gar 
        INNER JOIN '.GALAXIA_TABLE_PREFIX.'roles gr ON gar.wf_role_id=gr.wf_role_id
        INNER JOIN '.GALAXIA_TABLE_PREFIX.'user_roles gur ON gur.wf_role_id=gr.wf_role_id 
        where gar.wf_activity_id=? 
        and ( (gur.wf_user=? and gur.wf_account_type=?)';
    if (is_array($groups))
    {
      foreach ($groups as &$group)
        $group = "'{$group}'";
      $query .= ' or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
    }

    $query .= " or ('p'||gur.wf_role_id = '$user')";

    $query .= ')';

    if (!($readonly))
    {
      $query.= 'and NOT(gar.wf_readonly=1)';
    }
    $result= $this->getOne($query ,array($activity_id, $user, 'u'));
    if ($result)
    {
      //echo "<br>Access granted for ".$user;
      return true;
    }
    else
    {
      $this->error[]= tra('Access denied for user %1 on activity %2, no role', $user, $activity_id);
      return false;
    }
  }

  /**
   * Checks at RUNTIME whether running user is authorized for a given action on some activity/instance.
   * This function will check the given action for the current running user, lock the table rows if necessary to ensure
   * nothing will move from another process between the check and the later action. 
   * loacked tables can be instances and instance-activities.
   * NOTA BENE: there is no lock on activity/processes table, we assume the admin is not changing the activity data
   * on a running/production process, this is why there is versioning and activation on processes
   * Encapsulate this function call in a transaction, locks will be removed at the end of the transaction, whent COMMIT 
   * or ROLLBACK will be launched
   * 
   * @param int $activityId Activity id, it may be 0
   * @param int $instanceId InstanceId, it may be 0
   * @param string $action ONE action asked, it must be one of: 'grab', 'release', 'exception', 'resume', 'abort', 'run', 'send', 'view', 'viewrun', 'complete' (internal action before completing), 'restart' admin function, restarting a failed automatic activity 
   * be carefull, View can be done in 2 ways: viewrun : by the view activity if the process has a view activity, and only by this way in such case, view: by a general view form with access to everybody if the process has no view activity
   * @return bool True if action access is granted false in other case. Errors are stored in the object
   * @access public
   */
  function checkUserAction($activityId, $instanceId,$action)
  {
    //Warning: 
    //start and standalone activities have no instances associated
    //aborted and completed instances have no activities associated

    //$this->error[] = 'DEBUG: action:'.$action;
    if ($action!='run' && $action!='send' && $action!='view' && $action!='viewrun' && $action!='complete' && $action!='grab' && $action!='release' && $action!='exception' && $action!='resume' && $action!='abort' && $action!='restart')
    {
      $this->error[] = tra('Security check: Cannot understand asked action');
      return false;
    }
    
    $user = galaxia_retrieve_running_user();
    //$this->error[] = 'DEBUG: running user:'.$user;
    if ( (!(isset($user))) || (empty($user)) )
    {
      $this->error[] = tra('Cannot retrieve the user running the security check');
      return false;
    }

    //0 - prepare RowLocks ----------------------------------------------------------
    $lock_instance_activities = false;
    $lock_instances = false;
    switch($action)
    {
      case 'view':
      case 'viewrun':
        //no impact on write mode, no lock
        break;
      case 'grab':
        //impacted tables is instance_activities
        $lock_instance_activities = true;
        break;
      case 'release' :
        //impacted tables is instance_activities
        $lock_instance_activities = true;
        break;
      case 'exception':
        //impacted tables is instances
        $lock_instances = true;
        break;
      case 'resume':
        //impacted tables is instances
        $lock_instances = true;
        break;
      case 'abort':
        //impacted tables are instances and instance_activities (deleting rows)
        $lock_instance_activities = true;
        $lock_instances = true;
        break;
      case 'run':
        //impacted tables is instance_activities (new running user)
        $lock_instance_activities = true;
        break;
      case 'send':
        //impacted tables is instance_activities (deleting/adding row)
        $lock_instance_activities = true;
        break;
      case 'complete':
        //impacted tables are instances and instance_activities
        $lock_instance_activities = true;
        $lock_instances = true;
        break;
      case 'restart':
        //nothing to do, it will be done by the run part.
        break;
    }
    // no lock on instance_activities without a lock on instances
    // to avoid changing status of an instance or deletion of an instance while impacting instance_activities
    if ($lock_instance_activities) $lock_instances = true;
    
    //1 - load data -----------------------------------------------------------------
    $_no_activity=false;
    $_no_instance=false;
    
    //retrieve some activity datas and process data
    if ($activityId==0)
    {
      $_no_activity = true;
    }
    else
    {
      $query = 'select ga.wf_activity_id, ga.wf_type, ga.wf_is_interactive, ga.wf_is_autorouted, 
              gp.wf_name as wf_procname, gp.wf_is_active, gp.wf_version, gp.wf_p_id
              from '.GALAXIA_TABLE_PREFIX.'activities ga 
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=ga.wf_p_id
                where ga.wf_activity_id = ?';
      $result = $this->query($query, array($activityId));
      $resactivity = Array();
      if (!!$result)
      {
        $resactivity = $result->fetchRow();
        $pId = $resactivity['wf_p_id'];
        //DEBUG
        //$debugactivity = implode(",",$resactivity);
        //$this->error[] = 'DEBUG: '. date("[d/m/Y h:i:s]").'activity:'.$debugactivity;
      }
      if (count($resactivity)==0)
      {
        $_no_activity = true;
      }
    }

    //retrieve some instance and process data (need process data here as well if there is no activity)
    if ($instanceId==0)
    {
      $_no_instance = true;
    }
    else
    {
      if ($lock_instances)
      {
        //we need to make a row lock now, before any read action
        $where = 'wf_instance_id='.(int)$instanceId;
        //$this->error[]= '<br> Debug:locking instances '.$where;
        if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instances', $where)))
        {
          $this->error[] = tra('failed to obtain lock on %1 table', 'instances');
          return false;
        }
      }
      $query = 'select gi.wf_instance_id, gi.wf_owner, gi.wf_status, 
              gp.wf_name as wf_procname, gp.wf_is_active, gp.wf_version, gp.wf_p_id
              from '.GALAXIA_TABLE_PREFIX.'instances gi
              INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=gi.wf_p_id
              where gi.wf_instance_id=?';
      $result = $this->query($query,array($instanceId));
      if (!!$result)
      {

        $resinstance = $result->fetchRow();
        $pId = $resinstance['wf_p_id'];
        //DEBUG
        //$debuginstance = implode(",",$resinstance);
        //$this->error[] = 'DEBUG: '. date("[d/m/Y h:i:s]").'instance:'.$debuginstance;
      }
      if (count($resinstance)==0)
      {
        $_no_instance = true;
      }
    }

    if ($_no_activity && $_no_instance)
    {
      $this->error[] = tra('Action %1 is impossible if we have no activity and no instance designated for it!',$action);
      return false;
    }
    
    //retrieve some instance/activity data
    //if no_activity or no_instance we are with out-flow/without instances activities or with instances terminated 
    //we would not obtain anything there
    if (!($_no_activity || $_no_instance))
    {
      if ($lock_instance_activities)
      {
        //we need to lock this row now, before any read action
        $where = 'wf_instance_id='.(int)$instanceId.' and wf_activity_id='.(int)$activityId;
        //$this->error[] = '<br> Debug:locking instance_activities '.$where;
        if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instance_activities', $where)))
        {
          if ($this->db->getOne('SELECT 1 FROM ' . GALAXIA_TABLE_PREFIX . 'instance_activities WHERE ' . $where))
          {
            $this->error[] = tra('failed to obtain lock on %1 table','instances_activities');
            return false;
          }
          else
          {
            $this->error[] = tra("This instance doesn't exist in this activity. Probably, this instance has already been executed");
            return false;
          }
        }
      }
      $query = 'select gia.wf_instance_id, gia.wf_user, gia.wf_status
              from '.GALAXIA_TABLE_PREFIX.'instance_activities gia
                where gia.wf_activity_id = ? and gia.wf_instance_id = ?';
      $result = $this->query($query, array($activityId, $instanceId));
      $res_inst_act = Array();
      if (!!$result)
      {
        $res_inst_act = $result->fetchRow();
        //DEBUG
        //$debuginstact = implode(",",$res_inst_act);
        //$this->error[] = 'DEBUG: '. date("[d/m/Y h:i:s]").'instance/activity:'.$debuginstact;

      }
    }

    //Now that we have the process we can load config values
    //$this->error[] = 'DEBUG: load config values for process:'.$pId;
    $this->loadConfigValues($pId);
    //$debuconfig = '';foreach ($this->processesConfig[$pId] as $label => $value){$debugconfig .= ':'.$label.'=>'.$value;} $this->error[] = 'DEBUG: config:'.$debugconfig;


    
    //2 - decide which tests must be done ------------------------------------------------
    //init tests
    $_check_active_process = false; // is the process is valid?
    $_check_instance = false; //have we got an instance?
    $_check_instance_status = array(); //use to test some status between 'active','exception','aborted','completed'
    $_fail_on_exception = false; //no comment
    $_check_activity = false; //have we got an activity?
    //is there a relationship between instance and activity? this one can be decided already
    $_check_instance_activity =  !(($_no_instance) || ($_no_activity));
    $_bypass_user_role_if_owner = false; //if our user is the owner we ignore user tests
    $_bypass_user_on_non_interactive = false; //if activty is not interactive we do not perform user tests
    $_bypass_user_if_admin = false; //is our user a special rights user?
    $_bypass_instance_on_pseudo = false; //should we jump the instance check when in 'start' activity?
    $_check_is_user = false; //is the actual_user our user?
    $_check_is_not_star = false; //is the actual <>*?
    $_check_is_star = false; // is the actual user *?
    $_check_is_in_role = false; //is our user in associated roles with readonly=false?
    $_check_is_in_role_in_readonly = false; //is our user in associated roles?
    $_check_no_view_activity = false; //is the process having no view activities?
    $_check_is_admin_only = false; //is the action vaible only for admins?
    
    //first have a look at the action asked
    switch($action)
    {
      case 'restart':
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // authorization is given to admin only
        $_check_active_process          = true;
        $_check_activity                = true;
        $_check_instance		= true;
        $_fail_on_exception             = true;
        $_check_is_admin_only		= true;
        break;
      case 'view':
        //process can be inactive
        //we need an existing instance
        //no activity needed
        $_check_instance = true;
        $_bypass_user_if_admin	= true;
        //but be carefull the view function is forbidden on process having the viewrun action with activities
        $_check_no_view_activity = true;
        break;
      case 'viewrun':
        //process can be inactive
        //we need an existing instance
        //we need an activity
        //need a read-only role at least on this activity
        $_check_instance = true;
        $_bypass_user_if_admin	= true;
        $_check_activity = true; 
        $_check_is_in_role_in_readonly = true;
        //The view type is a special activity related to all instances
        $_check_instance_activity = false;
        break;
      case 'complete':
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // (the 'view' activity is not 'in_flow' and has instance, but no relashionship, no need to 
        // test it here or later for grab or others actions). 
        // warning we can complete a start activity, in this case it is the contrary, we musn't have an instance
        // we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // authorization is given to currentuser only,
        // for interactive activities (except start), instance user need to be the actual user
        // 'view' cannot be completed
        $_check_active_process		= true;
        $_check_instance        	= true;
        $_bypass_instance_on_pseudo	= true;
        $_fail_on_exception		= true;
        $_check_activity	        = true;
        $_bypass_user_on_non_interactive = true;
        $_check_is_user			= true;
        $_check_is_not_star		= true;
        break;
      case 'grab': 
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, role, never owner actually
        // TODO: add conf setting to give grab access to owner (that mean run access as well maybe)
        // current user MUST be '*' or user (no matter to grab something we already have)
        // check is star is done after check_is_user which can be false
        $_check_active_process	= true;
        $_check_activity	= true;
        $_check_instance	= true;
        $_check_is_user		= true;
        $_check_is_star		= true;
        $_bypass_user_if_admin	= true;
        $_check_is_in_role	= true;
        break;
      case 'release' :
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, maybe role, maybe owner,
        // current must not be '*'
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_is_user		= true;
        $_check_is_not_star 	= true;
        $_bypass_user_if_admin	= true;
        if ($this->processesConfig[$pId]['role_give_release_right']) $_check_is_in_role 		= true;
        if ($this->processesConfig[$pId]['ownership_give_release_right']) $_bypass_user_role_if_owner 	= true;
        break;
      case 'exception':
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, maybe role, maybe owner,
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_instance_status = array('active');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_exception_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_exception_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'resume':
        // like exception but inversed activity status
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_instance_status = array('exception');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_exception_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_exception_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'abort':
        // process can be inactive
        // we do not need an activity
        // we need an instance
        // authorization are given to currentuser, maybe role, maybe owner,
        // TODO: add conf setting to refuse abort by user
        $_check_instance        = true;
        $_check_instance_status = array('active','exception','completed');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_abort_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_abort_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'run':
        // the hell door:
        // all activities can be runned, even without instance, even if non interactive
        // if we have one we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // for interactive activities (except start and standalone), instance user need to be the actual user
        // run is ok if user is in role and actual user is '*', no rights for owner actually
        // no user bypassing on admin user, admin must grab (release if needed) the instance before
        $_check_active_process		= true;
        $_check_activity	        = true;
        $_fail_on_exception		= true;
        $_bypass_user_on_non_interactive = true;
        $_check_is_user			= true;
        $_check_is_star			= true;
        $_check_is_in_role		= true;
        break;
      case 'send':
        // we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // authorization are given to currentuser, maybe role, no rights for owner actually
        // run is ok if user is in role and actual user is '*'
        // no user bypassing on admin user, admin must grab (release if needed) the instance before
        $_check_active_process          = true;
        $_check_activity                = true;
        $_fail_on_exception             = true;
        $_bypass_user_if_admin		= true;
        $_check_is_user                 = true;
        $_check_is_star			= true;
        $_check_is_in_role		= true;
        break;
    }
    
    //3- now perform asked tests ---------------------------------------------------------------------
    if ($_check_active_process) // require an active process?
    {
      //$this->error[] = 'DEBUG: check active process';
      if ($_no_instance) //we need an instance or an activity to perfom the check
      {
        //we cannot be there without instance and without activity, we now we have one activity at least
        if (!($resactivity['wf_is_active']=='y'))
        {
          $this->error[] = tra('Process %1 %2 is not active, action %3 is impossible', $resactivity['wf_procname'], $resactivity['wf_version'], $action);
          return false;
        }
      }
      else
      {
        if (!($resinstance['wf_is_active']=='y'))
        {
          $this->error[] = tra('Process %1 %2 is not active, action %3 is impossible', $resinstance['wf_procname'], $resactivity['wf_version'], $action);
          return false;
        }
      }
    }
    
    if ($_check_instance)
    {
      //$this->error[] = 'DEBUG: check instance';
      if ( (!($_bypass_instance_on_pseudo)) && ($_no_instance))
      {
        $this->error[] = tra('Action %1 needs an instance and instance %2 does not exists', $action, $instanceId);
        return false;
      }
    }
    
    if ($_check_activity)
    {
      //$this->error[] = 'DEBUG: check activity';
      if ($_no_activity)
      {
        $this->error[] = tra('Action %1 needs an activity and activity %2 does not exists', $action, $activityId);
        return false;
      }
    }
    
    if ($_check_instance_activity) //is there a realtionship between instance and activity
    {
      //$this->error[] = 'DEBUG: check activity-instance relationship'.count($res_inst_act);
      if ( (!isset($res_inst_act)) || empty($res_inst_act) || (count($res_inst_act)==0) )
      {
        $this->error[] = tra('Instance %1 is not associated with activity %2, action %3 is impossible.', $instanceId, $activityId, $action);
        return false;
      }
    }
    
    if (!(count($_check_instance_status) == 0)) //use to test some status between 'active','exception','aborted','completed'
    {
      //DEBUG
      //$debug_status = implode(",",$_check_instance_status);
      //$this->error[] = 'DEBUG: check instance status, actually :'.$resinstance['wf_status'].' need:'.$debug_status;
      if (!(in_array($resinstance['wf_status'],$_check_instance_status)))
      {
        $this->error[] = tra('Instance %1 is in %2 state, action %3 is impossible.', $instanceId, $resinstance['wf_status'], $action);
        return false;
      }
    }
    if (($_fail_on_exception) && ($resinstance['wf_status']=='exception'))
    {
        $this->error[] = tra('Instance %1 is in exception, action %2 is not possible.', $instanceId, $action);
        return false;
    }
    
    // Test on the process to see if he has a view activity
    if ($_check_no_view_activity)
    {
      if (!(isset($this->pm)))
      {
        $this->pm = &Factory::newInstance('ProcessManager');
      }
      //$this->error[] = 'DEBUG: checking to see if there is no view activities on process :'.$pId.':'.$this->pm->get_process_view_activity($pId);
      /** whithout this check we can see the instance data if any view activity exists */
	/*if ($this->pm->get_process_view_activity($pId))
      {
        $this->error[] = tra('This process has a view activity. Access in view mode is granted only for this view activty.');
        return false;
      }*/
    }
    
    // user tests ---------------
    $checks = true;
    //is our actual workflow user a special rights user?
    // TODO test actual workflow user diff of $user
    //$this->error[] = 'DEBUG: user can admin instance :'.galaxia_user_can_admin_instance().' bypass?:'.$_bypass_user_if_admin;
    $is_admin = galaxia_user_can_admin_instance();
    if (! ( (($_bypass_user_if_admin) && ($is_admin)) || (($_check_is_admin_only) && ($is_admin))) )
    {
      //if our user is the owner we ignore user tests
      //$this->error[] = 'DEBUG: user is owner :'.$resinstance['wf_owner'].' bypass?:'.$_bypass_user_role_if_owner;
      if (!( ($_bypass_user_role_if_owner) && ((int)$resinstance['wf_owner']==(int)$user) ))
      {
        //$this->error[] = 'DEBUG: no_activity:'.$_no_activity.' interactive? :'.$resactivity['wf_is_interactive'].' bypass?:'.$_bypass_user_on_non_interactive;
        //if activity is not interactive we do not perform user tests
        if (!( (!($_no_activity)) && ($_bypass_user_on_non_interactive) && ($resactivity['wf_is_interactive']=='n') ))
        {
          //$this->error[] = 'DEBUG: no bypassing done:';
          //is the actual_user our user?
          if ( (!($_no_instance)) && $_check_is_user) 
          {
            //$this->error[] = 'DEBUG: check user is actual instance user:'.$user.':'.$res_inst_act['wf_user'];
            if (!((int)$res_inst_act['wf_user']==(int)$user))
            {
              //user test was false, but maybe we'll have better chance later
              $checks = false;
            }
          }
          // special '*' user
          if ($res_inst_act['wf_user']=='*')
          {
            //$this->error[] = 'DEBUG: we have the special * user:';
            //is the actual *?
            if ($_check_is_star)
            {
              // redemption here
              //$this->error[] = 'DEBUG Ok, we have a star';
              $checks = true;
            }
            
            //is the actual <>*?
            if ($_check_is_not_star)
            {
              //no redemption here
              $this->error[] = tra('Action %1 is impossible, there are no user assigned to this activity for this instance', $action);
              return false;
            }
            //perform the role test if actual user is '*'
            //$this->error[] = 'DEBUG: role checking?:'.$_check_is_in_role;
            if ($_check_is_in_role)
            {
              //$this->error[] = 'DEBUG: we have *, checking role of user:'.$user;
              $checks=$this->checkUserAccess($user, $activityId);
            }
            //$this->error[] = 'DEBUG: role checking in read-only at least?:'.$_check_is_in_role_in_readonly;
            if ($_check_is_in_role_in_readonly)
            {
              //$this->error[] = 'DEBUG: we have *, checking readonly role of user:'.$user;
              $checks=$this->checkUserAccess($user, $activityId, true);
            }        
          }
          else
          {
            if (substr($res_inst_act['wf_user'], 0, 1) == 'p')
            {
              $role_id = substr($res_inst_act['wf_user'], 1);
              $groups = galaxia_retrieve_user_groups($user);
              $sql = "SELECT 1 ";
              $sql .= "FROM " . GALAXIA_TABLE_PREFIX . "user_roles WHERE (";
              if (is_array($groups))
              {
                foreach ($groups as &$group)
                  $group = "'{$group}'";
                $sql .= "(wf_user IN (" . implode(',',$groups) . ") AND wf_account_type = 'g') OR ";
              }
              $sql .= "(wf_user = '$user' AND wf_account_type = 'u')) AND ";
              $sql .= "(wf_role_id = $role_id)";
              $result = $this->getOne($sql);

              if (is_null($result))
                $checks = false;
              else
                $checks = true;

            }
            else
            {
              //we have not *, do we need * as the actual? (done only if check_is_user is false)
              //notice that if check_user was false and we have not the '*' user and if you do not want
              //the check_is_star it means the user can bypass the actual user if you have a check_is_in_role ok!
              if ( (!($checks)) && ($_check_is_star))
              {
                // that was necessary
                $this->error[] = tra('Action %1 is impossible, another user is already in place', $action);
                return false;
              }
              //is our user in associated roles (done even if check_is_user was true)
              //$this->error[] = 'DEBUG: role checking?:'.$_check_is_in_role;
              if ($_check_is_in_role)
              {
                //$this->error[] = 'DEBUG: we have not *, checking role for user:'.$user;
                $checks=$this->checkUserAccess($user, $activityId);
              }
              //$this->error[] = 'DEBUG: role checking in read-only at least?:'.$_check_is_in_role_in_readonly;
              if ($_check_is_in_role_in_readonly)
              {
                //$this->error[] = 'DEBUG: we have not *, checking role in read-only for user:'.$user;
                $checks=$this->checkUserAccess($user, $activityId, true);
              }
            }
          }
        }
      }
    }
    //$this->error[] = 'DEBUG: final check:'.$checks;
    return $checks;
  }

  /**
   * Gets avaible actions for a given user on some activity and instance assuming he already have access to it.
   * To be able to decide this function needs all the parameters, use the GUI object equivalent function if you want less parameters.
   * 
   * @access public
   * @param int $user User id
   * @param int $instanceId Instance id
   * @param int $activityId Activity id
   * @param bool $readonly It has to be true if the user has only read-only level access with his role mappings
   * @param int $pId Process id
   * @param string $actType Activity type
   * @param string $actInteractive 'y' or 'n' and is the activity interactivity
   * @param string $actAutorouted 'y' or 'n' and is the activity routage 
   * @param string $actStatus Activity status ('running' or 'completed')
   * @param int $instanceOwner Instance owner id
   * @param string $instanceStatus Instance status ('running', 'completed', 'aborted' or 'exception')
   * @param mixed $currentUser Current instance/activity user id or '*'.
   * @param bool $viewactivity False if the process has no view activity, else it's the id of the view activity
   * @return array An array of this form:
   * array('action name' => 'action description')
   * 'actions names' are: 'grab', 'release', 'run', 'send', 'view', 'viewrun', 'exception', 'resume', 'monitor'
   * note that for the 'viewrun' key value is an array with a 'lang' key for the translation and a 'link' key for the view activity id
   * Some config values can change theses rules but basically here they are:
   *	* 'grab'	: be the user of this activity. User has access to it and instance status is ok.
   *	* 'release'	: let * be the user of this activity. Must be the actual user or the owner of the instance.
   *	* 'run'	: run an associated form. This activity is interactive, user has access, instance status is ok.
   *	* 'send'	: send this instance, activity was non-autorouted and he has access and status is ok.
   *	* 'view'	: view the instance, activity ok, always avaible if no view activity on the process except for start or standalone act.
   *	* 'viewrun'	: view the instance in a view activity, need role on view activity, always avaible except for start or standalone act.
   *	* 'abort'	: abort an instance, ok when we are the user
   *	* 'exception' : set the instance status to exception, need to be the user 
   *	* 'resume'	: back to running when instance status was exception, need to be the user
   *	* 'monitor' : admin the instance, for special rights users
   * 'actions description' are translated explanations like 'release access to this activity'
   * This function will as well load process configuration which could have some impact on the rights. 
   * Theses config data will be cached during the existence of this WfSecurity object.
   * WARNING: this is a snapshot, the engine give you a snaphsot of the rights a user have on an instance-activity
   * at a given time, this is not meaning theses rights will still be there when the user launch the action.
   * You should absolutely use the GUI Object or runtime to execute theses actions (except monitor) and they could be rejected.
   * WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via a 
   * GUI object theses access rights are allready checked (for example we do not check your readonly parameter is true).
   * In fact this function is GUI oriented, it is not granting rights
   */
  function getUserActions($user, $instanceId, $activityId, $readonly, $pId, $actType, $actInteractive, $actAutorouted, $actStatus, $instanceOwner, $instanceStatus, $currentUser, $view_activity)
  {
    $result= array();//returned array
    $stopflow=false;//true when the instance is in a state where the flow musn't advance
                    //ie: we can't send or run it
    $deathflow=false;//true when the instance is in a state where the flow will never advance anymore
                    //ie: we can't send, run, grab, release, exception or resume it
    $associated_instance=true;//false when no instance is associated with the activity
                    // ie: we cannot send, grab, release, exception, resume or view the instance but we can run
                    // it covers standalone activities and start activities not completed
    $_run  = false;
    $_send = false;
    $_grab = false;
    $_release = false;
    $_abort = false;
    $_view = false;
    $_viewrun = false;
    $_resume = false;
    $_exception = false;
    // this can be decided right now, it depends only on user rights
    $_monitor = galaxia_user_can_admin_instance($user);

    $this->loadConfigValues($pId);

    // check the instance status
    // 'completed' => no action except 'view'/'viewrun' or 'abort' or 'monitor'
    // 'aborted' =>  no action except 'view'/'viewrun' or 'monitor'
    // 'active' => ok first add 'exception'
    // 'exception' => first add 'resume', no 'run' or 'send' after    
    $_view = true;
    if ($view_activity)
    {
      //we should have a 'viewrun' instead of a 'view' action, but maybe we do not have access on this view activity
      //this access right will be checked by gui_get_process_view_activity
      $_viewrun = true;      
      $_iframe_height =  $this->processesConfig[$pId]['iframe_view_height'];
    }
       
    
    //on readonly mode things are simplier, no more rights
    if (!($readonly))
    {
      if ($instanceStatus == 'aborted')
      {
        $deathflow=true;
      }
      else
      {
        // first check ABORT
        if ( ($user==$currentUser) ||
             (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_abort_right'])) ||
             ($this->processesConfig[$pId]['role_give_abort_right']))
        {// we are the assigned user 
         //OR we are the owner and it gives rights
         //OR we have the role and it gives rights
         $_abort =true;
        }
        // now handle resume and exception but before detect completed instances
        if ($instanceStatus == 'completed')
        {
          $deathflow=true;
        }
        else
        {
          if ($instanceStatus == 'exception')
          {
            $stopflow = true;
            if ( ($user==$currentUser) ||
                 (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_exception_right'])) ||
                 ($this->processesConfig[$pId]['role_give_exception_right']))
            {// we are the assigned user OR we are the owner and it gives rights
              $_resume = true;
            }
          }
          elseif ($instanceStatus == 'active')
          {
            //handle rules about ownership
            if ( ($user==$currentUser) ||
                (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_exception_right'])) ||
                ($this->processesConfig[$pId]['role_give_exception_right']))
            {// we are the assigned user OR we are the owner and it gives rights
              $_exception = true;
            }
          }
        }
      }
  
      //now we check the activity
      // start (only uncompleted) and standalone activities have no instance associated.
      // If we are not in a 'stop' or 'death' flow we can check interactivity
      // interactive -> run
      // not interactive -> send (except for 'standalone')
      // if we are not in a 'death flow' we can add grab and release actions
      if ( ($actType=='standalone') || (($actType=='start') && (!($actStatus=='completed'))) )
      {
        $associated_instance=false;
        // there's no instance to view in fact
        $_view = false;
        $_viewrun = false;
      }
      if (($actInteractive=='y') && (!($deathflow)))
      {
        if ($associated_instance)
        {
            if ($currentUser=='*')
            {
              $_grab = true;
            }
            else
            {
              if ( ($user==$currentUser) ||
                 (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_release_right'])) ||
                 ($this->processesConfig[$pId]['role_give_release_right']))
              {// we are the assigned user 
               //OR we are the owner and it gives rights
               //OR we have the role and it gives rights
                $_release = true;
              }
            }
        }
        if (($actStatus=='running') && !($stopflow) && !($deathflow))
        {
          if (($currentUser=='*') || ($currentUser==$user))
          {
            $_run = true;
          }
        }
      }
      //for non autorouted activities we'll have to send, useless on standalone but usefull for start
      //activities which can be sended if completed and of course for all other activities
      if ($actAutorouted=='n')
      {
        if ($associated_instance)
        {
          if (($actStatus=='completed') && !($stopflow) && !($deathflow))
          {
            $_send = true;
          }
        }
      }
    }//end if !$readonly
	
    //build final array
    if ($_run) $result['run']=tra('Execute this activity');
    if ($_send) $result['send']=tra('Send this instance to the next activity');
    if ($_grab) $result['grab']=tra('Assign me this activity');
    if ($_release) $result['release']=tra('Release access to this activity');
    if ($_abort) $result['abort']=tra('Abort this instance');
    if ($_view) $result['view']=tra('View this instance');
    if ($_viewrun) $result['viewrun']= array('lang' => tra('View this instance'), 'link' => $view_activity);
    if ($_iframe_height) $result['viewrun']['iframe_height'] = $_iframe_height;
    if ($_resume) $result['resume']=tra('Resume this exception instance');
    if ($_exception) $result['exception']=tra('Exception this instance');
    if ($_monitor) $result['monitor']=tra('Monitor this instance');   
 
    return $result;
  }

  
}


?>
