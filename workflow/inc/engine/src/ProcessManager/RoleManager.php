<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'ProcessManager'.SEP.'BaseManager.php');

/**
 * Adds, removes, modifies and lists roles used in the Workflow engine.
 * Roles are managed in a per-process level, each role belongs to some process
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @todo Add a method to check if a role name exists in a process to prevent duplicate names 
 */
class RoleManager extends BaseManager {
    
  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object RoleManager
   * @access public
   */
  function RoleManager() 
  {
    parent::Base();
    $this->child_name = 'RoleManager';
  }

  /**
   * Gets role id
   * 
   * @param int $pid Process id
   * @param string $name Role name
   * @access public
   * @return string 
   */
  function get_role_id($pid,$name)
  {
    $name = addslashes($name);
    return ($this->getOne('select wf_role_id from '.GALAXIA_TABLE_PREFIX.'roles where wf_name=? and wf_p_id=?', array($name, $pid)));
  }
  
  /**
  * Gets a role
  * 
  * @param int $pId Process Id
  * @param int $roleId Role Id
  * @return array
  * @access public
  */
  function get_role($pId, $roleId)
  {
    $query = 'select * from `'.GALAXIA_TABLE_PREFIX.'roles` where `wf_p_id`=? and `wf_role_id`=?';
    $result = $this->query($query,array($pId, $roleId));
    $res = $result->fetchRow();
    return $res;
  }
  
  /**
  * Indicates if a role exists
  * 
  * @param int $pid Process Id
  * @param string $name Role name
  * @return int Number of roles with this name on this process
  * @access public
  */
  function role_name_exists($pid,$name)
  {
    $name = addslashes($name);
    return ($this->getOne('select count(*) from '.GALAXIA_TABLE_PREFIX.'roles where wf_p_id=? and wf_name=?', array($pid, $name)));
  }
  
  /**
   * Maps a user to a role
   * 
   * @param int $pId Process id
   * @param int $user User id
   * @param int $roleId Role id
   * @param string $account_type User account type
   * @return void 
   * @access public
   */
  function map_user_to_role($pId,$user,$roleId,$account_type='u')
  {
  $query = 'delete from `'.GALAXIA_TABLE_PREFIX.'user_roles` where wf_p_id=? AND wf_account_type=? and `wf_role_id`=? and `wf_user`=?';
  $this->query($query,array($pId, $account_type,$roleId, $user));
  $query = 'insert into '.GALAXIA_TABLE_PREFIX.'user_roles (wf_p_id, wf_user, wf_role_id ,wf_account_type)
  values(?,?,?,?)';
  $this->query($query,array($pId,$user,$roleId,$account_type));
  }
  
  /**
   * Removes a mapping
   * 
   * @param int $user User id
   * @param int $roleId Role id
   * @return void 
   * @access public
   */
  function remove_mapping($user,$roleId)
  { 
    $query = 'delete from `'.GALAXIA_TABLE_PREFIX.'user_roles` where `wf_user`=? and `wf_role_id`=?';
    $this->query($query,array($user, $roleId));
  }

  /**
   * Deletes all existing mappings concerning one user
   * 
   * @param int $user User id
   * @return void 
   * @access public
   */
  function remove_user($user)  
  {
    $query = 'delete from '.GALAXIA_TABLE_PREFIX.'user_roles where wf_user=?';
    $this->query($query,array($user));
  }
  
  /**
  * Transfers all existing mappings concerning one user to another user
  * 
  * @param array $user_array Associative, keys are: 'old_user': current user id and 'new_user', the new user id
  * @return void 
  * @access public
  */
  function transfer_user($user_array)  
  {
    $query = 'update '.GALAXIA_TABLE_PREFIX.'user_roles set wf_user=? where wf_user=?';
    $this->query($query,array($user_array['new_user'], $user_array['old_user']));
  }
  
