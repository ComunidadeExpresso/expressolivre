<?php
require_once (GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
/**
 * Represents activities, and must be derived for each activity type supported in the system. 
 * Derived activities extending this class can be found in the activities subfolder.
 * This class is observable.
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class BaseActivity extends Base {
  /**
   * @var string $name Activity's name
   * @access public
   */
  var $name;
  /**
   * @var string $normalizedName Activity's normalized (follows a pattern) name
   * @access public
   */  
  var $normalizedName;
  /**
   * @var string $description Activity's description
   * @access public
   */  
  var $description;
  /**
   * @var string $menuPath Activity's menu path
   * @access public
   */  
  var $menuPath;
  /**
   * @var bool $isInteractive
   * @access public
   */  
  var $isInteractive;
  /**
   * @var bool $isAutoRouted
   * @access public
   */  
  var $isAutoRouted;
  /**
   * @var array $roles
   * @access public
   */
  var $roles=Array();
  /**
   * @var array $outbound
   * @access public
   */
  var $outbound=Array();
  /**
   * @var array $inbound
   * @access public
   */
  var $inbound=Array();
  /**
   * @var int $pId Process's identification number 
   * @access public
   */
  var $pId;
  /**
   * @var int $activityId Activity's identification number 
   * @access public
   */  
  var $activityId;
  /**
   * @var string $type Activity's type 
   * @access public
   */
  var $type;
  /**
   * @var string  $defaultUser
   * @access public
   */
  var $defaultUser='*';
  /**
   * @var array $agents
   * @access public
   */  
  var $agents=Array();
  
  /**
   * Seems to be the rest of a bad object architecture
   * 
   * @deprecated 2.2.00.000
   */
  function setDb(&$db)
  {
	wf_warn_deprecated_method();
    $this->db =& $db;
  }
  
  /**
   * Constructor of the BaseActivity Object
   * 
   * @param object $db ADODB object
   * @access public
   */
  function BaseActivity()
  {
    $this->type='base';
    $this->child_name = 'BaseActivity';
    parent::Base();
  }

  /**
   * Factory method returning an activity of the desired type, loading the information from the database 
   * and populating the activity object with datas related to his activity type (being more than a BaseActivity then
   * 
   * @param int $activityId it is the id of the wanted activity
   * @param bool $with_roles true by default, gives you the basic roles information in the result
   * @param bool $with_agents false by default, gives you the basic agents information in the result
   * @param bool $as_array false by default, if true the function will return an array instead of an object
   * @return object Activity of the right class (Child class) or an associative array containing the activity 
   * information if $as_array is set to true
   * @access public  
   */
  function &getActivity($activityId, $with_roles= true,$with_agents=false,$as_array=false) 
  {
    $query = "select * from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?";
    $result = $this->query($query,array($activityId));
    if(!$result || !$result->numRows() ) return false;
    $res = $result->fetchRow();

    switch($res['wf_type']) {
      case 'start':
        $act = &Factory::newInstance('Start');
        break;

      case 'end':
        $act = &Factory::newInstance('End');
        break;

      case 'join':
        $act = &Factory::newInstance('Join');
        break;

      case 'split':
        $act = &Factory::newInstance('Split');
        break;

      case 'standalone':
        $act = &Factory::newInstance('Standalone');
        break;

      case 'view':
        $act = &Factory::newInstance('View');
        break;

      case 'switch':
        $act = &Factory::newInstance('SwitchActivity');
        break;

      case 'activity':
        $act = &Factory::newInstance('Activity');
        break;

      default:
        trigger_error('Unknown activity type:'.$res['wf_type'],E_USER_WARNING);
    }

    $act->setName($res['wf_name']);
    $act->setProcessId($res['wf_p_id']);
    $act->setNormalizedName($res['wf_normalized_name']);
    $act->setDescription($res['wf_description']);
    $act->setMenuPath($res['wf_menu_path']);
    $act->setIsInteractive($res['wf_is_interactive']);
    $act->setIsAutoRouted($res['wf_is_autorouted']);
    $act->setActivityId($res['wf_activity_id']);
    $act->setType($res['wf_type']);
    $act->setDefaultUser($res['wf_default_user']);
    
    //Now get forward transitions 
    
    //Now get backward transitions
    
    //Now get roles
    if ($with_roles)
    {
      $query = "select `wf_role_id` from `".GALAXIA_TABLE_PREFIX."activity_roles` where `wf_activity_id`=?";
      $result=$this->query($query,array($activityId));
      if (!(empty($result)))
      {
        while($res = $result->fetchRow()) 
        {
          $this->roles[] = $res['wf_role_id'];
        }
      }
      $act->setRoles($this->roles);
    }
    
    //Now get agents if asked so
    if ($with_agents)
    {
      $query = "select wf_agent_id, wf_agent_type from ".GALAXIA_TABLE_PREFIX."activity_agents where wf_activity_id=?";
      $result=$this->query($query,array($activityId));
      if (!(empty($result)))
      {
        while($res = $result->fetchRow()) 
        {
          $this->agents[] = array(
              'wf_agent_id'	=> $res['wf_agent_id'],
              'wf_agent_type'	=> $res['wf_agent_type'],
            );
        }
      }
      $act->setAgents($this->agents);
    }

    if ($as_array)
    {//we wont return the object but an associative array instead
       $res['wf_name']=$act->getName();
       $res['wf_normalized_name']=$act->getNormalizedName();
       $res['wf_description']=$act->getDescription();
       $res['wf_menu_path']=$act->getMenuPath();
       $res['wf_is_interactive']=$act->isInteractive();
       $res['wf_is_autorouted']=$act->isAutoRouted();
       $res['wf_roles']=$act->getRoles();
       //$res['outbound']=$act->get();
       //$res['inbound']=$act->get();
       $res['wf_p_id']=$act->getProcessId();
       $res['wf_activity_id']=$act->getActivityId();
       $res['wf_type']=$act->getType();
       $res['wf_default_user']=$act->getDefaultUser();
       $res['wf_agents']= $act->getAgents();
       return $res;
    }
    else
    {
      return $act;
    }
  }
  
  /**
   * Gets performed roles for a given user
   * 
   * @param array $user
   * @return array RoleIds for the given user
   * @access public
   */
  function getUserRoles($user) {
    
    // retrieve user_groups information in an array containing all groups for this user
    $user_groups = galaxia_retrieve_user_groups($GLOBALS['phpgw_info']['user']['account_id'] );
    // and append it to query                      
    $query = 'select `wf_role_id` from `'.GALAXIA_TABLE_PREFIX."user_roles` 
          where (
            (wf_user=? and wf_account_type='u')";
    if (is_array($groups))
    {
      foreach ($groups as &$group)
        $group = "'{$group}'";
      $mid .= '	or (wf_user in ('.implode(',',$groups).") and wf_account_type='g')";
    }
    $mid .= ')';

    $result=$this->query($query,array($user));
    $ret = Array();
    while($res = $result->fetchRow()) 
    {
      $ret[] = $res['wf_role_id'];
    }
    return $ret;
  }

  /**
   * Gets activity's roleId and name
   * 
   * @return array $ret Array of associative arrays with roleId and name
   * @access public
   */
  function getActivityRoleNames() {
    $aid = $this->activityId;
    $query = "select gr.`wf_role_id`, `wf_name` from `".GALAXIA_TABLE_PREFIX."activity_roles` gar, `".GALAXIA_TABLE_PREFIX."roles` gr where gar.`wf_role_id`=gr.`wf_role_id` and gar.`wf_activity_id`=?";
    $result=$this->query($query,array($aid));
    $ret = Array();
    while($res = $result->fetchRow()) {
      $ret[] = $res;
    }
    return $ret;
  }
  
  /**
   * Returns the normalized name for the activity
   * 
   * @return string
   * @access public 
   */
  function getNormalizedName() {
    return $this->normalizedName;
  }

  /**
   * Sets normalized name for the activity
   * 
   * @return void
   * @access public
   */  
  function setNormalizedName($name) {
    $this->normalizedName=$name;
  }
  
  /**
   * Sets the name for the activity
   * 
   * @param string New desired activity's name
   * @return void
   * @access public
   */
  function setName($name) {
    $this->name=$name;
  }
  
  /**
   * Gets the activity name
   * 
   * @return void
   * @access public
   */
  function getName() {
    return $this->name;
  }

  /**
   * Sets the agents for the activity object (no save)
   * 
   * @param array $agents Has 'wf_agent_id' and 'wf_agent_type' as keys
   * @return bool False if any problem is detected
   * @access public
   */
  function setAgents($agents) 
  {
    if (!(is_array($agents)))
    {
      $this->error[] = tra('bad parameter for setAgents, the parameter should be an array');
      return false;
    }
    $this->agents = $agents;
  }
  
  /**
   * Gets the activity agents
   * 
   * @return array Basic agents informations (id an type) or false if no agent is defined for this activity
   * @access public
   */
  function getAgents() 
  {
    if (empty($this->agents)) return false;
    return $this->agents;
  }
  
  /**
   * Sets the activity description
   * 
   * @param string $desc Activity description
   * @return void
   * @access public
   */
  function setDescription($desc) {
    $this->description=$desc;
  }

  /**
   * Gets the activity description
   * 
   * @return string
   * @access public
   */  
  function getDescription() {
    return $this->description;
  }

  /**
   * Sets the activity menu path
   * 
   * @param string $mp Menu path
   * @return void
   * @access public
   */
  function setMenuPath($mp) {
    $this->menuPath=$mp;
  }

  /**
   * Gets the activity menu path
   * 
   * @return string
   * @access public
   */  
  function getMenuPath() {
    return $this->menuPath;
  }
  
  /**
   * Sets the type for the activity although it does NOT allow you to change the current type
   * 
   * @param string $type 
   * @return void
   * @access public
   */
  function setType($type) {
    $this->type=$type;
  }
  
  /**
   * Gets the activity type
   * 
   * @return string
   * @access public
   */
  function getType() {
    return $this->type;
  }

  /**
   * Sets if the activity is interactive
   * 
   * @param bool $is
   * @return void
   * @access public
   */
  function setIsInteractive($is) {
    $this->isInteractive=$is;
  }
  
  /**
   * Returns if the activity is interactive
   * 
   * @return string
   * @access public
   */
  function isInteractive() {
    return $this->isInteractive == 'y';
  }
  
  /**
   * Sets if the activity is auto-routed
   * 
   * @param boolean $is 
   * @return void
   * @access public 
   */
  function setIsAutoRouted($is) {
    $this->isAutoRouted = $is;
  }
  
  /**
   * Gets if the activity is auto routed
   * 
   * @return string
   * @access public
   */
  function isAutoRouted() {
    return $this->isAutoRouted == 'y';
  }

  /**
   * Sets the processId for this activity
   * 
   * @param int $pid
   * @return void 
   * @access public
   */
  function setProcessId($pid) {
    $this->pId=$pid;
  }
  
  /**
   * Gets the processId for this activity
   * 
   * @return int 
   * @access public
   */
  function getProcessId() {
    return $this->pId;
  }

  /**
   * Gets the activityId
   * 
   * @return int 
   * @access public
   */
  function getActivityId() {
    return $this->activityId;
  }  
  
  /**
   * Sets the activityId
   * 
   * @param int $id
   * @return void 
   * @access public
   */
  function setActivityId($id) {
    $this->activityId=$id;
  }
  
  /**
   * Gets array with roleIds asociated to this activity
   * 
   * @return array 
   * @access public
   */
  function getRoles() {
    return $this->roles;
  }
  
  /**
   * Sets roles for this activities, should receive an array of roleIds
   * 
   * @param array $roles
   * @return void 
   * @access public
   */
  function setRoles($roles) {
    $this->roles = $roles;
  }

  /**
   * Gets default user id associated with this activity as he's recorded 
   * there's no check about validity of this user
   * 
   * @return string 
   * @access protected
   */
  function getDefaultUser() {
    return $this->defaultUser;
  }

  /**
   * Sets the default user for an activity
   * 
   * @param string $default_user
   * @return void 
   * @access public
   */
  function setDefaultUser($default_user)
  {
    if ((!isset($default_user)) || ($default_user=='') || ($default_user==false))
    {
      $default_user='*';
    }
    $this->defaultUser = $default_user;
  }

   /**
   * Checks if a user has a certain role (by name) for this activity,
   * e.g. $isadmin = $activity->checkUserRole($user,'admin')
   * 
   * @deprecated 2.2.00.000 - Unused function. Old API, do not use it. Return always false
   */
  function checkUserRole($user,$rolename) 
  {
	wf_warn_deprecated_method();
    $this->error[] = 'use of an old deprecated function checkUserRole, return always false';
    return false;
  }

}
?>
