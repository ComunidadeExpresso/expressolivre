<?php
require_once (GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ActivityManager.php');

/**
 * This class represents a process instance, it is used when any activity is
 * executed. The $instance object is created representing the instance of a
 * process being executed in the activity or even a to-be-created instance
 * if the activity is a start activity
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Instance extends Base {
  /**
   * @var array $changed Changed instance object's members
   * @access protected 
   */
  var $changed = Array('properties' => Array(), 'nextActivity' => Array(), 'nextUser' => Array());
  /**
   * @var array $properties Instance properties
   * @access protected
   */
  var $properties = Array();
  /**
   * @var array $cleared Used to detect conflicts on sync with the database
   * @access protected
   */  
  var $cleared = Array();
  /**
   * @var string  $owner Instance owner
   * @access protected
   */  
  var $owner = '';
  /**
   * @var string $status Instance status
   * @access protected
   */  
  var $status = '';
  /**
   * @var bool $started
   * @access protected
   */  
  var $started;
  /**
   * @var array $nextActivity
   * @access protected
   */  
  var $nextActivity=Array();
  /**
   * @var string $nextUser
   * @access protected
   */  
  var $nextUser;
  /**
   * @var bool $ended
   * @access protected
   */  
  var $ended;
  /**
   * @var string $name Instance name
   * @access protected
   */
  var $name='';
  /**
   * @var string $category Instance category
   * @access protected
   */
  var $category;
  /**
   * @var int $prioryty Instance priority
   * @access protected
   */
  var $priority = 1;

  /**
   * @var bool $isChildInstance Indicates wether the current instance is a child instance or not
   * @access public
   */
  var $isChildInstance = false;
  /**
   * @var bool $parentLock Indicates wether the parent instance depends on the child instance or not
   * @access public
   */
  var $parentLock = false;
  /**
   * @var int $parentInstanceId The instance ID of the parent instance
   * @access public
   */
  var $parentInstanceId;

  /**
   * @var array $activities Array of assocs(activityId, status, started, ended, user, name, interactivity, autorouting)
   * @access protected
   */
  var $activities = Array();
  /**
   * @var int $pIdProcess id
   * @access protected
   */
  var $pId;
  /**
   * @var int $instanceId Instance id
   * @access protected
   */
  var $instanceId = 0;
  /**
   * @var array $workitems An array of workitem ids, date, duration, activity name, user, activity type and interactivity
   * @access protected
   */
  var $workitems = Array(); 
  /**
   * @var object $security Performs some tests and locks
   * @access protected
   */
  var $security;
  /**
   * @var bool $__activity_completed Internal reminder
   * @access protected
   */
  var $__activity_completed=false;
  /**
   * @var bool $unsynch indicator, if true we are not synchronised in the memory object with the database 
   * @see sync()
   * @access protected
   */
  var $unsynch=false;
  
  /**
   * Constructor
   * 
   * @param object $db ADOdb object
   * @access public
   */

  var $activityID = null;
  function Instance() 
  {
    $this->child_name = 'Instance';
    parent::Base();
  }

  /**
   * Method used to load an instance data from the database.
   * This function will load/initialize members of the instance object from the database
   * it will populatae all members and will by default populate the related activities array
   * and the workitems (history) array.
   * 
   * @param int $instanceId
   * @param bool $load_activities true by default, do we need to reload activities from the database?
   * @param bool $load_workitems true by default, do we need to reload workitems from the database?  
   * @return bool 
   * @access protected
   */
  function getInstance($instanceId, $load_activities=true, $load_workitems=true) 
  {
    if (!($instanceId)) return true; //start activities for example - pseudo instances
    // Get the instance data
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instances` where `wf_instance_id`=?";
    $result = $this->query($query,array((int)$instanceId));
    if( empty($result) || (!$result->numRows())) return false;
    $res = $result->fetchRow();

    //Populate 
    $this->properties = unserialize(base64_decode($res['wf_properties']));
    $this->status = $res['wf_status'];
    $this->pId = $res['wf_p_id'];
    $this->instanceId = $res['wf_instance_id'];
    $this->priority = $res['wf_priority'];
    $this->owner = $res['wf_owner'];
    $this->started = $res['wf_started'];
    $this->ended = $res['wf_ended'];
    $this->nextActivity = unserialize(base64_decode($res['wf_next_activity']));
    $this->nextUser = unserialize(base64_decode($res['wf_next_user']));
    $this->name = $res['wf_name'];
    $this->category = $res['wf_category'];

    // Get the activities where the instance is (nothing for start activities)
    if ($load_activities)
    {
      $this->_populate_activities($instanceId);

    }
    
    // Get the workitems where the instance is
    if ($load_workitems)
    {
      $query = "select wf_item_id, wf_order_id, gw.wf_instance_id, gw.wf_activity_id, wf_started, wf_ended, gw.wf_user,
              ga.wf_name, ga.wf_type, ga.wf_is_interactive
              from ".GALAXIA_TABLE_PREFIX."workitems gw
              INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON ga.wf_activity_id = gw.wf_activity_id
              where wf_instance_id=? order by wf_order_id ASC";
      $result = $this->query($query,array((int)$instanceId));
      if (!(empty($result)))
      {
        while($res = $result->fetchRow()) 
        {
          $this->workitems[]=$res;
        }
      }
      return true;
    }
    
  }
  
  /**
   * Loads all activities related to the insance given in parameter in the activities array
   * 
   * @access private
   * @param int $instanceId
   */
  function _populate_activities($instanceId)
  {
    $this->activities=Array();
    $query = "select gia.wf_activity_id, gia.wf_instance_id, wf_started, wf_ended, wf_started, wf_user, wf_status,
            ga.wf_is_autorouted, ga.wf_is_interactive, ga.wf_name, ga.wf_type
            from ".GALAXIA_TABLE_PREFIX."instance_activities gia
            INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON ga.wf_activity_id = gia.wf_activity_id
            where wf_instance_id=?";
    $result = $this->query($query,array((int)$instanceId));
    if (!(empty($result)))
    {
      while($res = $result->fetchRow())
      {
        $this->activities[] = $res;
      }
    }
  }

  /**
   * Performs synchronization on an instance member
   * 
   * @param bool $changed
   * @param array $init 
   * @param array $actual 
   * @param string $name 
   * @param string $fieldname 
   * @param array $namearray
   * @param array $vararray 
   * @return void 
   * @access private
   */
  function _synchronize_member(&$changed,&$init,&$actual,$name,$fieldname,&$namearray,&$vararray)
  {
    //if we work with arrays then it's more complex
    //echo "<br>$name is_array?".(is_array($changed)); _debug_array($changed);
    if (!(is_array($changed)))
    {
      if (isset($changed))
      {
        //detect unsynchro
        if (!($actual==$init))
        {
          $this->error[] = tra('Instance: unable to modify %1, someone has changed it before us', $name);
        }
        else
        {
          $namearray[] = $fieldname;
          $vararray[] = $changed;
          $actual = $changed;
        }
        unset ($changed);
      }
    }
    else //we are working with arrays (properties for example)
    {
      $modif_done = false;
      foreach ($changed as $key => $value)
      {
        //detect unsynchro
        if (!($actual[$key]==$init[$key]))
        {
          $this->error[] = tra('Instance: unable to modify %1 [%2], someone has changed it before us', $name, $key);
        }
        else
        {
          $actual[$key] = $value;
          $modif_done = true;
        }
      }
      if ($modif_done) //at least one modif
      {
        $namearray[] = $fieldname;
        //no more serialize, done by the core security_cleanup
        $vararray[] = $actual; //serialize($actual);
      }   
      $changed=Array();
    }
  }
  
  /**
   * Synchronize thes instance object with the database. All change smade will be recorded except
   * conflicting ones (changes made on members or properties that has been changed by another source
   * --could be another 'instance' of this instance or an admin form-- since the last call of sync() )
   * the unsynch private member is used to test if more heavy tests should be done or not
   * pseudo instances (start, standalone) are not synchronised since there is no record on database
   * 
   * @access public
   * @return bool  
   */
  function sync()
  {
    if ( (!($this->instanceId)) || (!($this->unsynch)) )
    {
      //echo "<br>nothing to do ".$this->unsynch;
      return true;
    }
    //echo "<br> synch!";_debug_array($this->changed);
    //do it in a transaction, can have several activities running
    $this->db->StartTrans();
    //we need to make a row lock now,
    $where = 'wf_instance_id='.(int)$this->instanceId;
    if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instances', $where)))
    {
      $this->error[] = 'sync: '.tra('failed to obtain lock on %1 table', 'instances');
      $this->db->FailTrans();
    }
    else
    {
      //wf_p_id and wf_instance_id are set in creation only.
      //we remember initial values
      $init_properties = $this->properties;
      $init_status = $this->status;
      $init_priority = $this->priority;
      $init_owner = $this->owner;
      $init_started = $this->started;
      $init_ended = $this->ended;
      $init_nextUser = $this->nextUser;
      $init_nextActivity = $this->nextActivity;
      $init_name = $this->name;
      $init_category = $this->category;
      // we re-read instance members to detect conflicts, changes made while we were unsynchronised
      $this->getInstance($this->instance_id, false, false);
      // Now for each modified field we'll change the database vale if nobody has changed
      // the database value before us
      // (Drovetto) Inclusion of the nextUser field in the synchronization
      $bindvars = Array();
      $querysets = Array();
      $queryset = '';
      $this->_synchronize_member($this->changed['status'],$init_status,$this->status,tra('status'),'wf_status',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['priority'],$init_priority,$this->priority,tra('priority'),'wf_priority',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['owner'],$init_owner,$this->owner,tra('owner'),'wf_owner',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['started'],$init_started,$this->started,tra('started'),'wf_started',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['ended'],$init_ended,$this->ended,tra('ended'),'wf_ended',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['name'],$init_name,$this->name,tra('name'),'wf_name',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['category'],$init_category,$this->category,tra('category'),'wf_category',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['properties'],$init_properties,$this->properties,tra('property'),'wf_properties',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['nextUser'],$init_nextUser,$this->nextUser,tra('next user'),'wf_next_user',$querysets,$bindvars);
      $this->_synchronize_member($this->changed['nextActivity'],$init_nextActivity,$this->nextActivity,tra('next activity'),'wf_next_activity',$querysets,$bindvars);
      /* remove unsetted properties */
      if (($propertiesIndex = array_search("wf_properties", $querysets)) !== false)
        foreach ($this->cleared as $clearedName)
          unset($bindvars[$propertiesIndex][$clearedName]);
      /* remove unsetted nextUser */
      if (($nextUserIndex = array_search("wf_next_user", $querysets)) !== false)
        foreach ($bindvars[$nextUserIndex] as $nextUserKey => $nextUserValue)
          if ($bindvars[$nextUserIndex][$nextUserKey] == '__UNDEF__')
          {
            unset($bindvars[$nextUserIndex][$nextUserKey]);
            unset($this->nextUser[$nextUserKey]);
          }
      if (!(empty($querysets)))
      {
        $queryset = implode(' = ?,', $querysets). ' = ?';
        $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set '.$queryset
              .' where wf_instance_id=?';
        $bindvars[] = $this->instanceId;
        //echo "<br> query $query"; _debug_array($bindvars);
        $this->query($query,$bindvars);
      }
    }
    if (!($this->db->CompleteTrans()))
    {
      $this->error[] = tra('failed to synchronize instance data with the database');
      return false;
    }

    //we are not unsynchronized anymore.
    $this->unsynch = false;
    return true;
  }
  
  /**
   * Sets the next activity to be executed, if the current activity is
   * a switch activity the complete() method will use the activity setted
   * in this method as the next activity for the instance. 
   * The object records an array of transitions, as the instance can be splitted in several 
   * running activities, transition from the current activity to the given activity will
   * be recorded and all previous recorded transitions starting from the current activity
   * will be deleted
   * 
   * @param int $activityId Running activity Id 
   * @param string $actname Activity name as argument (not an id)
   * @return bool
   * @access public  
   */
  function setNextActivity($activityId, $actname) 
  {
    $pId = $this->pId;
    $actname=trim($actname);
    $aid = $this->getOne('select wf_activity_id from '.GALAXIA_TABLE_PREFIX.'activities where wf_p_id=? and wf_name=?',array($pId,$actname));
    if (!($aid))
    {
      $this->error[] = tra('setting next activity to an unexisting activity');
      return false;
    }
    $this->changed['nextActivity'][$activityId]=$aid;
    $this->unsynch = true;
    return true;
  }

  /**
   * This method can be used to set the user that must perform the next 
   * activity of the process. this effectively "assigns" the instance to
   * some user
   * 
   * @param mixed $user The user which will execute the next activity
   * @param mixed $activityID The ID of the activity that the user will be able to execute. '*' means any next activity
   * @return bool true in case of success or false otherwise
   * @access public
   */
  function setNextUser($user, $activityID = '*')
  {
    if ($activityID == '*')
    {
      $candidates = $this->getActivityCandidates($this->activityID);
      foreach ($candidates as $candidate)
        $this->changed['nextUser'][$candidate] = $user;
    }
    else
      $this->changed['nextUser'][$activityID] = $user;
    $this->unsynch = true;
    return true;
  }

  /**
   * Sets next instance user role
   *
   * @param string $roleName The name of the role
   * @param mixed $activityID The ID of the activity that the role will be able to execute. '*' means any next activity
   * @return bool true in case of success or false otherwise
   * @access public
   */
  function setNextRole($roleID, $activityID = '*')
  {
    return $this->setNextUser('p' . $roleID, $activityID);
  }

  /**
   * Removes the user/role of the provided activity ID
   *
   * @param mixed $activityID The ID of the activity from which the users/roles will be removed.
   * @return void
   * @access public
   */
  function unsetNextUser($activityID = '*')
  {
    $this->setNextUser('__UNDEF__', $activityID);
  }

  /**
   * This method can be used to get the user that must perform the next 
   * activity of the process. This can be empty if no setNextUser was done before.
   * It wont return the default user but inly the user which was assigned by a setNextUser
   * 
   * @param mixed $activityID The ID of the activity from which we want the user/role that will execute it.
   * @return string
   * @access public
   */
  function getNextUser($activityID = '*')
  {
    if ($activityID == '*')
      $order = array('*' . $this->activityID);
    else
      $order = array($activityID, '*' . $this->activityID);
    foreach ($order as $currentOrder)
    {
      if (isset($this->changed['nextUser'][$currentOrder]) && ($this->changed['nextUser'][$currentOrder] == '__UNDEF__'))
        continue;
      if (isset($this->changed['nextUser'][$currentOrder]))
        return $this->changed['nextUser'][$currentOrder];
      if (isset($this->nextUser[$currentOrder]))
        return $this->nextUser[$currentOrder];
    }
    return '';
  }

  /**
   * Creates a new instance.
   * This method is called in start activities when the activity is completed
   * to create a new instance representing the started process.
   *
   * @param int $activityId start activity id
   * @param int $user current user id
   * @return bool
   * @access private
   */
  function _createNewInstance($activityId,$user) {
    // Creates a new instance setting up started, ended, user, status and owner
    $pid = $this->getOne('SELECT wf_p_id FROM '.GALAXIA_TABLE_PREFIX.'activities WHERE wf_activity_id=?',array((int)$activityId));
    $this->pId = $pid;
    $this->setStatus('active');
    //$this->setNextUser('');
    $now = date("U");
    $this->setStarted($now);
    $this->setOwner($user);

    //Get the id of new instance, before insert values in table and use this value from main table and relationship tables.
    $this->instanceId = $this->getOne("SELECT nextval('seq_egw_wf_instances')");
    $iid=$this->instanceId;

    $query = 'INSERT INTO '.GALAXIA_TABLE_PREFIX.'instances
                (wf_instance_id, wf_started,wf_ended,wf_status,wf_p_id,wf_owner,wf_properties)
              VALUES
                (?,?,?,?,?,?,?)';

    $this->query($query,array((int)$iid, $now,0,'active',$pid,$user,$this->security_cleanup(Array(),false)));

    // Then add in ".GALAXIA_TABLE_PREFIX."instance_activities an entry for the
    // activity the user and status running and started now
    $query = 'INSERT INTO '.GALAXIA_TABLE_PREFIX.'instance_activities
                (wf_instance_id,wf_activity_id,wf_user,wf_started,wf_status)
              VALUES
                (?,?,?,?,?)';
    $this->query($query,array((int)$iid,(int)$activityId,$user,(int)$now,'running'));

    if (($this->isChildInstance) && (!is_null($this->parentInstanceId)))
    {
      $query = 'INSERT INTO '.GALAXIA_TABLE_PREFIX.'interinstance_relations
                  (wf_parent_instance_id, wf_child_instance_id, wf_parent_lock)
                VALUES
                  (?,?,?)';
      $this->query($query,array((int) $this->parentInstanceId, (int) $iid, (int) (($this->parentLock) ? 1 : 0)));
    }

    //update database with other datas stored in the object
    return $this->sync();
  }

  /**
   * Sets the name of this instance
   *
   * @param string $value
   * @return bool
   * @access public
   */
  function setName($value) 
  {
    $this->changed['name'] = substr($value,0,120);
    $this->unsynch = true;
    return true;
  }

  /**
   * Get the name of this instance
   *
   * @return string
   * @access public
   */
  function getName() 
  {
    if (!(isset($this->changed['name'])))
    {
      return $this->name;
    }
    else
    {
      return $this->changed['name'];
    }
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
    $this->changed['category'] = $value;
    $this->unsynch = true;
    return true;
  }

  /**
   * Get the category of this instance
   *
   * @return string
   * @access public
   */
  function getCategory() 
  {
    if (!(isset($this->changed['category'])))
    {
      return $this->category;
    }
    else
    {
      return $this->changed['category'];
    }
  }

  /**
   * Normalizes a property name
   *
   * @param string $name name you want to normalize
   * @return string property name
   * @access private
   */
  function _normalize_name($name)
  {
    $name = trim($name);
    $name = str_replace(" ","_",$name);
    $name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
    return $name;
  }

  /**
   * Sets a property in this instance. This method is used in activities to
   * set instance properties.
   * all property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). If you have several set to call look
   * at the setProperties function. Each call to this function has an impact on database
   *
   * @param string $name property name (it will be normalized)
   * @param mixed $value value you want for this property
   * @return bool
   * @access public
   */
  function set($name,$value) 
  {
    $name = $this->_normalize_name($name);
    unset($this->cleared[$name]);
    $this->changed['properties'][$name] = $this->security_cleanup($value);
    if (is_array($value))
      $this->changed['properties'][$name] = "__ARRAY__" . $this->changed['properties'][$name];
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Unsets a property in this instance. This method is used in activities to
   * unset instance properties.
   * All property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). Each call to this function has an impact on database
   *
   * @param string $name the property name (it will be normalized)
   * @return bool
   * @access public
   */
  function clear($name)
  {
    $this->set($name, '');
    $this->cleared[$name] = $name;
    $this->unsynch = true;
    return true;
  }

  /**
   * Checks if a property in this instance exists. This method is used in activities to
   * check the existance of instance properties.
   * All property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces).
   *
   * @param string $name property name (it will be normalized)
   * @return bool
   * @access public
   */
  function exists($name)
  {
    $name = $this->_normalize_name($name);

    return ((isset($this->changed['properties'][$name]) || isset($this->properties[$name])) && !isset($this->cleared[$name]));
  }

  /**
   * Sets several properties in this instance. This method is used in activities to
   * set instance properties. Use this method if you have several properties to set
   * as it will avoid to re-call the SQL engine for each property.
   * all property names are normalized for security reasons and to avoid localisation
   * problems (A->z, digits and _ for spaces). 
   *
   * @param array $properties_array associative array containing for each record the
   * property name as the key and the property value as the value
   * @return bool
   * @access public
   */
  function setProperties($properties_array) 
  {
    $backup_values = $this->properties;
    foreach ($properties_array as $key => $value)
    {
      $name = $this->_normalize_name($key);
      $this->changed['properties'][$name] = $this->security_cleanup($value);
    }
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Gets the value of an instance property
   *
   * @param string $name name of the property
   * @param mixed $defaultValue
   * @return bool
   * @access public
   */
  function get($name, $defaultValue = "__UNDEF__")
  {
    $name = $this->_normalize_name($name);
    $output = "";

    /* select the value of the current property */
    if (isset($this->changed['properties'][$name]))
    {
      $output = $this->changed['properties'][$name];
    }
    else
    {
      if (isset($this->properties[$name]))
      {
        $output = $this->properties[$name];
      }
    }

    /* if the property doesn't exist return the default value or throw an error */
    if (isset($this->cleared[$name]) || ((!isset($this->properties[$name])) && (!isset($this->changed['properties'][$name]))))
    {
      if ($defaultValue != "__UNDEF__")
      {
        $output = $defaultValue;
      }
      else
      {
        $this->error[] = tra('property %1 not found', $name);
        $output = false;
      }
    }

    /* if the requested value is an enconded/serialized array, change it back to its original type */
    if (@strcmp(@substr($output, 0, 9), "__ARRAY__") == 0)
      if ( ($tmp = base64_decode(substr($output, 9))) !== false )
        if ( ($tmp = unserialize($tmp)) !== false )
          $output = $tmp;
    return $output;
  }
  
  /**
   * Returns an array of assocs describing the activities where the instance
   * is present, can be more than one activity if the instance was "splitted"
   *
   * @return array
   * @access public
   */
  function getActivities() {
    return $this->activities;
  }
  
  /**
   * Gets the instance status can be
   * 'completed', 'active', 'aborted' or 'exception'
   *
   * @return string
   * @access public
   */
  function getStatus() {
    if (!(isset($this->changed['status'])))
    {
      return $this->status;
    }
    else
    {
      return $this->changed['status'];
    }
  }
  
  /**
   * Sets the instance status
   *
   * @param string_type $status it can be: 'completed', 'active', 'aborted' or 'exception'
   * @return bool
   * @access public
   */
  function setStatus($status) 
  {
    if (!(($status=='completed') || ($status=='active') || ($status=='aborted') || ($status=='exception')))
    {
      $this->error[] = tra('unknown status');
      return false;
    }
    $this->changed['status'] = $status; 
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Gets the instance priority
   *
   * @return int
   * @access public
   */
  function getPriority()
  {
    if (!(isset($this->changed['priority'])))
    {
      return $this->priority;
    }
    else
    {
      return $this->changed['priority'];
    }
  } 

  /**
   * Sets the instance priority
   *
   * @param int $priority
   * @return bool
   * @access public
   */
  function setPriority($priority)
  {
    $mypriority = (int)$priority;
    $this->changed['priority'] = $mypriority;
    $this->unsynch = true;
    return true;
  }
   
  /**
   * Returns the instanceId
   *
   * @return int
   * @access public
   */
  function getInstanceId() 
  {
    return $this->instanceId;
  }
  
  /**
   * Returns the processId for this instance
   *
   * @return int
   * @access public
   */
  function getProcessId() 
  {
    return $this->pId;
  }
  
  /**
   * Returns the user that created the instance
   *
   * @return int
   * @access public
   */
  function getOwner() 
  {
    if (!(isset($this->changed['owner'])))
    {
      return $this->owner;
    }
    else
    {
      return $this->changed['owner'];
    }
  }
  
  /**
   * Sets the instance creator user
   *
   * @param int $user new owner id, musn't be false, 0 or empty
   * @return bool
   * @access public
   */
  function setOwner($user) 
  {
    if (empty($user))
    { 
      return false;
    }
    $this->changed['owner'] = $user;
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Sets the user that must execute the activity indicated by the activityId.
   * Note that the instance MUST be present in the activity to set the user,
   * you can't program who will execute an activity.
   * If the user is empty then the activity user is setted to *, allowing any
   * authorised user to take the token later concurrent access to this function 
   * is normally handled by WfRuntime and WfSecurity theses objects are the only ones 
   * which should call this function. WfRuntime is handling the current transaction and
   * WfSecurity is Locking the instance and instance_activities table on a 'run' action 
   * which is the action leading to this setActivityUser call (could be a release 
   * as well on auto-release)
   *
   * @param int $activityId
   * @param int $theuser user id or '*' (or 0, '' or null which will be set to '*')
   * @return bool
   * @access public
   */
  function setActivityUser($activityId,$theuser) {
    if(empty($theuser)) $theuser='*';
    $found = false;
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        // here we are in the good activity
        $found = true;

        // prepare queries
        $where = ' where wf_activity_id=? and wf_instance_id=?';
        $bindvars = array((int)$activityId,(int)$this->instanceId);
        if(!($theuser=='*')) 
        {
          $where .= ' and (wf_user=? or wf_user=? or wf_user LIKE ?)';
          $bindvars[]= $theuser;
          $bindvars[]= '*';
          $bindvars[]= 'p%';
        }
        
        // update the user
        $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=?';
        $query .= $where;
        $bindvars_update = array_merge(array($theuser),$bindvars);
        $this->query($query,$bindvars_update);
        $this->activities[$i]['wf_user']=$theuser;
        return true;
      }
    }
    // if we didn't find the activity it will be false
    return $found;
  }

  /**
   * Returns the user that must execute or is already executing an activity
   * wherethis instance is present
   *
   * @param int $activityId
   * @return bool
   * @access public
   */
  function getActivityUser($activityId) {
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_user'];
      }
    }  
    return false;
  }

  /**
   * Sets the status of the instance in some activity
   *
   * @param int $activityId
   * @param string $status new status, it can be 'running' or 'completed'
   * @return bool
   * @access public
   */
  function setActivityStatus($activityId,$status) 
  {
    if (!(($status=='running') || ($status=='completed')))
    {
      $this->error[] = tra('unknown status');
      return false;
    }
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i)
    {
      if($this->activities[$i]['wf_activity_id']==$activityId) 
      {
        $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_status=? where wf_activity_id=? and wf_instance_id=?';
        $this->query($query,array($status,(int)$activityId,(int)$this->instanceId));
        return true;
      }
    }
    $this->error[] = tra('new status not set, no corresponding activity was found.'); 
    return false;
  }
  
  
  /**
   * Gets the status of the instance in some activity, can be
   * 'running' or 'completed'
   *
   * @param int $activityId
   * @return mixed
   * @access public
   */
  function getActivityStatus($activityId) {
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_status'];
      }
    }
    $this->error[] = tra('activity status not avaible, no corresponding activity was found.');
    return false;
  }
  
  /**
   * Resets the start time of the activity indicated to the current time
   *
   * @param int $activityId
   * @return bool
   * @access public
   */
  function setActivityStarted($activityId) {
    $now = date("U");
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        $this->activities[$i]['wf_started']=$now;
        $query = "update `".GALAXIA_TABLE_PREFIX."instance_activities` set `wf_started`=? where `wf_activity_id`=? and `wf_instance_id`=?";
        $this->query($query,array($now,(int)$activityId,(int)$this->instanceId));
        return true;
      }
    }
    $this->error[] = tra('activity start not set, no corresponding activity was found.');
    return false;
  }
  
  /**
   * Gets the Unix timstamp of the starting time for the given activity
   *
   * @param int $activityId
   * @return mixed
   * @access public
   */
  function getActivityStarted($activityId) {
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;++$i) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_started'];
      }
    }
    $this->error[] = tra('activity start not avaible, no corresponding activity was found.');
    return false;
  }
  
  /**
   * Gets an activity from the list of activities of the instance
   * the result is an array describing the instance
   *
   * @param int $activityId
   * @return mixed
   * @access private
   */
  function _get_instance_activity($activityId) 
  {
    $activities_count = count($this->activities);
    for($i=0;$i<$activities_count;$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i];
      }
    }
    $this->error[] = tra('no corresponding activity was found.');
    return false;
  }

  /**
   * Sets the time where the instance was started
   *
   * @param int $time
   * @return bool
   * @access public
   */
  function setStarted($time) 
  {
    $this->changed['started'] = $time;
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Gets the time where the instance was started (Unix timestamp)
   *
   * @return int
   * @access public
   */
  function getStarted() 
  {
    if (!(isset($this->changed['started'])))
    {
      return $this->started;
    }
    else
    {
      return $this->changed['started'];
    }
  }
  
  /**
   * Sets the end time of the instance (when the process was completed)
   *
   * @param int $time
   * @return bool
   * @access public
   */
  function setEnded($time) 
  {
    $this->changed['ended']=$time;
    $this->unsynch = true;
    return true;
  }
  
  /**
   * Gets the end time of the instance (when the process was completed)
   *
   * @return int
   * @access public
   */
  function getEnded() 
  {
    if (!(isset($this->changed['ended'])))
    {
      return $this->ended;
    }
    else
    {
      return $this->changed['ended'];
    }
  }
  
  /**
   * This set to true or false the 'Activity Completed' status which will
   * be important to know if the user code has completed the current activity
   *
   * @param bool $bool true by default, it will be the next status of the 'Activity Completed' indicator
   * @access private
   * @return void
   */
  function setActivityCompleted($bool)
  {
    $this->__activity_completed = $bool;
  }
  
  /**
   * Gets the 'Activity Completed' status
   *
   * @return mixed
   * @access public
   */
  function getActivityCompleted()
  {
    return $this->__activity_completed;
  }

  /**
   * This function can be called by the instance object himself (for automatic activities)
   * or by the WfRuntime object. In interactive activities code users use complete() --without args--
   * which refer to the WfRuntime->complete() function which call this one.
   * In non-interactive activities a call to a complete() will generate errors because the engine
   * does it his own way as I said first.
   * Particularity of this Complete is that it is Transactional, i.e. it it done completely
   * or not and row locks are ensured
   *
   * @param int $activityId activity that is being completed
   * @param bool $addworkitem indicates if a workitem should be added for the completed
   * activity (true by default)
   * @return bool false it means the complete was not done for some internal reason
   * consult $instance->get_error() for more informations
   * @access public
   */
  function complete($activityId,$addworkitem=true)
  {
    //$this->db->
    $result = $this->query("SELECT 1 FROM " . GALAXIA_TABLE_PREFIX . "instances i, " . GALAXIA_TABLE_PREFIX . "interinstance_relations ir WHERE (ir.wf_child_instance_id = i.wf_instance_id) AND (i.wf_status IN ('active', 'exception')) AND (ir.wf_parent_lock = 1) AND (ir.wf_parent_instance_id = ?)", array($this->instanceId));
    if ($result->numRows() > 0)
      die("Esta instância está aguardando que outras instâncias, das quais depende, sejam finalizadas.");
    //ensure it's false at first
    $this->setActivityCompleted(false);

    //The complete() is in a transaction, it will be completly done or not at all
    $this->db->StartTrans();

    //lock rows and ensure access is granted
    if (!(isset($this->security))) $this->security = &Factory::getInstance('WfSecurity');
    if (!($this->security->checkUserAction($activityId,$this->instanceId,'complete')))
    {
      $this->error[] = tra('you were not allowed to complete the activity');
      $this->db->FailTrans();
    }
    else
    {
      if (!($this->_internalComplete($activityId,$addworkitem)))
      {
        $this->error[] = tra('The activity was not completed');
        $this->db->FailTrans();
      }
    }
    //we mark completion with result of the transaction wich will be false if any error occurs
    //this is the end of the transaction
    $this->setActivityCompleted($this->db->CompleteTrans());

    //we return the completion state.
    return $this->getActivityCompleted();
  }

  /**
   * YOU MUST NOT CALL _internalComplete() directly, use Complete() instead
   *
   * @param int $activityId activity that is being completed
   * @param bool $addworkitem indicates if a workitem should be added for the completed
   * activity (true by default)
   * @return bool false it means the complete was not done for some internal reason
   * consult $instance->get_error() for more informations
   * @access private
   */
  function _internalComplete($activityId,$addworkitem=true) {
    global $user;

    if(empty($user)) 
    {
      $theuser='*';
    } 
    else 
    {
      $theuser=$user;
    }

    if(!($activityId)) 
    {
      $this->error[] = tra('it was impossible to complete, no activity was given.');
      return false;
    }  
    
    $now = date("U");
    
    // If we are completing a start activity then the instance must 
    // be created first!
    $type = $this->getOne('select wf_type from '.GALAXIA_TABLE_PREFIX.'activities where wf_activity_id=?',array((int)$activityId));
    if($type=='start') 
    {
      if (!($this->_createNewInstance((int)$activityId,$theuser)))
      {
        return false;
      }
    }
    else
    {  
      // Now set ended
      $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_ended=? where wf_activity_id=? and wf_instance_id=?';
      $this->query($query,array((int)$now,(int)$activityId,(int)$this->instanceId));
    }
    
    //Set the status for the instance-activity to completed
    //except for start activities
    if (!($type=='start'))
    {
      if (!($this->setActivityStatus($activityId,'completed')))
      {
        return false;
      }
    }
    
    //If this and end actt then terminate the instance
    if($type=='end') 
    {
      if (!($this->terminate($now)))
      {
        return false;
      }
    }

    //now we synchronise instance with the database
    if (!($this->sync())) return false;
    
    //Add a workitem to the instance 
    if ($addworkitem)
    {
      return $this->addworkitem($type,$now, $activityId);
    }
    else
    {
      return true;
    }
  }
  
  /**
   * This function will add a workitem in the workitems table, the instance MUST be synchronised before
   * calling this function.
   *
   * @param string $activity_type activity type, needed because internals are different for start activities
   * @param int $ended ending time
   * @param int $activityId finishing activity id
   * @return mixed
   * @access private
   */
  function addworkitem($activity_type, $ended, $activityId)
  {
    $iid = $this->instanceId;
    $max = $this->getOne('select max(wf_order_id) from '.GALAXIA_TABLE_PREFIX.'workitems where wf_instance_id=?',array((int)$iid));
    if(!$max) 
    {
        $max=1;
    }
    else 
    {
        ++$max;
    }
    if($activity_type=='start')
    {
      //Then this is a start activity ending
      $started = $this->getStarted();
      //at this time owner is the creator
      $putuser = $this->getOwner();
    }
    else
    {
      $act = $this->_get_instance_activity($activityId);
      if(!$act) 
      {
        //this will abort the function
        $this->error[] = tra('failed to create workitem');
        return false;
      }
      else 
      {
        $started = $act['wf_started'];
        $putuser = $act['wf_user'];
      }
    }
    //no more serialize, done by the core security_cleanup
    $properties = $this->security_cleanup($this->properties, false); //serialize($this->properties);
    $query='insert into '.GALAXIA_TABLE_PREFIX.'workitems
        (wf_instance_id,wf_order_id,wf_activity_id,wf_started,wf_ended,wf_properties,wf_user) values(?,?,?,?,?,?,?)';    
    $this->query($query,array((int)$iid,(int)$max,(int)$activityId,(int)$started,(int)$ended,$properties,$putuser));
    return true;
  }
  
  /**
   * Send autorouted activities to the next one(s)
   * YOU MUST NOT CALL sendAutorouted() for non-interactive activities since
   * the engine does automatically complete and send automatic activities after
   * executing them
   * This function is in fact a Private function runned by the engine
   * You should never use it without knowing very well what you're doing
   *
   * @param int $activityId activity that is being completed, when this is not
   * passed the engine takes it from the $_REQUEST array,all activities
   * are executed passing the activityId in the URI
   * @param bool $force indicates that the instance must be routed no matter if the
   * activity is auto-routing or not. This is used when "sending" an
   * instance from a non-auto-routed activity to the next activity
   * @return mixed
   * @access private
   */
  function sendAutorouted($activityId,$force=false)
  {
    $returned_value = Array();
    $type = $this->getOne("select `wf_type` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));    
    //on a end activity we have nothing to do
    if ($type == 'end')
    {
      return true;
    }
    //If the activity ending is not autorouted then we have nothing to do
    if (!(($force) || ($this->getOne("select `wf_is_autorouted` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array($activityId)) == 'y')))
    {
      $returned_value['transition']['status'] = 'not autorouted';
      return $returned_value;
    }
    //If the activity ending is autorouted then send to the activity
    // Now determine where to send the instance
    $candidates = $this->getActivityCandidates($activityId);
    if($type == 'split') 
    {
      $erase_from = false;
      $num_candidates = count($candidates);
      $returned_data = Array();
      $i = 1;
      foreach ($candidates as $cand) 
      {
        // only erase split activity in instance when all the activities comming from the split have been set up
        if ($i == $num_candidates)
        { 
          $erase_from = true;
        }
        $returned_data[$i] = $this->sendTo($activityId,$cand,$erase_from);
        $this->unsetNextUser($cand);
        $this->sync();
        ++$i;
      }
      $this->unsetNextUser('*' . $activityId);
      $this->sync();
      return $returned_data;
    } 
    elseif($type == 'switch') 
    {
      if (in_array($this->nextActivity[$activityId],$candidates))
      {
        $selectedActivity = $this->nextActivity[$activityId];
        foreach ($candidates as $candidate)
          if ($candidate != $selectedActivity)
            $this->unsetNextUser($candidate);
        $output = $this->sendTo((int)$activityId,(int)$selectedActivity);
        $this->unsetNextUser($selectedActivity);
        $this->unsetNextUser('*' . $activityId);
        $this->sync();
        return $output;
      } 
      else 
      {
        $returned_value['transition']['failure'] = tra('Error: nextActivity does not match any candidate in autorouting switch activity');
        return $returned_value;
      }
    } 
    else 
    {
      if (count($candidates)>1) 
      {
        $returned_value['transition']['failure'] = tra('Error: non-deterministic decision for autorouting activity');
        return $returned_value;
      }
      else 
      {
        $output = $this->sendTo((int)$activityId,(int)$candidates[0]);
        $this->unsetNextUser($candidates[0]);
        $this->unsetNextUser('*' . $activityId);
        $this->sync();
        return $output;
      }
    }
  }
  
  /**
   * This is a semi-private function, use GUI's abort function
   * Aborts an activity and terminates the whole instance. We still create a workitem to keep track
   * of where in the process the instance was aborted
   *
   * @param bool $addworkitem
   * @return bool
   * @access public
   * @todo review, reuse of completed code
   */
  function abort($addworkitem=true) 
  {
    // If we are aborting a start activity then the instance must 
    // be created first!
    // ==> No, there's no reason to have an uncompleted start activity to abort

    // load all the activities of the current instance
    if ($addworkitem)
    {
      $activities = array();
      $query = 'SELECT a.wf_type AS wf_type, ia.wf_activity_id AS wf_activity_id FROM '.GALAXIA_TABLE_PREFIX.'activities a, '.GALAXIA_TABLE_PREFIX.'instance_activities ia WHERE (ia.wf_activity_id = a.wf_activity_id) AND (ia.wf_instance_id = ?)';
      $result = $this->query($query,array((int)$this->instanceId));
      while ($row = $result->fetchRow())
        $activities[] = $row;
    }

    // Now set ended on instance_activities
    $now = date("U");

    // terminate the instance with status 'aborted'
    if (!($this->terminate($now,'aborted')))
      return false;

    //now we synchronise instance with the database
    if (!($this->sync()))
      return false;
    
    //Add a workitem to the instance 
    if ($addworkitem)
    {
      foreach ($activities as $activity)
        if (!($this->addworkitem($activity['wf_type'], $now, $activity['wf_activity_id'])))
          return false;
      return true;
    }
    else
    {
      return true;
    }
  }
  
  /**
   * Terminates the instance marking the instance and the process
   * as completed. This is the end of a process
   * Normally you should not call this method since it is automatically
   * called when an end activity is completed
   * object is synched at the end of this function
   *
   * @param int $time terminating time
   * @param string $status
   * @return bool
   * @access private
   */
  function terminate($time, $status = 'completed') {
    //Set the status of the instance to completed
    if (!($this->setEnded((int)$time))) return false;
    if (!($this->setStatus($status))) return false;
    $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=?";
    $this->query($query,array((int)$this->instanceId));
    return $this->sync();
  }
  
  
  /**
   * Sends the instance from some activity to another activity. (walk on a transition)
   * You should not call this method unless you know very very well what
   * you are doing
   *
   * @param int $from activity id at the start of the transition
   * @param int $activityId activity id at the end of the transition
   * @param bool $erase_from true by default, if true the coming activity row will be erased from
   * instance_activities table. You should set it to false for example with split activities while
   * you still want to re-call this function
   * @return mixed false if anything goes wrong, true if we are at the end of the execution tree and an array
   * if a part of the process was automatically runned at the end of the transition. this array contains
   * 2 keys 'transition' is the transition we walked on, 'activity' is the result of the run part if it was an automatic activity.
   * 'activity' value is an associated array containing several usefull keys:
   * 'completed' is a boolean indicating that the activity was completed or not
   * 'debug contains debug messages
   * 'info' contains some usefull infos about the activity-instance running (like names)
   * 'next' is the result of a SendAutorouted part which could in fact be the result of a call to this function, etc
   * @access public
   */
  function sendTo($from,$activityId,$erase_from=true) 
  {
    //we will use an array for return value
    $returned_data = Array();
    //1: if we are in a join check
    //if this instance is also in
    //other activity if so do
    //nothing
    $query = 'select wf_type, wf_name from '.GALAXIA_TABLE_PREFIX.'activities where wf_activity_id=?';
    $result = $this->query($query,array($activityId));
    if (empty($result))
    {
      $returned_data['transition']['failure'] = tra('Error: trying to send an instance to an activity but it was impossible to get this activity');
      return $returned_data;
    }
    while ($res = $result->fetchRow())
    {
      $type = $res['wf_type'];
      $targetname = $res['wf_name'];
    }
    $returned_data['transition']['target_id'] = $activityId;
    $returned_data['transition']['target_name'] = $targetname;
    
    // Verify the existence of a transition
    if(!$this->getOne("select count(*) from `".GALAXIA_TABLE_PREFIX."transitions` where `wf_act_from_id`=? and `wf_act_to_id`=?",array($from,(int)$activityId))) {
      $returned_data['transition']['failure'] = tra('Error: trying to send an instance to an activity but no transition found');
      return $returned_data;
    }

    //init
    $putuser=0;
    
    //try to determine the user or *
    //Use the nextUser
    $the_next_user = $this->getNextUser($activityId);
    if($the_next_user) 
    {
      //we check rights for this user on the next activity
      if (!(isset($this->security))) $this->security = &Factory::getInstance('WfSecurity');
      if ($this->security->checkUserAccess($the_next_user,$activityId))
      {
        $putuser = $the_next_user;
      }
    }
    if ($putuser===0)
    {
      // then check to see if there is a default user
      $activity_manager = &Factory::newInstance('ActivityManager');
      //get_default_user will give us '*' if there is no default_user or if the default user has no role
      //mapped anymore
      $default_user = $activity_manager->get_default_user($activityId,true);
      unset($activity_manager);
      // if they were no nextUser, no unique user avaible, no default_user then we'll have '*'
      // which will let user having the good role mapping grab this activity later
      $putuser = $default_user;
    }
    if ($the_next_user)
      $this->setNextUser($the_next_user, '*' . $activityId);
    
    //update the instance_activities table
    //if not splitting delete first
    //please update started,status,user
    if (($erase_from) && (!empty($this->instanceId)))
    {
      $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=? and `wf_activity_id`=?";
      $this->query($query,array((int)$this->instanceId,$from));
    }
  
    if ($type == 'join') {
      if (count($this->activities)>1) {
        // This instance will have to wait!
        $returned_data['transition']['status'] = 'waiting';
        return $returned_data;
      }
    }    

    //create the new instance-activity
    $returned_data['transition']['target_id'] = $activityId;
    $returned_data['transition']['target_name'] = $targetname;
    $now = date("U");
    $iid = $this->instanceId;
    $query="delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=? and `wf_activity_id`=?";
    $this->query($query,array((int)$iid,(int)$activityId));
    $query="insert into `".GALAXIA_TABLE_PREFIX."instance_activities`(`wf_instance_id`,`wf_activity_id`,`wf_user`,`wf_status`,`wf_started`) values(?,?,?,?,?)";
    $this->query($query,array((int)$iid,(int)$activityId,$putuser,'running',(int)$now));
    
    //record the transition walk
    $returned_data['transition']['status'] = 'done';

    
    //we are now in a new activity
    $this->_populate_activities($iid);
    //if the activity is not interactive then
    //execute the code for the activity and
    //complete the activity
    $isInteractive = $this->getOne("select `wf_is_interactive` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));
    if ($isInteractive=='n') 
    {
      //first we sync actual instance because the next activity could need it
      if (!($this->sync()))
      {
        $returned_data['activity']['failure'] = true;
        return $returned_data;
      }
      // Now execute the code for the activity
      $this->activityID = $activityId;
      $returned_data['activity'] = $this->executeAutomaticActivity($activityId, $iid);
      $this->activityID = $from;
    }
    else
    {
      // we sync actual instance
      if (!($this->sync()))
      {
        $returned_data['failure'] = true;
        return $returned_data;
      }
    }
    return $returned_data;
  }
  
  /**
   * This is a public method only because the GUI can ask this action for the admin
   * on restart failed automated activities, but in fact it's quite an internal function,
   * This function handle the execution of automatic activities (and the launch of transitions
   * which can be related to this activity)
   *
   * @param int $activityId activity id at the end of the transition
   * @param int $iid instance id
   * @return array
   * @access public
   */
  function executeAutomaticActivity($activityId, $iid)
  {
    $returned_data = Array();
    // Now execute the code for the activity (function defined in galaxia's config.php)
    $returned_data =& galaxia_execute_activity($activityId, $iid , 1);

    //we should have some info in $returned_data now. if it is false there's a problem
    if ((!(is_array($returned_data))) && (!($returned_data)) )
    {
      $this->error[] = tra('failed to execute automatic activity');
      //record the failure
      $returned_data['failure'] = true;
      return $returned_data;
    }
    else
    {
      //ok, we have an array, but it can still be a bad result
      //this one is just for debug info
      if (isset($returned_data['debug']))
      {
        //we retrieve this info here, in this object
        $this->error[] = $returned_data['debug'];
      }
      //and this really test if it worked, if not we have a nice failure message (better than just failure=true)
      if (isset($returned_data['failure']))
      {
        $this->error[] = tra('failed to execute automatic activity');
        $this->error[] = $returned_data['failure'];
        //record the failure
        return $returned_data;
      }
      
    }
    // Reload in case the activity did some change, last sync was done just before calling this function
    //TODO: check if this sync is really needed
    $this->getInstance($this->instanceId, false, false);

    //complete the automatic activity----------------------------
    if ($this->Complete($activityId))
    {
      $returned_data['completed'] = true;
      
      //and send the next autorouted activity if any
      $returned_data['next'] = $this->sendAutorouted($activityId);
    }
    else
    {
      $returned_data['failure'] = $this->get_error();
    }
    return $returned_data;
  }
  
  /**
   * Gets a comment for this instance 
   *
   * @param int $cId
   * @return mixed
   * @access public
   */
  function get_instance_comment($cId) {
    $iid = $this->instanceId;
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? and `wf_c_id`=?";
    $result = $this->query($query,array((int)$iid,(int)$cId));
    $res = $result->fetchRow();
    return $res;
  }
  
  /**
   * Inserts or updates an instance comment 
   *
   * @param int $cId
   * @param int $activityId
   * @param object $activity
   * @param int $user
   * @param string $title
   * @param mixed $comment
   * @return bool
   * @access public
   */
  function replace_instance_comment($cId, $activityId, $activity, $user, $title, $comment) {
    if (!$user) {
      $user = 'Anonymous';
    }
    $iid = $this->instanceId;
    //no need on pseudo-instance
    if (!!($this->instanceId))
    {
      if ($cId) 
      {
        $query = "update `".GALAXIA_TABLE_PREFIX."instance_comments` set `wf_title`=?,`wf_comment`=? where `wf_instance_id`=? and `wf_c_id`=?";
        $this->query($query,array($title,$comment,(int)$iid,(int)$cId));
      } 
      else 
      {
        $hash = md5($title.$comment);
        if ($this->getOne("select count(*) from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? and `wf_hash`=?",array($iid,$hash))) 
        {
          return false;
        }
        $now = date("U");
        $query ="insert into `".GALAXIA_TABLE_PREFIX."instance_comments`(`wf_instance_id`,`wf_user`,`wf_activity_id`,`wf_activity`,`wf_title`,`wf_comment`,`wf_timestamp`,`wf_hash`) values(?,?,?,?,?,?,?,?)";
        $this->query($query,array((int)$iid,$user,(int)$activityId,$activity,$title,$comment,(int)$now,$hash));
      }
    }
    return true;
  }
  
  /**
   * Removes an instance comment
   *
   * @param int $cId
   * @access public
   * @return void
   */
  function remove_instance_comment($cId) {
    $iid = $this->instanceId;
    $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_c_id`=? and `wf_instance_id`=?";
    $this->query($query,array((int)$cId,(int)$iid));
  }
 
  /**
   * Lists instance comments
   *
   * @return array
   * @access public
   */
  function get_instance_comments() {
    $iid = $this->instanceId;
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? order by ".$this->convert_sortmode("timestamp_desc");
    $result = $this->query($query,array((int)$iid));    
    $ret = Array();
    while($res = $result->fetchRow()) {    
      $ret[] = $res;
    }
    return $ret;
  }

  /**
   * Get the activity candidates (more than one in split and switch)
   *
   * @param int $activityID The activity from which we want to obtain the candidate activities
   * @return array List of activities that can be reached from one given activity
   * @access public
   */
  function getActivityCandidates($activityID)
  {
    $query = 'SELECT wf_act_to_id FROM ' . GALAXIA_TABLE_PREFIX . 'transitions WHERE (wf_act_from_id = ?)';
    $result = $this->query($query, array((int) $activityID));
    $candidates = Array();
    while ($res = $result->fetchRow())
      $candidates[] = $res['wf_act_to_id'];
    return $candidates;
  }
}
?>
