<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');

/**
 * Can be viewed by the user like an Instance, it is in fact more than an instance
 * as it handle concurrency and part of the core execution of the instance while avoiding
 * bad manipulation of the instance
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class WfRuntime extends Base 
{  
  /**
   * @var array processes config values cached for this object life duration, init is done at first use for the only process associated with this runtime object
   * @access public
   */
  var $conf= Array();  
  /**
   * @var object $activity
   * @access public 
   */
  var $activity = null;
  /**
   * @var object $instance
   * @access public 
   */
  var $instance = null;
  /**
   * @var int $instance_id
   * @access public 
   */
  var $instance_id = 0;  
  /**
   * @var int $activity_id
   * @access public 
   */
  var $activity_id = 0;
  /**
   * @var object $process Retrieve process information
   * @access public 
   */
  var $process = null;
  /**
   * @var object $workitems  Reference to $instance->workitems
   * @access public 
   */
  var $workitems = null;
  /**
   * @var object $activities Reference to $instance->activities
   * @access public 
   */  
  var $activities = null;
  /**
   * @var object $security Security object
   * @access public 
   */  
  var $security = null;
  /**
   * @var bool $transaction_in_progress Transaction state
   * @access public 
   */  
  var $transaction_in_progress = false; 
  /**
   * @var bool $debug Debug state
   * @access public 
   */   
  var $debug=false;
  /**
   * @var bool $auto_mode Automatic mode state, non-interactive for instance, big impact on error handling
   * @access public 
   */  
  var $auto_mode=false;

  /**
   * @var string $activityCompleteMessage Holds a message which is displayed in the activity completion page
   * @access public
   */
  var $activityCompleteMessage = '';

  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object WfRuntime instance
   * @access public
   */
  function WfRuntime() 
  {
    $this->child_name = 'WfRuntime';
    parent::Base();

    //first the activity is not set
    $this->activity = null;
    $this->instance = &Factory::newInstance('Instance');
    $this->process = &Factory::newInstance('Process');
    $this->security = &Factory::getInstance('WfSecurity');
  }

  /**
   * Collect errors from all linked objects which could have been used by this object
   * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
   * 
   * @param bool $debug false by default, if true debug messages can be added to 'normal' messages
   * @param string $prefix appended to the debug message
   * @return void 
   * @access private
   */
  function collect_errors($debug=false, $prefix = '')
  {
    parent::collect_errors($debug, $prefix);
    if (isset($this->instance) && !!($this->instance)) $this->error[] = $this->instance->get_error(false, $debug, $prefix);
    if (isset($this->process) && !!($this->process)) $this->error[] = $this->process->get_error(false, $debug, $prefix);
    if (isset($this->security) && !!($this->security)) $this->error[] = $this->security->get_error(false, $debug, $prefix);
    if (isset($this->activity) && !!($this->activity)) $this->error[] = $this->activity->get_error(false, $debug, $prefix);
  }

  /**
   * Ends-up dying and giving a last message
   * 
   * @param string $last_message last sentence
   * @param bool $include_errors false by default, if true we'll include error messages
   * @param bool $debug false by default, if true you will obtain more messages, if false you could obtain those
   * @param bool $dying true by default, tells the engine to die or not
   * messages as well if this object has been placed in debug mode with setDebug(true)
   * recorded by this runtme object.
   * @access public
   * @return void
   */
  function fail($last_message, $include_errors = false, $debug=false, $dying=true)
  {
    $the_end = '';
    //see if local objects have been set to enforce it
    if ($this->debug) $debug = true;
    if ($this->auto_mode) $dying = false;
    
    if ($include_errors)
    {
      $the_end = $this->get_error(false, $debug).'<br />';
    }
    $the_end .= $last_message;
    if ($this->transaction_in_progress)
    {
      //we had a transaction actually, we mark a fail, this will force Rollback
      $this->db->FailTrans();
      $this->db->CompleteTrans();
    }
    if ($dying)
    {
      //this will make the session die
      galaxia_show_error($the_end, $dying);
    }
    else
    {
      //this will NOT BREAK the session!
      return $the_end;
    }
  }

  /**
   * Loads the config values for the process associated with the runtime
   * config values are cached while this WfRuntime object stays alive
   * 
   * @param array $arrayconf Config array with default value, where key is config option name and value is default value
   * @access private 
   * @return array Values associated with the current process for the asked config options and as well for som WfRuntime internal config options
   */
  function &getConfigValues(&$arrayconf)
  {
    if (!(isset($this->process)))
    {
      $this->loadProcess();
    }
    $arrayconf['auto-release_on_leaving_activity'] = 1;  
    $this->conf =  $this->process->getConfigValues($arrayconf);
    return $this->conf;
  }

  /**
   * Loads instance, the activity and the process, needed by the runtime engine to 'execute' the activity
   * 
   * @param int $activityId activity id, the activity we will run
   * @param int $instanceId instance Id, can be empty for a start or standalone activity
   * @return bool 
   * @access public
   */
  function &LoadRuntime($activityId,$instanceId=0)
  {
    // load activity
    if (!($this->loadActivity($activityId, true, true)))
    {
      return false;
    }
    //interactive or non_interactive?
    $this->setAuto(!($this->activity->isInteractive()));
    //load instance
    if (!($this->loadInstance($instanceId)))
    {
      return false;
    }
    // load process
    if (!($this->loadProcess()))
    {
      return false;
    }
    
    //ensure the activity is not completed
    $this->instance->setActivityCompleted(false);
	$this->instance->activityID = $activityId;
    
    //set the workitems and activities links
    $this->workitems =& $this->instance->workitems;
    $this->activities =& $this->instance->activities;
    return true;
  }
  
  /**
   * Retrieves the process object associated with the activity
   * 
   * @param int $pId Process id of the process you want, if you do not give it we will try to take it from the activity
   * @return mixed Process object of the right type or false
   * @access private
   */
  function loadProcess($pId=0)
  {
    if ( (!(isset($this->process))) || ($this->process->getProcessId()==0))
    {
      if ( (empty($pId)) || (!($pId)) )
      {
        $pId = $this->activity->getProcessId();
        if ( (empty($pId)) || (!($pId)) )
        {
          //fail can return in auto mode or die
          $errors = $this->fail(tra('No Process indicated'),true, $this->debug, !($this->auto_mode));
          $this->error[] = $errors;
          return false;
        }
      }
      if ($this->debug) $this->error[] = 'loading process '.$pId;
      $this->process->getProcess($pId);
    }
    return true;
  }
  
  /**
   * Gets current process instance
   * 
   * @return object Process instance
   * @access public
   */
  function &getProcess()
  {
    return $this->process;
  }

  /**
   * Retrieves the activity of the right type from a baseActivity Object
   * 
   * @param int $activity_id activity_id you want
   * @param bool $with_roles will load the roles links on the object
   * @param bool $with_agents will load the agents links on the object
   * @return mixed Activity object of the right type or false
   * @access private
   */
  function loadActivity($activity_id, $with_roles= true,$with_agents=false)
  {
    if ( (empty($activity_id)) || (!($activity_id)) )
    {
      //fail can return in auto mode or die
      $errors = $this->fail(tra('No activity indicated'),true, $this->debug, !($this->auto_mode));
      $this->error[] = $errors;
      return false;
    }
    $base_activity = &Factory::newInstance('BaseActivity');
    $this->activity =& $base_activity->getActivity($activity_id, $with_roles, $with_agents);
    if (!$this->activity)
    {
      $errors = $this->fail(tra('failed to load the activity'),true, $this->debug, !($this->auto_mode));
      $this->error[] = $errors;
      return false;
    }
    $this->activity_id = $activity_id;
    $this->error[] =  $base_activity->get_error();
    if ($this->debug) $this->error[] = 'loading activity '.$activity_id;
    return true;
  }
  
  /**
   * Gets current activity instance
   * 
   * @return object Activity instance
   * @access public
   */  
  function &getActivity()
  {
    return $this->activity;
  }
  
  /**
   * Gets the instance which could be an empty object
   * 
   * @param int $instanceId is the instance id
   * @return mixed Instance object which can be empty or string if something was turning bad
   * @access public
   */
  function loadInstance($instanceId)
  {
    $this->instance_id = $instanceId;
    $this->instance->getInstance($instanceId);
    if ( ($this->instance->getInstanceId()==0) 
      && (! (($this->activity->getType()=='standalone') || ($this->activity->getType()=='start') )) )
    {
      //fail can return in auto mode or die
      $errors = $this->fail(tra('no instance avaible'), true, $this->debug, !($this->auto_mode));
      $this->error[] = $errors;
      return false;
    }
    if ($this->debug) $this->error[] = 'loading instance '.$instanceId;
    return true;
  }
  
  /**
   * Perform necessary security checks at runtime before running an activity
   * This will as well lock the tables via the security object
   * It should be launched in a transaction
   * 
   * @return bool true if ok, false if the user has no runtime access
   * @access public
   */
  function checkUserRun()
  {
    if ($this->activity->getType()=='view')
    {
      //on view activities  the run action is a special action
      $action = 'viewrun';
    }
    else
    {
      $action = 'run';
    }
    //this will test the action rights and lock the necessary rows in tables in case of 'run'
    $result = $this->security->checkUserAction($this->activity_id,$this->instance_id,$action);
    $this->error[] =  $this->security->get_error(false, $this->debug);
    if ($result)
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Perform necessary security checks at runtime
   * This will as well lock the tables via the security object
   * It should be launched in a transaction
   * 
   * @return bool true if ok, false if the user has no runtime access instance and activity are unsetted in case of false check
   * @access public
   */
  function checkUserRelease()
  {
    //the first thing to scan if wether or not this process is configured for auto-release
    if ( (isset($this->conf['auto-release_on_leaving_activity'])) && ($this->conf['auto-release_on_leaving_activity']))
    {
      //this will test the release rights and lock the necessary rows in tables in case of 'release'
      $result = $this->security->checkUserAction($this->activity_id,$this->instance_id,'release');
      $this->error[] =  $this->security->get_error(false, $this->debug);
      if ($result)
      {
        //we are granted an access to release but there is a special bad case where
        //we are a user authorized at releasing instances owned by others and where
        //this instance is owned by another (for some quite complex reasons). 
        //we should not release this new user!!
        //Then this is auto-release, not a conscious act and so we will release only 
        //if we can still grab this instance (avoiding the bad case)
        
        //test grab before release
        if ($this->checkUserRun())
        {
          return true;
        }
      }
    }
    return false;
  }
  
  /**
   * Sets/unsets the WfRuntime in debug mode
   * 
   * @param bool $debug_mode true by default, set it to false to disable debug mode
   * @access public
   * @return void 
   */
  function setDebug($debug_mode=true)
  {
    $this->debug = $debug_mode;
  }
  
  /**
   * Sets/unsets the WfRuntime in automatic mode. i.e : executing
   * non-interactive or interactive activities. Automatic mode have big impacts
   * on error handling and on the way activities are executed
   * 
   * @param bool $auto_mode true by default, set it to false to disable automatic mode
   * @access public
   * @return void 
   */
  function setAuto($auto_mode=true)
  {
    $this->auto_mode = $auto_mode;
  }
  
  /**
   * This function will start a transaction, call it before setActivityUser()
   * 
   * @access public
   * @return void 
   */
  function StartRun()
  {
    $this->transaction_in_progress =true;
    $this->db->StartTrans();
  }
  
  /**
   * This function ends the transactions started in StartRun()
   * 
   * @access public
   * @return void
   */
  function EndStartRun()
  {
    if ($this->transaction_in_progress) 
    {
      $this->db->CompleteTrans();
      $this->transaction_in_progress =false;
    }
  }
  
  /**
   * For interactive activities this function will set the current user on the instance-activities table. 
   * This will prevent having several user using the same activity on the same intsance at the same time
   * But if you want this function to behave well you should call it after a checkUserRun or a checkUserRelease
   * and inside a transaction. Theses others function will ensure the table will be locked and the user
   * is really granted the action
   * 
   * @param bool $grab true by default, if false the user will be set to '*', releasing the instance-activity record
   * @access public
   * @return bool 
   */
  function setActivityUser($grab=true)
  {
    if(isset($GLOBALS['user']) && !empty($this->instance->instanceId) && !empty($this->activity_id)) 
    {
      if ($this->activity->isInteractive())
      {// activity is interactive and we want the form, we'll try to grab the ticket on this instance-activity (or release)
        if ($grab)
        {
          $new_user = $GLOBALS['user'];
        }
        else
        {
          $new_user= '*';
        }
        if (!$this->instance->setActivityUser($this->activity_id,$new_user))
        {
           //fail can return in auto mode or die
           $errors = $this->fail(lang("You do not have the right to run this activity anymore, maybe a concurrent access problem, refresh your datas.", true, $this->debug, !($this->auto_mode)));
           $this->error[] = $errors;
           return false;
        }
      }// if activity is not interactive there's no need to grab the token
    }
    else
    {
      //fail can return in auto mode or die
      $errors= $this->fail(lang("We cannot run this activity, maybe this instance or this activity do not exists anymore.", true, $this->debug, !($this->auto_mode)));
      $this->error[] = $errors;
      return false;
    }    
  }
  
  /**
   * Tries to give some usefull info about the current runtime
   * 
   * @return array associative arrays with keys/values which could be usefull
   * @access public
   */
  function &getRuntimeInfo()
  {
    $result = Array();
//    _debug_array($this->instance);
    if (isset($this->instance))
    {
      $result['instance_name'] = $this->instance->getName();
      $result['instance_owner'] = $this->instance->getOwner();
    }
    if (isset($this->activity))
    {
      $result['activity_name'] = $this->activity->getName();
      $result['activity_id'] = $this->activity_id;
      $result['activity_type'] = $this->activity->getType();
    }
    return $result;
  }

  /**
   * This part of the runtime will be runned just after the activity code inclusion.
   * We are in fact after all the "user code" part. We should decide what to do next
   * 
   * @param bool $debug false by default
   * @return array an array which must be analysed by the application run class. It contains 2 keys
   ** 'action' : value is a string is the action the run class should do
   *	* 'return' should return the result we just returned (in auto mode, to propagate infos)
   *	* 'loop' should just loop on the form, i.e.: do nothing
   *	* 'leaving' should show a page for the user leaving the activity (Cancel or Close without completing)
   *	* 'completed' should show a page for the user having completed the activity
   ** 'engine_info' : value is an array is an array containing a lot of infos about what was done by the engine
   *	especially when completing the instance or when executing an automatic activity
   * @access public
   */
  function handle_postUserCode($debug=false)
  {
    $result = Array();
    
     // re-retrieve instance id which could have been modified by a complete
     $this->instance_id	= $this->instance->getInstanceId();
     
     //synchronised instance object with the database
     $this->instance->sync();

    // for interactive activities in non-auto mode:
    if (!($this->auto_mode) && $this->activity->isInteractive())
    {
      if ($this->instance->getActivityCompleted())
      {
        // activity is interactive and completed, 
        // we have to continue the workflow
        // and send any autorouted activity which could be after this one
        // this is not done in the $instance->complete() to let
        // xxx_pos.php code be executed before sending the instance

        $result['engine_info'] =& $this->instance->sendAutorouted($this->activity_id);

        // application should display completed page
        $result['action']='completed';
        return $result;
      }
      // it hasn't been completed
      else
      {
        if ($GLOBALS['workflow']['__leave_activity'])
        {
          // activity is interactive and the activity source set the 
          // $GLOBALS['workflow'][__leave_activity] it's a 'cancel' mode.
          // we redirect the user to the leave activity page
          $result['action']='leaving';
          return $result;
        }
        else
        { 
          //the activity is not completed and the user doesn't want to leave
          // we loop on the form
          $result['action']='loop';
          return $result;
        }
      }
    }
    else
    { 
      // in auto mode or with non interactive activities we return engine info
      // and we collect our errors, we do not let them for other objects
      $this->collect_errors($debug);
      $result['engine_info']['debug'] = implode('<br />',array_filter($this->error));
      $result['engine_info']['info'] =& $this->getRuntimeInfo();
      $result['action'] = 'return';
      return $result;
    }
  }

  /**
   * Gets the the 'Activity Completed' status
   * 
   * @access public
   * @return string 
   */
  function getActivityCompleted()
  {
    return $this->instance->getActivityCompleted();
  }

  
  //----------- Instance public function mapping -------------------------------------------
  
  /**
   * Sets the next activity to be executed, if the current activity is
   * a switch activity the complete() method will use the activity setted
   * in this method as the next activity for the instance. 
   * Note that this method receives an activity name as argument (Not an Id)
   * and that it does not need the activityId like the instance method
   * 
   * @param string $actname name of the next activity
   * @return bool
   * @access public
   */
  function setNextActivity($actname) 
  {
    return $this->instance->setNextActivity($this->activity_id,$actname);
  }

  /**
   * Sets the user that must perform the next 
   * activity of the process. this effectively "assigns" the instance to
   * some user
   * 
   * @param int $user the next user id
   * @param string $activityName The name of the activity that the user will be able to executed. '*' means the next activity.
   * @return bool 
   * @access public
   */
  function setNextUser($user, $activityName = '*')
  {
    if ($activityName != '*')
		$activityID = $this->getOne('SELECT wf_activity_id FROM ' . GALAXIA_TABLE_PREFIX . 'activities WHERE (wf_name = ?) AND (wf_p_id = ?)', array($activityName, $this->activity->getProcessId()));
    else
		$activityID = '*';
    return $this->instance->setNextUser($user, $activityID);
  }

  /**
   * Sets the user role that must perform the next 
   * activity of the process. this effectively "assigns" the instance to
   * some user
   * 
   * @param string $roleName the next activity role
   * @param string $activityName The name of the activity that the role will be able to executed. '*' means the next activity.
   * @return bool true in case of success or false otherwise
   * @access public
   */
  function setNextRole($roleName, $activityName = '*')
  {
    $roleID = $this->getOne('SELECT wf_role_id FROM ' . GALAXIA_TABLE_PREFIX . 'roles WHERE (wf_name = ?) AND (wf_p_id = ?)', array($roleName, $this->activity->getProcessId()));
    if (is_null($roleID))
      return false;

    if ($activityName != '*')
		$activityID = $this->getOne('SELECT wf_activity_id FROM ' . GALAXIA_TABLE_PREFIX . 'activities WHERE (wf_name = ?) AND (wf_p_id = ?)', array($activityName, $this->activity->getProcessId()));
    else
		$activityID = '*';

    return $this->instance->setNextRole($roleID, $activityID);
  }

  /**
   * Gets the user that must perform the next activity of the process. 
   * This can be empty if no setNextUser() was done before.
   * It wont return the default user but only the user which was assigned by a setNextUser
   * 
   * @return int 
   * @access public
   */
  function getNextUser() 
  {
    return $this->instance->getNextUser();
  }
 
  /**
   * Sets the name of this instance.
   * 
   * @param string $value new name of the instance
   * @return bool 
   * @access public
   */
  function setName($value) 
  {
    return $this->instance->setName($value);
  }

  /**
   * Gets the name of this instance
   * 
   * @return string 
   * @access public
   */
  function getName() {
    return $this->instance->getName();
  }

  /**
   * Sets the category of this instance
   * 
   * @param string $value
   * @return bool
   * @access public
   */
  function setCategory($value) 
  {
    return $this->instance->setcategory($value);
  }

  /**
   * Gets category of this instance
   * 
   * @return string 
   * @access public
   */
  function getCategory() 
  {
    return $this->instance->getCategory();
  }
  
  /**
   * Sets a property in this instance. This method is used in activities to
   * set instance properties.
   * all property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). If you have several set to call look
   * at the setProperties function. Each call to this function has an impact on database
   * 
   * @param string $name property name (it will be normalized)
   * @param mixed $value value for this property
   * @return bool 
   * @access public
   */
  function set($name,$value) 
  {
    return $this->instance->set($name,$value);
  }
  
  /**
   * Unsets a property in this instance. This method is used in activities to
   * unset instance properties.
   * All property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). Each call to this function has an impact on database
   * 
   * @param string $name property name (it will be normalized)
   * @return bool true if it was ok
   * @access public
   */
  function clear($name) 
  {
    return $this->instance->clear($name);
  }

  /**
   * Checks if a property in this instance exists. This method is used in activities to
   * check the existance of instance properties.
   * All property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces)
   * 
   * @param string $name property name (it will be normalized)
   * @return bool true if it exists and false otherwise
   * @access public
   */
  function exists($name)
  {
  	return $this->instance->exists($name);
  }

  /**
   * Sets several properties in this instance. This method is used in activities to
   * set instance properties. Use this method if you have several properties to set
   * as it will avoid
   * all property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). If you have several set to call look
   * at the setProperties function. Each call to this function has an impact on database
   * 
   * @param array $properties_array associative array containing for each record the
   * property name as the key and the property value as the value. You do not need the complete
   * porperty array, you can give only the knew or updated properties.
   * @return bool true if it was ok
   * @access public
   */
  function setProperties($properties_array)
  {
     return $this->instance->setProperties($properties_array);
  }

  /**
   * Gets the value of an instance property
   * 
   * @param string $name name of the instance
   * @param string $defaultValue
   * @return bool false if the property was not found, but an error message is stored in the instance object
   * @access public
   */
  function get($name, $defaultValue = "__UNDEF__")
  {
    return $this->instance->get($name, $defaultValue);
  }

  /**
   * Describes the activities where the instance is present, can be more than one activity if the instance was "splitted"
   * 
   * @access public
   * @return array Vector of assocs
   */
  function getActivities() 
  {
    return $this->instance->getActivities();
  }
  
  /**
   * Gets the instance status can be 'completed', 'active', 'aborted' or 'exception'
   * 
   * @access public
   * @return string Instance status
   */
  function getStatus() 
  {
    return $this->instance->getStatus();
  }
  
  /**
   * Sets the instance status
   * 
   * @param $status Desired status, it can be: 'completed', 'active', 'aborted' or 'exception'
   * @return bool
   * @access public
   */
  function setStatus($status) 
  {
    return $this->instance->setStatus($status);
  }
  
  /**
   * Gets the instance priority
   * 
   * @access public
   * @return int 
   */
  function getPriority()
  {
    return $this->instance->getPriority();
  } 

  /**
   * Sets the instance priority
   * 
   * @param int $priority
   * @access public
   * @return bool 
   */
  function setPriority($priority)
  {
    return $this->instance->setPriority($priority);
  }
   
  /**
   * Returns the instance id
   * 
   * @return int 
   * @access public
   */
  function getInstanceId() 
  {
    return $this->instance->getInstanceId();
  }
  
  /**
   * Returns the process id for this instance
   * 
   * @return int 
   * @access public 
   */
  function getProcessId() {
    return $this->instance->getProcessId();
  }
  
  /**
   * Returns the owner of the instance
   * 
   * @return string 
   * @access public
   */
  function getOwner() 
  {
    return $this->instance->getOwner();
  }
  
  /**
   * Sets the instance owner
   * 
   * @param string $user 
   * @access public
   * @return bool  
   */
  function setOwner($user) 
  {
    return $this->instance->setOwner($user);
  }
  
  /**
   * Returns the user that must execute or is already executing an activity where the instance is present
   * 
   * @param int $activityId
   * @return bool False if the activity was not found for the instance, else return the user id or '*' if no user is defined yet
   * @access public
   */  
  function getActivityUser($activityId) 
  {
    return $this->instance->getActivityUser($activityId);
  }
  
  /**
   * Sets the status of the instance in some activity
   * 
   * @param int $activityId Activity id
   * @param string $status New status, it can be 'running' or 'completed'
   * @return bool False if no activity was found for the instance
   * @access public
   */  
  function setActivityStatus($activityId,$status) 
  {
    return $this->instance->setActivityStatus($activityId,$status);
  }
  
  
  /**
   * Gets the status of the instance in some activity 
   * 
   * @param int $activityId 
   * @return string 'running' or 'completed'
   * @access public
   */
  function getActivityStatus($activityId) 
  {
    return $this->instance->getActivityStatus($activityId);
  }
  
  /**
   * Resets the start time of the activity indicated to the current time
   * 
   * @param int $activityId
   * @return bool 
   * @access public
   */
  function setActivityStarted($activityId) 
  {
    return $this->instance->setActivityStarted($activityId);
  }
  
  /**
   * Gets the Unix timstamp of the starting time for the given activity
   * 
   * @param int $activityId
   * @return int 
   * @access public
   */
  function getActivityStarted($activityId) 
  {
    return $this->instance->getActivityStarted($activityId);
  }
  
  /**
   * Gets the time where the instance was started
   * 
   * @return int 
   * @access public
   */
  function getStarted() 
  {
    return $this->instance->getStarted();
  }
  
  /**
   * Gets the end time of the instance (when the process was completed)
   * 
   * @return int 
   * @access public 
   */
  function getEnded() 
  {
    return $this->instance->getEnded();
  }
  
  
  /**
   * Completes an activity.
   * YOU MUST NOT CALL complete() for non-interactive activities since
   * the engine does automatically complete automatic activities after
   * executing them
   * 
   * @return bool True or false, if false it means the complete was not done for some internal reason
   * consult get_error() for more informations
   * @access public
   */
  function complete() 
  {
    if (!($this->activity->isInteractive()))
    {
      $this->error[] = tra('interactive activities should not call the complete() method');
      return false;
    }
    
    return $this->instance->complete($this->activity_id);
  }

  /**
   * Aborts an activity and terminates the whole instance. We still create a workitem to keep track
   * of where in the process the instance was aborted
   * 
   * @return bool 
   * @access public
   */
  function abort() 
  {
    return $this->instance->abort();
  }
  
  /**
   * Gets a comment for this instance
   * 
   * @param int $cId Comment id
   * @return string 
   * @access public
   */
  function get_instance_comment($cId) 
  {
    return $this->instance->get_instance_comment($cId);
  }
  
  /**
   * Inserts or updates an instance comment
   * 
   * @param int $cId Commend id
   * @param int $activityId 
   * @param object $activity 
   * @param int	$user User id
   * @param string $title Comment's title
   * @param string $comment Comment's contents
   * @return bool 
   * @access public
   */
  function replace_instance_comment($cId, $activityId, $activity, $user, $title, $comment) 
  {
    return $this->instance->replace_instance_comment($cId, $activityId, $activity, $user, $title, $comment);
  }
  
  /**
   * Removes an instance comment
   * 
   * @param int $cId Comment id
   * @return bool 
   * @access public
   */
  function remove_instance_comment($cId) 
  {
    return $this->instance->remove_instance_comment($cId);
  }
 
  /**
   * Lists instance comments
   * 
   * @return array 
   * @access public
   */
  function get_instance_comments() 
  {
    return $this->instance->get_instance_comments();
  }

  /**
   * Creates a child instance from the current one
   *
   * @param string $activityName The name of the activity of the new instance
   * @param mixed $properties Determines the new instance properties. If "true" the properties of the current instance will be inherited by the new instance. If "false" no properties will be set. If an "array" (format: property_name => property_value) every property defined in that array will be available to the new instance. False by default
   * @param mixed $user The ID of the user who will own the new instance or '*' (i.e., everyone). '*' by default
   * @param bool $parentLock Flag that determines if the parent instance MUST wait for it's children completion before it's own completion ("true") or not ("false"). "true" by default.
   * @return int The instance ID of the just created instance
   * @access public
   */
  function createChildInstance($activityName, $properties = false, $user = '*', $parentLock = true)
  {
    if ((!$this->activity->isInteractive()) && $parentLock)
      $this->error[] = "createChildInstance: atividades não interativas não podem executar este método com travamento da instância pai";

    $activityID = $this->getOne('SELECT wf_activity_id FROM ' . GALAXIA_TABLE_PREFIX . 'activities WHERE (wf_name = ?) AND (wf_p_id = ?)', array($activityName, $this->activity->getProcessId()));

    if ($properties === true)
      $properties = $this->instance->properties;
    if (!is_array($properties))
      $properties = array();

    $iid = $_REQUEST['iid'];
    $workflow = $GLOBALS['workflow'];
    unset($_REQUEST['iid']);
    $run_activity = Factory::newInstance('run_activity');
    $run_activity->runtime->instance->parentInstanceId = $this->instance_id;
    $output = $run_activity->goChildInstance($activityID, $properties, $user, $parentLock);
    $_REQUEST['iid'] = $iid;
    $GLOBALS['workflow'] = $workflow;

    return $output;
  }

  /**
   * Set a message which is displayed in the activity completion page.
   *
   * @param string $message The message itself.
   * @return void
   * @access public
   */
  function setActivityCompleteMessage($message)
  {
    $this->activityCompleteMessage = $message;
  }

  /**
   * Get the message which is displayed in the activity completion page.
   *
   * @return string The message which will be shown.
   * @access public
   */
  function getActivityCompleteMessage()
  {
    return $this->activityCompleteMessage;
  }

  /**
   * Get information about the parent of an instance (if it has one)
   *
   * @return mixed An array containing the information of the parent or false if the instances does not have a parent
   * @access public
   */
  function getParent()
  {
    $resultSet = $this->query("SELECT wf_parent_instance_id, wf_parent_lock FROM egw_wf_interinstance_relations WHERE (wf_child_instance_id = ?)", array($this->getInstanceId()));
    if (($row = $resultSet->fetchRow()))
      return array('instance_id' => $row['wf_parent_instance_id'], 'lock' => ($row['wf_parent_lock'] == 1));
    else
      return false;
  }

  /**
   * Unlock the parent of the instance
   *
   * @return void
   * @access public
   */
  function unlockParent()
  {
    $this->query("UPDATE egw_wf_interinstance_relations SET wf_parent_lock = 0 WHERE (wf_child_instance_id = ?)", array($this->getInstanceId()));
  }
}


?>