  /**
  * Gets list of roles/users mappings for a given process
  * 
  * @param int $pId Process id, mappings are returned for a complete process
  * @param int $offset Starting record of the returned array
  * @param int $maxRecords Maximum number of records for the returned array
  * @param string $sort_mode Sort order for the query, like 'wf_name__ASC'
  * @param string $find Searched in role name, role description or user/group name
  * @return array Having for each row [wf_name] (role name),[wf_role_id],[wf_user] and [wf_account_type] ('u' user  or 'g' group),
  * Be aware 'cause you may have the same user or group several times if mapped to several roles
  * @access public
  */
  function list_mappings($pId,$offset,$maxRecords,$sort_mode,$find)  {
    $sort_mode = $this->convert_sortmode($sort_mode);
    $whereand = ' and gur.wf_p_id=? ';
    $bindvars = Array($pId);
    if($find) 
    {
      // no more quoting here - this is done in bind vars already
      $findesc = '%'.$find.'%';
      $whereand .=  ' and ((wf_name like ?) or (wf_user like ?) or (wf_description like ?)) ';
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    
    $query = "select wf_name,gr.wf_role_id,wf_user,wf_account_type from
                    ".GALAXIA_TABLE_PREFIX."roles gr,
                    ".GALAXIA_TABLE_PREFIX."user_roles gur 
                where gr.wf_role_id=gur.wf_role_id 
                $whereand";
    $result = $this->query($query,$bindvars, $maxRecords, $offset, true, $sort_mode);
    $query_cant = "select count(*) from 
                      ".GALAXIA_TABLE_PREFIX."roles gr, 
                      ".GALAXIA_TABLE_PREFIX."user_roles gur 
                  where gr.wf_role_id=gur.wf_role_id 
                  $whereand";
    $cant = $this->getOne($query_cant,$bindvars);

    $ret = Array();
    while($res = $result->fetchRow()) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }

  /**
   * Gets a list of users/groups mapped for a given process. Can expand groups to real users in the result and can restrict mappings to a given subset of roles and or activities
   * 
   * @param int $pId Process id, mappings are returned for a complete process by default (see param roles_subset or activities_subset)
   * @param bool $expand_groups If true (false by default) we are not giving the group mappings but instead expand these groups to real users while avoiding repeating users twice
   * @param array $subset Associative containing a list of roles and/or activities for which we want to restrict the list empty by default.
   * This array needs to contains the [wf_role_name] key with role names values to restrict roles.
   * This array needs to contains the [wf_activity_name] key with activity names values to restrict activities
   * @return array Associative, having for each row the user or group id and an associated name
   * @access public
   */

  function &list_mapped_users($pId,$expand_groups=false, $subset=Array())  
  {
    $whereand = ' where gur.wf_p_id=? ';
    $bindvars = Array($pId);
    
    if (!(count($subset)==0))
    {
       $roles_subset = Array();
       $activities_subset =Array();
       $activities_id_subset = Array();
       foreach($subset as $key => $value )
       {
         if ($key=='wf_role_name')
         {
           $roles_subset = $value;
         }
         if ($key=='wf_activity_name')
         {
           $activities_subset = $value;
         }
         if ($key == 'wf_activity_id')
         {
           $activities_id_subset = $value;
         }
       }
       if (count($roles_subset)>0)
       {
         if (!(is_array($roles_subset)))
         {
           $roles_subset = explode(',',$roles_subset);
         }
         $whereand .= " and ((gr.wf_name) in ('".implode("','",$roles_subset)."'))";
       }
       if (count($activities_subset)>0)
       {
         if (!(is_array($activities_subset)))
         {
           $activities_subset = explode(',',$activities_subset);
         }
         $whereand .= " and ((ga.wf_name) in ('".implode("','",$activities_subset)."'))";
       }
       if (count($activities_id_subset) > 0)
       {
         if (!(is_array($activities_id_subset)))
         {
           $activities_id_subset = explode(',',$activities_id_subset);
         }
         $whereand .= " and ((ga.wf_activity_id) in ('".implode("','",$activities_id_subset)."'))";
       }
    }
    $query = "select distinct(wf_user),wf_account_type from
                    ".GALAXIA_TABLE_PREFIX."roles gr
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gr.wf_role_id=gur.wf_role_id 
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_role_id=gr.wf_role_id 
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON ga.wf_activity_id=gar.wf_activity_id 
                $whereand ";
    $result = $this->query($query,$bindvars);
    $ret = Array();
    $ldap = &Factory::getInstance('WorkflowLDAP');
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        if (($expand_groups) && ($res['wf_account_type']=='g'))
        {
          //we have a group instead of a simple user and we want real users
          $real_users = galaxia_retrieve_group_users($res['wf_user'], true);
		  if (!empty($real_users)) {
            foreach ($real_users as $key => $value)
            {
              $ret[$key]=$value;
            }
		  }
        }
        else
        {
          $ret[$res['wf_user']] = $ldap->getName($res['wf_user']);
        }
      }
    }
    return $ret;
  }
  
  /**
   * Lists roles at process level
   * 
   * @param int $pId Process id
   * @param int $offset Starting resultset row
   * @param int $maxRecords Maximum number of records
   * @param string $sort_mode Query sorting mode
   * @param string $find Search query string
   * @param string $where Condition query string
   * @return array Roles list
   * @access public
   */
  function list_roles($pId,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    $sort_mode = $this->convert_sortmode($sort_mode);
    if($find) {
      // no more quoting here - this is done in bind vars already
      $findesc = '%'.$find.'%';
      $mid=' where wf_p_id=? and ((wf_name like ?) or (wf_description like ?))';
      $bindvars = array($pId,$findesc,$findesc);
    } else {
      $mid=' where wf_p_id=? ';
      $bindvars = array($pId);
    }
    if($where) {
      $mid.= " and ($where) ";
    }
    $query = 'select * from '.GALAXIA_TABLE_PREFIX."roles $mid";
    $query_cant = 'select count(*) from '.GALAXIA_TABLE_PREFIX."roles $mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset, 1, $sort_mode);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    while($res = $result->fetchRow()) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval['data'] = $ret;
    $retval['cant'] = $cant;
    return $retval;
  }
   
  /** 
  * Removes a role
  * 
  * @param int $pId Process Id
  * @param int $roleId Role Id
  * @return bool
  * @access public
  */
  function remove_role($pId, $roleId)
  {
    // start a transaction
    $this->db->StartTrans();
    $query = 'delete from `'.GALAXIA_TABLE_PREFIX.'roles` where `wf_p_id`=? and `wf_role_id`=?';
    $this->query($query,array($pId, $roleId));
    $query = 'delete from `'.GALAXIA_TABLE_PREFIX.'activity_roles` where `wf_role_id`=?';
    $this->query($query,array($roleId));
    $query = 'delete from `'.GALAXIA_TABLE_PREFIX.'user_roles` where `wf_role_id`=?';
    $this->query($query,array($roleId));
    // perform commit (return true) or Rollback (return false)
    return $this->db->CompleteTrans();
    }
  
  /**
  * Updates or inserts a new role in the database
  *  
  * @param array $vars Associative, having the fields to update or to insert as needed
  * @param int $pId Process id
  * @param int $roleId Role id, 0 in insert mode
  * @return mixed Role id (the new one if in insert mode) if everything was ok, false in the other case
  */
  function replace_role($pId, $roleId, $vars) 
  {
    // start a transaction 
    $this->db->StartTrans(); 
    $TABLE_NAME = GALAXIA_TABLE_PREFIX.'roles'; 
    $now = date("U");
    if (!(isset($vars['wf_last_modif']))) $vars['wf_last_modif']=$now; 
    $vars['wf_p_id']=$pId;
    
    foreach($vars as $key=>$value) 
    {
      $vars[$key]=addslashes($value);
    }
  
    if($roleId) {
      // update mode 
      $first = true; 
      $query ="update $TABLE_NAME set"; 
      $bindvars = Array();
      foreach($vars as $key=>$value) 
      {
        if(!$first) $query.= ','; 
        //if(!is_numeric($value)) $value="'".$value."'"; 
        $query.= " $key=? ";
        $bindvars[] = $value; 
        $first = false;
      }
      $query .= ' where wf_p_id=? and wf_role_id=? '; 
      $bindvars[] = $pId;
      $bindvars[] = $roleId; 
      $this->query($query, $bindvars);
    } 
    else 
    {
      //check unicity 
      $name = $vars['wf_name']; 
      if ($this->getOne('select count(*) from '.$TABLE_NAME.' where wf_p_id=? and wf_name=?', array($pId,$name))) 
      { 
        return false;
      }
      unset($vars['wf_role_id']); 
      // insert mode
      $bindvars = Array();
      $first = true;
      $query = "insert into $TABLE_NAME(";
      foreach(array_keys($vars) as $key)
      {
        if(!$first) $query.= ','; 
        $query.= "$key";
        $first = false;
      } 
      $query .=') values(';
      $first = true;
      foreach(array_values($vars) as $value) 
      {
        if(!$first) $query.= ','; 
        //if(!is_numeric($value)) $value="'".$value."'";
        $query.= '?';
        $bindvars[] = $value;
        $first = false;
      } 
      $query .=')';
      $this->query($query, $bindvars);
      //get the last inserted row
      $roleId = $this->getOne('select max(wf_role_id) from '.$TABLE_NAME.' where wf_p_id=?', array($pId)); 
    }
    // perform commit (return true) or Rollback (return false)
    if ($this->db->CompleteTrans())
    {
      // Get the id
      return $roleId;
    }
    else
    {
      return false;
    }
  }
  
  /**
  * Lists all users and groups recorded in the mappings with their status (user or group)
  * 
  * @return array Associative, containing a row for each user, where each row is an array containing 'wf_user' and 'wf_account_type' keys
  * @access public
  */
  function get_all_users()
  {
    $final = Array();
    //query for user mappings affected to groups & vice-versa
    $query ='select distinct(gur.wf_user), gur.wf_account_type
            from '.GALAXIA_TABLE_PREFIX.'user_roles gur';
    $result = $this->query($query);
    if (!(empty($result)))
    {
    while ($res = $result->fetchRow())
    {
        $final[] = $res;
    }
    }
    return $final;
  }

  /**
   * Get roles for a given user
   *
   * @param int $user The given user
   * @return array Roles for the given user
   * @access public
   */
  function getUserRoles($user, $pid = null)
  {
    /* retrieve user_groups information in an array containing all groups for this user */
    $userGroups = galaxia_retrieve_user_groups($user);
    $values = array($user);
    $query = 'SELECT wf_role_id FROM ' . GALAXIA_TABLE_PREFIX . 'user_roles
          WHERE (
            (wf_user = ? AND wf_account_type = \'u\')';
    if (is_array($userGroups))
    {
      foreach ($userGroups as &$group)
        $group = "'{$group}'";
      $query .= ' OR (wf_user IN (' . implode(',', $userGroups) . ') AND wf_account_type = \'g\')';
    }
    $query .= ')';
    if (!is_null($pid))
    {
      $query .= ' AND (wf_p_id = ?)';
      $values[] = $pid;
    }

    $result = $this->query($query, $values);
    $output = Array();
    while($res = $result->fetchRow())
      $output[] = $res['wf_role_id'];

    return $output;
  }

}
?>
