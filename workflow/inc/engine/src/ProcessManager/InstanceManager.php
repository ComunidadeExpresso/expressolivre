<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'ProcessManager'.SEP.'BaseManager.php');
/**
 * Add, removes, modifies and lists instances.
 *
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class InstanceManager extends BaseManager {
  
  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object InstanceManager
   * @access public
   */
  function InstanceManager() 
  {
    parent::BaseManager();
    $this->child_name = 'InstanceManager';
  }

  /**
   * Gets an activities related to some instance
   * 
   * @param int $iid Instance Id
   * @return array Associative, describing activities and their relation with the instance
   * @access public
   */
  function get_instance_activities($iid)
  {
    $query = 'select ga.wf_type,ga.wf_is_interactive,ga.wf_is_autorouted,ga.wf_activity_id,ga.wf_name,
            gi.wf_p_id,gi.wf_instance_id,gi.wf_status,gi.wf_started,
            gia.wf_activity_id,gia.wf_user,gia.wf_status as wf_act_status 
            from '.GALAXIA_TABLE_PREFIX.'activities ga,
            INNER JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON ga.wf_activity_id=gia.wf_activity_id
            INNER JOIN '.GALAXIA_TABLE_PREFIX.'instances gi ON gia.wf_instance_id=gi.wf_instance_id,
            where gi.wf_instance_id=?';
    $result = $this->query($query, array($iid));
    $ret = Array();
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        // Number of active instances
        $ret[] = $res;
      }
    }
    return $ret;
  }

  /**
  * Describes given instance
  * 
  * @access public
  * @param int $iid Instance Id
  * @return array Associative, describing the instance
  */
  function get_instance($iid)
  {
    $query = 'select * from '.GALAXIA_TABLE_PREFIX.'instances gi where wf_instance_id=?';
    $result = $this->query($query, array($iid));
    $res = Array();
    if (!(empty($result)))
    {
      $res = $result->fetchRow();
      $res['wf_next_activity']=unserialize(base64_decode($res['wf_next_activity']));
      $res['wf_workitems']=$this->getOne('select count(*) from '.GALAXIA_TABLE_PREFIX.'workitems where wf_instance_id=?', array($iid));
    }
    return $res;
  }

  /**
   * Describes instance properties 
   * 
   * @access public
   * @param int $iid Instance id
   * @return array Associative, describing the instance properties
   */
  function get_instance_properties($iid)
  {
    $prop = unserialize(base64_decode($this->getOne('select wf_properties from '.GALAXIA_TABLE_PREFIX.'instances gi where wf_instance_id=?',array($iid))));
    return $prop;
  }

  /**
  * Start a transaction and lock the instance table on the given instance row.
  * It can lock as weel the instance-activities table.
  * 
  * @access private 
  * @param int $instanceId Instance id
  * @param int $activityId Activity id, 0 b default, the instance-activities table is not locked, instead the instance-activities table will be locked on the corresponding instance-activity row
  * @return bool 
  */
  function lockAndStartTrans($instanceId, $activityId=0)
  {
    //do it in a transaction, for activities running
    $this->db->StartTrans();
    //we need to make a row lock now, first on the instance table (always first!)
    $where = 'wf_instance_id='.(int)$instanceId;
    if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instances', $where)))
    {
      $this->error[] = 'Process Manager: '.tra('failed to obtain lock on %1 table', 'instances');
      $this->db->FailTrans();
    }
    if ($activityId)
    {
      //we need to make a row lock now, on the instance_activities table (always second!)
      $where = 'wf_instance_id='.(int)$instanceId.' and wf_activity_id='.(int)$activityId;
      if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instance_activities', $where)))
      {
        $this->error[] = 'Process Manager: '.tra('failed to obtain lock on %1 table','instances_activities');
        return false;
      }
    }
   } 

  
  /**
  * Saves given instance properties
  * 
  * @access public 
  * @param int $iid Instance Id
  * @param array $prop Associative, describing the instance properties
  * @return bool
  */
  function set_instance_properties($iid,&$prop)
  {
    $this->lockAndStartTrans($iid);
    //no more serialize, done by the core security_cleanup, empty array and bad properties names handled
    $prop = $this->security_cleanup($prop, false);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_properties=? where wf_instance_id=?';
    $this->query($query, array($prop,$iid));
    return $this->db->CompleteTrans();
  }
  
  /**
  * Saves given instance name
  * 
  * @access public 
  * @param int $iid Instance Id
  * @param string $name Instance name
  * @return bool
  */
  function set_instance_name($iid,$name)
  {
    $this->lockAndStartTrans($iid);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_name=? where wf_instance_id=?';
    $this->query($query, array($name,$iid));
    return $this->db->CompleteTrans();
  }

  /**
  * Saves given instance priority
  * 
  * @access public 
  * @param int $iid Instance id
  * @param int $priority Instance priority
  * @return bool
  */
  function set_instance_priority($iid,$priority)
  {
    $this->lockAndStartTrans($iid);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_priority=? where wf_instance_id=?';
    $this->query($query, array((int)$priority, (int)$iid));
    return $this->db->CompleteTrans();
  }

  /**
  * Saves given instance category
  * 
  * @access public 
  * @param int $iid Instance Id
  * @param string $category Instance category
  * @return bool
  */
  function set_instance_category($iid,$category)
  {
    $this->lockAndStartTrans($iid);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_category=? where wf_instance_id=?';
    $this->query($query, array((int)$category, (int)$iid));
    return $this->db->CompleteTrans();
  }

  /**
  * Saves given instance owner
  * 
  * @access public 
  * @param int $iid Instance Id
  * @param int $owner Instance owner id
  * @return bool
  */
  function set_instance_owner($iid,$owner)
  {
    $this->lockAndStartTrans($iid);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_instance_id=?';
    $this->query($query, array($owner, $iid));
    return $this->db->CompleteTrans();
  }
  
  /**
  * Saves given instance status
  * 
  * @access public 
  * @param int $iid Instance Id
  * @param string $status Instance status, should be one of 'active', 'completed', 'exception' or 'aborted
  * @return bool
  */
  function set_instance_status($iid,$status)
  {
    if (!(($status=='completed') || ($status=='active') || ($status=='aborted') || ($status=='exception')))
    {
      $this->error[] = tra('unknown status');
      return false;
    }
    $this->lockAndStartTrans($iid);
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_status=? where wf_instance_id=?';
    $this->query($query, array($status,$iid));
    return $this->db->CompleteTrans();
  }
  
  /**
  * Removes all previous activities on this instance and create a new activity on the activity given
  * 
  * @access public 
  * @param int $iid Instance id
  * @param int $activityId Activity id
  * @param mixed $user '*' by default and could be an user id
  * @param string $status 'running' by default but you could send 'completed' as well
  * @return bool False if any problems was encoutered (the database is then intact), true if everything was ok;
  * WARNING: if they were multiple activities ALL previous activities avaible on this instance are deleted
  */
  function set_instance_destination($iid,$activityId, $user='*', $status='running')
  {
    $this->lockAndStartTrans($iid, $activityId);
    $query = 'delete from '.GALAXIA_TABLE_PREFIX.'instance_activities where wf_instance_id=?';
    $this->query($query, array($iid));
    $query = 'insert into '.GALAXIA_TABLE_PREFIX.'instance_activities(wf_instance_id,wf_activity_id,wf_user,wf_status, wf_started, wf_ended)
    values(?,?,?,?,?,?)';
    $this->query($query, array($iid,$activityId,$user,$status,date('U'),0));
    // perform commit (return true) or Rollback (return false)
    return $this->db->CompleteTrans();
  }
 
  /**
  * Sets new user for activity $activityId if this activity is really related to the instance
  *  
  * @access public 
  * @param int $iid Instance Id
  * @param int $activityId Activity Id
  * @param int $user New user id
  * @return bool
  */
  function set_instance_user($iid,$activityId,$user)
  {
    $this->lockAndStartTrans($iid, $activityId);
    $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities set wf_user=? where wf_instance_id=? and wf_activity_id=?";
    $this->query($query, array($user, $iid, $activityId));
    return $this->db->CompleteTrans();
  }
  
  /**
  * Deletes all references to given user on all instances, concerning wf_user, wf_owner and wf_next_user fields
  * 
  * @param int $user User id to remove
  * @return bool
  * @access public
  */
  function remove_user($user)  
  {
    //TODO: add a global lock on the whole tables
    // user=id => user='*'
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=? where wf_user=?';
    $this->query($query,array('*',$user));
    // owner=id => owner=0
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_owner=?';
    $this->query($query,array(0,$user));
    // next_user=id => next_user=NULL
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_next_user=? where wf_next_user=?';
    $this->query($query,array(NULL,$user));
    return true;
  }
  
  /**
  * Transfers all references concerning one user to another user, concerning wf_user, wf_owner and wf_next_user fields
  * This function will not check access on the instance for the new user, it is the task of the admin to ensure the new user will have the necessary access rights
  * 
  * @param array $user_array Associative, keys are 'old_user' : current user id and 'new_user' : the new user id
  * @return bool
  * @access public
  */
  function transfer_user($user_array)  
  {
    $new_user = $user_array['new_user'];
    $old_user = $user_array['old_user'];
    //TODO: add a global lock on the whole tables
    // user
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=? where wf_user=?';
    $this->query($query,array($new_user,$old_user));
    // owner
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_owner=?';
    $this->query($query,array($new_user,$old_user));
    // next_user
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_next_user=? where wf_next_user=?';
    $this->query($query,array($new_user,$old_user));
    return true;
  }
  
  /**
  * Normalizes a property name
  * 
  * @access public 
  * @param string $name Name to normalize
  * @return string Property name
  */
  function normalize_name($name)
  {
    $name = trim($name);
    $name = str_replace(" ","_",$name);
    $name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
    return $name;
  }

}    

?>
