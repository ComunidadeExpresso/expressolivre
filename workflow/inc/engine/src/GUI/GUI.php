<?php require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
/**
 * Provides methods for use in typical user interface scripts
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @todo More options in list_user_instances, they should not be added by the external modules  
 */
class GUI extends Base {

  /**
   * @var object $wf_cecurity Used to obtain access for the user on certain actions from the engine
   * @access public
   */
  var $wf_security;
  /**
   * @var object  $pm Process manager object used to retrieve infos from processes
   * @access public
   */
  var $pm;
  /**
   * @var array $process_cache Cache to avoid queries
   * @access public
   */
  var $process_cache=Array();

  /**
   * Constructor
   * 
   * @param object &$db ADOdb
   * @return object GUI instance
   * @access public
   */
  function GUI()
  {
    $this->child_name = 'GUI';
    parent::Base();
    $this->wf_security = &Factory::getInstance('WfSecurity');
  }

  /**
   * Collects errors from all linked objects which could have been used by this object.
   * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
   * 
   * @param bool $debug False by default, if true debug messages can be added to 'normal' messages
   * @param string $prefix Appended to the debug message
   * @access public
   * @return void 
   */
  function collect_errors($debug=false, $prefix='')
  {
    parent::collect_errors($debug, $prefix);
    $this->error[] = $this->wf_security->get_error(false, $debug, $prefix);
  }

   /**
    * List user processes, user processes should follow one of these conditions:
    * 1) The process has an instance assigned to the user
    * 2) The process has a begin activity with a role compatible to the user roles
    * 3) The process has an instance assigned to '*' and the roles for the activity match the roles assigned to the user
    * 
    * @param int $user Current user id
    * @param int $offset Current starting point for the query results
    * @param int $maxRecords Max number of results to return
    * @param string $sort_mode For sorting
    * @param string $find Search in activity name or description
    * @param string $where Deprecated it's a string to add to the query, use with care for SQL injection 
    * @access public
    * @return array List of processes that match this and it also returns the number of instances that are in the process matching the conditions
    */
  function gui_list_user_processes($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = "where gp.wf_is_active=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    if (is_array($groups))
    {
      foreach ($groups as &$group)
        $group = "'{$group}'";
      $mid .= '	or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
    }
    $mid .= ')';
    $bindvars = array('y',$user);
    if($find) {
      $findesc = '%'.$find.'%';
      $mid .= " and ((gp.wf_name like ?) or (gp.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) {
      $mid.= " and ($where) ";
    }
    
    $query = "select distinct(gp.wf_p_id), 
                     gp.wf_is_active,                    
                     gp.wf_name as wf_procname, 
                     gp.wf_normalized_name as normalized_name, 
                     gp.wf_version as wf_version,
                     gp.wf_version as version
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              $mid";
    $query_cant = "select count(distinct(gp.wf_p_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              $mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset, true, $sort_mode);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) {
        // Get instances and activities per process,
        $pId=$res['wf_p_id'];
        $query_act = 'select count(distinct(ga.wf_activity_id))
              from '.GALAXIA_TABLE_PREFIX.'processes gp
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              where gp.wf_p_id=? 
              and (  ((gur.wf_user=? and gur.wf_account_type='u') ";
        if (is_array($groups))
        {
          $query_act .= ' or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
        }
        $query_act .= '))';
         
        $res['wf_activities']=$this->getOne($query_act,array($pId,$user));
        //we are counting here instances which are completed/exception or actives
        // TODO: maybe we should add a second counter with only running instances
        $query_inst = 'select count(distinct(gi.wf_instance_id))
              from '.GALAXIA_TABLE_PREFIX.'instances gi
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX."user_roles gur ON gar.wf_role_id=gur.wf_role_id
              where gi.wf_p_id=? 
              and (";
        if (is_array($groups))
        {
          $query_inst .= "(gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g') or ";
        }
        $query_inst .= "(gi.wf_owner=?) 
                         or ((gur.wf_user=?) and gur.wf_account_type='u'))";
        $res['wf_instances']=$this->getOne($query_inst,array($pId,$user,$user));
        $ret[] = $res;
      }
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }

  /**
   * Gets user activities
   * 
   * @param int $user Current user id
   * @param int $offset Current starting point for the query results
   * @param int $maxRecords Max number of results to return
   * @param string $sort_mode For sorting
   * @param string $find Search in activity name or description
   * @param string $where Deprecated it's a string to add to the query, use with care for SQL injection 
   * @param bool $remove_activities_without_instances False by default will remove all activities having no instances related at this time
   * @param bool $remove_instances_activities False by default, if true then all activities related to instances will be avoided (i.e. activities which are not standalone, start or view). If $remove_activities_without_instances is true you'll obtain nothing :-)
   * @param bool $add_start False by default, if true start activities are added to the listing, no effect if $remove_activities_without_instances is true
   * @param bool $add_standalone False by default, if true standalone activities are added to the listing, no effect if $remove_activities_without_instances is true
   * @param bool $add_view False by default, if true view activities are added to the listing, no effect if $remove_activities_without_instances is true
   * @return array Associative, key cant gives the number of results, key data is an associative array conteining the results
   * @access public
   */
  function gui_list_user_activities($user,$offset,$maxRecords,$sort_mode,$find,$where='', $remove_activities_without_instances=false, $remove_instances_activities =false, $add_start = false, $add_standalone = false, $add_view = false)
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);
    $mid = "where gp.wf_is_active=?";
    $bindvars = array('y');
    
    if ($remove_instances_activities)
    {
      $mid .= " and ga.wf_type <> ? and ga.wf_type <> ? and ga.wf_type <> ?  and  ga.wf_type <> ?  and  ga.wf_type <> ? ";
      $bindvars[] = 'end';
      $bindvars[] = 'switch';
      $bindvars[] = 'join';
      $bindvars[] = 'activity';
      $bindvars[] = 'split';
    }
    if (!($add_start))
    {
      $mid .= " and ga.wf_type <> ?";
      $bindvars[] = 'start';
    }
    if (!($add_standalone))
    {
      $mid .= " and ga.wf_type <> ?";
      $bindvars[] = 'standalone';
    }
    if (!($add_view))
    {
      $mid .= " and ga.wf_type <> ?";
      $bindvars[] = 'view';
    }

    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    if (is_array($groups))
      foreach ($groups as &$group)
        $group = "'{$group}'";
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    if (is_array($groups))
    {
      $mid .= '	or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
    }
    $mid .= ')';
    $bindvars[] = $user;
    if($find) {
      $findesc = '%'.$find.'%';
      $mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) {
      $mid.= " and ($where) ";
    }
    if ($remove_activities_without_instances)
    {
      $more_tables = "INNER JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gia.wf_activity_id=gar.wf_activity_id
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."instances gi ON gia.wf_instance_id=gi.wf_instance_id";
    }
    else
    {
	$more_tables = "";
    }
    $query = "select distinct(ga.wf_activity_id),
                     ga.wf_name,
                     NULLIF(ga.wf_menu_path, '') AS wf_menu_path,
                     ga.wf_type,
                     gp.wf_name as wf_procname,
                     ga.wf_is_interactive,
                     ga.wf_is_autorouted,
                     ga.wf_activity_id,
                     gp.wf_version as wf_version,
                     gp.wf_p_id,
                     gp.wf_is_active,
		     gp.wf_normalized_name
                from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
                $more_tables
                $mid";
              
    $query_cant = "select count(distinct(ga.wf_activity_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
                $more_tables
                $mid ";
    $result = $this->query($query,$bindvars,$maxRecords,$offset, true, $sort_mode);
    $cant = (int) $this->getOne($query_cant,$bindvars);
    $ret = Array();

    if (!empty($result) && ($cant > 0))
      $ret = $result->getArray(-1);

    $retval = Array();
    $retval['data'] = $ret;
    $retval['cant'] = $cant;
    return $retval;
  }

  /**
   * Gets user activities but each activity name (and not id) appears only one time
   * 
   * @param int $user Current user id
   * @param int $offset Current starting point for the query results
   * @param int $maxRecords Max number of results to return
   * @param string $sort_mode For sorting
   * @param string $find Search in activity name or description
   * @param string $where Deprecated it's a string to add to the query, use with care for SQL injection 
   * @param bool $remove_instances_activities False by default, if true then all activities related to instances will be avoided (i.e. activities which are not standalone, start or view).
   * @param bool $add_start False by default, if true start activities are added to the listing
   * @param bool $add_standalone False by default, if true standalone activities are added to the listing
   * @param bool $add_view False by default, if true view activities are added to the listing
   * @return array Associative, key cant gives the number of results, key data is an associative array conteining the results
   * @access public
   */	
	function gui_list_user_activities_by_unique_name($user,$offset,$maxRecords,$sort_mode,$find,$where='', $remove_instances_activities =false, $add_start = false, $add_standalone = false, $add_view = false)
	{
		// FIXME: this doesn't support multiple sort criteria
		//$sort_mode = $this->convert_sortmode($sort_mode);
		$sort_mode = str_replace("__"," ",$sort_mode);
		$mid = "where gp.wf_is_active=?";
		$bindvars = array('y');
		
                if ($remove_instances_activities)
                {
                   $mid .= " and ga.wf_type <> ? and ga.wf_type <> ? and ga.wf_type <> ?  and  ga.wf_type <> ?  and  ga.wf_type <> ? ";
                   $bindvars[] = 'end';
                   $bindvars[] = 'switch';
                   $bindvars[] = 'join';
                   $bindvars[] = 'activity';
                   $bindvars[] = 'split';
                }
		if (!($add_start))
		{
		  $mid .= " and ga.wf_type <> ?";
		  $bindvars[] = 'start';
                }
                if (!($add_standalone))
		{
		  $mid .= " and ga.wf_type <> ?";
		  $bindvars[] = 'standalone';
                }
                if (!($add_view))
		{
		  $mid .= " and ga.wf_type <> ?";
		  $bindvars[] = 'view';
                }
                
		// add group mapping, warning groups and user can have the same id
		$groups = galaxia_retrieve_user_groups($user);
		$mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
		if (is_array($groups))
		{
      foreach ($groups as &$group)
        $group = "'{$group}'";
		  $mid .= ' or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
                }
                $mid .= ')';

		$bindvars[] = $user;
		if($find) 
		{
			$findesc = '%'.$find.'%';
			$mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?))";
			$bindvars[] = $findesc;
			$bindvars[] = $findesc;
		}
		if($where) 
		{
			$mid.= " and ($where) ";
		}

		$query = "select distinct(ga.wf_name)
			from ".GALAXIA_TABLE_PREFIX."processes gp
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
			$mid";

		$query_cant = "select count(distinct(ga.wf_name))
			from ".GALAXIA_TABLE_PREFIX."processes gp
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
			$mid";
		$result = $this->query($query,$bindvars,$maxRecords,$offset, true, $sort_mode);
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = Array();
		if (!(empty($result)))
		{
		  while($res = $result->fetchRow()) 
		  {
			$ret[] = $res;
                  }
                }

		$retval = Array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

  /**
   * Gets start activities avaible for a given user
   * 
   * @param int $user Current user id
   * @param int $offset Current starting point for the query results
   * @param int $maxRecords Max number of results to return
   * @param string $sort_mode For sorting
   * @param string $find Search in activity name or description
   * @param string $where Deprecated it's a string to add to the query, use with care for SQL injection
   * @return array Associative, key cant gives the number of results, key data is an associative array conteining the results
   * @access public
   */	  
  function gui_list_user_start_activities($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = "where gp.wf_is_active=? and ga.wf_type=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    if (is_array($groups))
    {
      foreach ($groups as &$group)
        $group = "'{$group}'";
      $mid .= '	or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
    }
    $mid .= ')';
    $bindvars = array('y','start',$user);
    if($find)
    {
      //search on activities and processes
      $findesc = '%'.$find.'%';
      $mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?) or (gp.wf_name like ?) or (gp.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) 
    {
      $mid.= " and ($where) ";
    }

    $query = "select distinct(ga.wf_activity_id), 
                              ga.wf_name,
                              ga.wf_is_interactive,
                              ga.wf_is_autorouted,
                              gp.wf_p_id,
                              gp.wf_name as wf_procname,
                              gp.wf_version,
			      gp.wf_normalized_name
        from ".GALAXIA_TABLE_PREFIX."processes gp
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
	$mid";
    $query_cant = "select count(distinct(ga.wf_activity_id))
	from ".GALAXIA_TABLE_PREFIX."processes gp
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
	$mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset, true, $sort_mode);
    $ret = Array();
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        $ret[] = $res;
      }
    }
    $retval = Array();
    $retval["data"]= $ret;
    $retval["cant"]= $this->getOne($query_cant,$bindvars);
    
    return $retval;
  }

  /**
   * Gets instances avaible for a given user, theses instances are all the instances where the user is able to launch a gui action (could be a run --even a run view activity-- or an advanced action like grab, release, admin, etc)
   * type of action really avaible are not given by this function
   *
   * @see GUI::getUserAction
   * @access public
   * @param int $user User id
   * @param int $offset Starting number for the returned records
   * @param int $maxRecords Limit of records to return in data (but the 'cant' key count the total number without limits)
   * @param string $sort_mode Sort mode for the query
   * @param string $find Look at in activity name, activity description or instance name
   * @param string $where is an empty string by default, the string let you add a string to the SQL statement -please be carefull with it
   * @param bool $add_properties False by default, will add properties in the returned instances
   * @param int $pId Process id, 0 by default, in such case it is ignored
   * @param bool $add_completed_instances False by default, if true we add completed instances in the result
   * @param bool $add_exception_instances False by default, if true we add instances in exception in the result
   * @param bool $add_aborted_instances False by default, if true we add aborted instances in the result
   * @param bool $restrict_to_owner False by default, if true we restrict to instance for which the user is the owner even if it gives no special rights (that can give more or less results -- you'll have ownership but no rights but you wont get rights without ownership)
   * @param bool $add_non_interactive_instances_of_the_owner False by default, if true we include the non interactive instances the user owns
   * @return array Number of records in the 'cant key and instances in the 'data' key.
   * Each instance is an array containing theses keys: wf_instance_id, wf_started (instance), wf_ended (instance), wf_owner, wf_user, wf_status (instance status),
   * wf_category, wf_act_status, wf_act_started, wf_name (activity name), wf_type, wf_procname, wf_is_interactive, wf_is_autorouted, wf_activity_id, 
   * wf_version (process version), wf_p_id, insname (instance name), wf_priority and wf_readonly (which is true if the user only have read-only roles associated with this activity)
   */
  function gui_list_user_instances($user, $offset, $maxRecords, $sort_mode, $find, $where='', $add_properties=false, $pId=0, $add_active_instances=true, $add_completed_instances=false, $add_exception_instances=false, $add_aborted_instances=false, $restrict_to_owner=false, $add_non_interactive_instances_of_the_owner = false)
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = 'WHERE (gp.wf_is_active = ?)';
    $bindvars = array('y');

    /* restrict to process */
    if ($pId !== 0)
    {
        $mid .= ' AND (gp.wf_p_id = ?)';
        $bindvars[] = $pId;
    }

    /* look for a owner restriction */
    if ($restrict_to_owner)
    {
        $mid .= ' AND (gi.wf_owner = ?)';
        $bindvars[] = $user;
    }
    else /* no restriction on ownership, look for user and/or owner */
    {
      $groups = galaxia_retrieve_user_groups($user);
      if (is_array($groups))
        $groups = '{' . implode(', ', $groups) . '}';

      /* selects the instances that belong to a role, which the user mapped to */
      $mid .= ' AND (';
      $mid .= 'gia.wf_user IN (SELECT \'p\' || gur.wf_role_id FROM egw_wf_user_roles gur WHERE (gur.wf_user = ? and gur.wf_account_type=\'u\')';
      $bindvars[] = $user;
      if ($groups)
      {
        $mid .= ' OR (gur.wf_user = ANY (?) AND gur.wf_account_type=\'g\')';
        $bindvars[] = $groups;
      }
      $mid .= ')';

      /* selects the instances that belong to the user or everyone (the user must be mapped to a role that has access to the activity the instance is in) */
      $mid .= 'OR (((gia.wf_user = \'*\') OR (gia.wf_user = ?)) AND gia.wf_activity_id IN (SELECT gar.wf_activity_id FROM egw_wf_activity_roles gar, egw_wf_user_roles gur WHERE (gur.wf_role_id = gar.wf_role_id) AND ((gur.wf_user = ? AND gur.wf_account_type=\'u\')';
      $bindvars[] = $user;
      $bindvars[] = $user;
      if ($groups)
      {
        $mid .= 'OR (gur.wf_user = ANY (?) AND gur.wf_account_type = \'g\')';
        $bindvars[] = $groups;
      }
      $mid .= ')))';

      /* this collect non interactive instances we are owner of */
      if ($add_non_interactive_instances_of_the_owner)
      {
        $mid .= ' OR ((gi.wf_owner = ?) AND ga.wf_is_interactive = \'n\')';
        $bindvars[] = $user;
      }

      /* and this collect completed/aborted instances when asked which haven't got any user anymore */
      if ($add_completed_instances || $add_aborted_instances)
          $mid .= ' OR (gur.wf_user IS NULL)';

      $mid .= ')';
    }

    if($find)
    {
      $findesc = '%'. $find .'%';
      $mid .= " AND ((UPPER(ga.wf_name) LIKE UPPER(?))";
      $mid .= " OR (UPPER(gi.wf_name) LIKE UPPER(?)))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }

    if($where)
      $mid.= " AND ({$where}) ";

    /* instance selection :: instances can be active|exception|aborted|completed */
    $or_status = Array();
    if ($add_active_instances)
      $or_status[] = "(gi.wf_status = 'active')";
    if ($add_exception_instances)
      $or_status[] = "(gi.wf_status = 'exception')";
    if ($add_aborted_instances)
      $or_status[] = "(gi.wf_status = 'aborted')";
    if ($add_completed_instances)
      $or_status[] = "(gi.wf_status = 'completed')";
    if (!(empty($or_status)))
        $mid .= ' AND (' . implode(' OR ', $or_status) . ')';
    else
      /*special case, we want no active instance, and we do not want exception/aborted and completed, so what?
       * maybe a special new status or some bad record in database... */
      $mid .= " AND (gi.wf_status NOT IN ('active','exception','aborted','completed'))";


    $selectedColumns = array(
      'DISTINCT(gi.wf_instance_id)',
      'gi.wf_started',
      'gi.wf_ended',
      'gi.wf_owner',
      'gia.wf_user',
      'gi.wf_status',
      'gi.wf_category',
      'gia.wf_status AS wf_act_status',
      'gia.wf_started AS wf_act_started',
      'ga.wf_name',
      'ga.wf_type',
      'gp.wf_name AS wf_procname',
      'ga.wf_is_interactive',
      'ga.wf_is_autorouted',
      'ga.wf_activity_id',
      'gp.wf_version AS wf_version',
      'gp.wf_p_id',
      'gp.wf_normalized_name',
      'gi.wf_name AS insname',
      'gi.wf_priority'
    );

    /* add the read only column */
    $readOnlyColumn = '(SELECT MIN(gar.wf_readonly) FROM egw_wf_activity_roles gar, egw_wf_user_roles gur WHERE (gar.wf_activity_id = gia.wf_activity_id) AND (gur.wf_role_id=gar.wf_role_id) AND ((gur.wf_user = ? and gur.wf_account_type=\'u\')';
    if ($groups)
    {
      $readOnlyColumn .= ' OR (gur.wf_user = ANY (?) AND gur.wf_account_type = \'g\')';
      /* add the groups to be the second element of the array (there's another 'array_unshift' in the next lines) */
      array_unshift($bindvars, $groups);
    }
    /* add as the first element of the array */
    array_unshift($bindvars, $user);
    $readOnlyColumn .= ')) AS wf_readonly';
    $selectedColumns[] = $readOnlyColumn;

    /* if requested, retrieve the properties */
    if ($add_properties)
      $selectedColumns[] = 'gi.wf_properties';

    /* (regis) we need LEFT JOIN because aborted and completed instances are not showned
     * in instance_activities, they're only in instances */
    $query = 'SELECT ' . implode(', ', $selectedColumns) . ' ';
    $query .= 'FROM egw_wf_instances gi LEFT JOIN egw_wf_instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id ';
    $query .= 'LEFT JOIN egw_wf_activities ga ON gia.wf_activity_id = ga.wf_activity_id ';
    $query .= 'INNER JOIN egw_wf_processes gp ON gp.wf_p_id=gi.wf_p_id ';
    $query .= $mid;

    /* fetch the data (paging, if necessary) and count the records */
    $result = $this->query($query, $bindvars, -1, 0, true, $sort_mode);
    $cant = $result->NumRows();
    $realMaxRecords = ($maxRecords == -1) ? ($cant - $offset) : min($maxRecords, $cant - $offset);
    $ret = Array();
    if ($cant > $offset)
    {
      $result->Move($offset);
      for ($i = 0; $i < $realMaxRecords; ++$i)
      {
        $res = $result->fetchRow();
        if (substr($res['wf_user'], 0, 1) == 'p')
          $res['wf_user'] = '*';
        $ret[] = $res;
      }
    }

    $retval = Array();
    $retval['data'] = $ret;
    $retval['cant'] = $cant;
    return $retval;
  }

 /**
  * Gets all instances where the user is the owner (active, completed, aborted, exception)
  * 
  * @access public
  * @param int $user User id
  * @param int $offset Starting number for the returned records
  * @param int $maxRecords Limit of records to return in data (but the 'cant' key count the total number without limits)
  * @param string $sort_mode Sort mode for the query
  * @param string $find Look at in activity name, activity description or instance name
  * @param string $where Empty by default, the string let you add a string to the SQL statement -please be carefull with it
  * @param bool $add_properties False by default, will add properties in the returned instances
  * @param int $pId Process id, 0 by default, in such case it is ignored
  * @param bool $add_completed_instances False by default, if true we add completed instances in the result
  * @param bool $add_exception_instances False by default, if true we add instances in exception in the result
  * @param bool $add_aborted_instances False by default, if true we add aborted instances in the result
  * @param bool $add_non_interactive_instances_of_the_owner True by default, if true we include the non interactive instances the user owns
  * @return array Associative, key cant gives the number of results, key data is an array of instances and each instance
  * an array containing theses keys: wf_instance_id, wf_started (instance), wf_ended (instance), wf_owner, wf_user,
  * wf_status (instance status), wf_category, wf_act_status (activity), wf_act_started (activity), wf_name (activity name),
  * wf_type, wf_procname, wf_is_interactive, wf_is_autorouted, wf_activity_id, wf_version (process version), wf_p_id,
  * insname (instance name), wf_priority and wf_readonly (which is true if the user only have read-only roles associated
  * with this activity)
  */
  function gui_list_instances_by_owner($user, $offset, $maxRecords, $sort_mode, $find, $where='', $add_properties=false, $pId=0, $add_active_instances=true, $add_completed_instances=false, $add_exception_instances=false, $add_aborted_instances=false, $add_non_interactive_instances_of_the_owner = true)
  {
	  return $this->gui_list_user_instances($user,$offset,$maxRecords,$sort_mode,$find,$where,$add_properties, $pId,$add_active_instances,$add_completed_instances,$add_exception_instances, $add_aborted_instances, true, $add_non_interactive_instances_of_the_owner);
  }

  /**
   * Gets the view activity id avaible for a given process.
   * No test is done on real access to this activity for users, this access will be check at runtime (when clicking)
   * 
   * @param int $pId Process Id
   * @return mixed View activity id or false if no view activity is present dor this process
   * @access public
   */
  function gui_get_process_view_activity($pId)
  {
    if (!(isset($this->process_cache[$pId]['view'])))
    {
      if (!(isset($this->pm)))
      {
        $this->pm = &Factory::newInstance('ProcessManager');
      }
      $this->process_cache[$pId]['view'] = $this->pm->get_process_view_activity($pId);
    }
    return $this->process_cache[$pId]['view'];
  }

  /**
   * Gets all informations about a given instance and a given user, list activities and status.
   * We list activities for which the user is the owner or the actual user or in a role giving him access to the activity
   * notice that completed and aborted instances aren't associated with activities and that start and standalone activities
   * aren't associated with an instance ==> if instanceId is 0 you'll get all standalone and start activities in the result.
   * This is the reason why you can give --if you have it-- the process id, to restrict results to start and standalone
   * activities to this process
   * 
   * @access public
   * @param int $user User id
   * @param int $instance_id Instance id
   * @param int $pId Process id, 0 by default, in such case it is ignored
   * @param bool $add_completed_instances False by default, if true we add completed instances in the result
   * @param bool $add_exception_instances False by default, if true we add instances in exception in the result
   * @param bool $add_aborted_instances False by default, if true we add aborted instances in the result
   * @return array Associative, contains:
   * ['instance'] =>
   *    ['instance_id'], ['instance_status'], ['owner'], ['started'], 
   *    ['ended'], ['priority'], ['instance_name'], 
   *    ['process_name'], ['process_version'], ['process_id']
   * ['activities'] =>
   *     ['activity'] =>
   *         ['user']		    : current user
   *         ['id']		        : activity Id
   *         ['name']
   *         ['type']
   *         ['is_interactive']	: 'y' or 'n'
   *         ['is_autorouted']	: 'y' or 'n'
   *         ['status']
   */
  function gui_get_user_instance_status($user,$instance_id, $pId=0, $add_completed_instances=false,$add_exception_instances=false, $add_aborted_instances=false)
  {
    $bindvars =Array();
    $mid = "\n where gp.wf_is_active=?";
    $bindvars[] = 'y';
    if (!($pId==0))
    {
      // process restriction
      $mid.= " and gp.wf_p_id=?";
      $bindvars[] = $pId;
    }
    if (!($instance_id==0))
    {
      // instance selection
      $mid .= " and (gi.wf_instance_id=?)";
      $bindvars[] = $instance_id;
      $statuslist[]='active';
      if ($add_exception_instances) $statuslist[]='exception';
      if ($add_aborted_instances) $statuslist[]='aborted';
      if ($add_completed_instances) $statuslist[]='completed';
      $status_list = implode ($statuslist,',');
      $mid .= " and (gi.wf_status in ('".implode ("','",$statuslist)."'))\n";
    }
    else
    {
      // collect NULL instances for start and standalone activities
      $mid .= " and (gi.wf_instance_id is NULL)";
    }
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= "\n and ( ((gur.wf_user=? and gur.wf_account_type='u')";
    if (is_array($groups))
    {
      foreach ($groups as &$group)
        $group = "'{$group}'";
      $mid .= '	or (gur.wf_user in ('.implode(',',$groups).") and gur.wf_account_type='g')";
    }
    $mid .= ')';
    $bindvars[] = $user;
    // this collect non interactive instances we are owner of
    $mid .= "\n or (gi.wf_owner=?)"; 
    $bindvars[] = $user;
    // and this collect completed/aborted instances when asked which haven't got any user anymore
    if (($add_completed_instances) || ($add_aborted_instances))
    {
      $mid .= "\n or (gur.wf_user is NULL)";
    }
    $mid .= ")";
    
    // we need LEFT JOIN because aborted and completed instances are not showned 
    // in instance_activities, they're only in instances
    $query = 'select distinct(gi.wf_instance_id) as instance_id,
                     gi.wf_status as instance_status,
                     gi.wf_owner as owner,
                     gi.wf_started as started,
                     gi.wf_ended as ended,
                     gi.wf_priority as priority,
                     gi.wf_name as instance_name,
                     gp.wf_name as process_name,
                     gp.wf_version as process_version,
                     gp.wf_p_id as process_id,
                     gia.wf_user as user,
                     ga.wf_activity_id as id,
                     ga.wf_name as name,
                     ga.wf_type as type,
                     ga.wf_is_interactive as is_interactive,
                     ga.wf_is_autorouted as is_autorouted,
                     gia.wf_status as status';
    if ($instance_id==0)
    {//TODO: this gives all activities, rstrict to standalone and start
      $query.=' from '.GALAXIA_TABLE_PREFIX.'activities ga
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON ga.wf_activity_id=gia.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instances gi ON gia.wf_activity_id = gi.wf_instance_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=ga.wf_p_id '.$mid;
    }
    else
    {
      $query.=' from '.GALAXIA_TABLE_PREFIX.'instances gi
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activities ga ON gia.wf_activity_id = ga.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=gi.wf_p_id '.$mid;
    }
    $result = $this->query($query,$bindvars);
    $retinst = Array();
    $retacts = Array();
    if (!!$result)
    {
      while($res = $result->fetchRow()) 
      {
        // Get instances per activity
        if (count($retinst)==0)
        {//the first time we retain instance data
          $retinst[] = array_slice($res,0,-7);
        }
        $retacts[] = array_slice($res,10);
      }
    }
    $retval = Array();
    $retval["instance"] = $retinst{0};
    $retval["activities"] = $retacts;
    return $retval;
  }
  
  /**
   * Aborts an instance by terminating the instance with status 'aborted', and removes all running activities
   * 
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */
  function gui_abort_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    // start a transaction
    $this->db->StartTrans();

    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'abort')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $instance = &Factory::newInstance('Instance');
      $instance->getInstance($instanceId);
      if (!empty($instance->instanceId)) 
      {
          if (!($instance->abort()))
          {
            $this->error[] = ($instance->get_error());
            $this->db->FailTrans();
          }
      }
      unset($instance);
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }
  
  /**
   * Exception handling for an instance, setting instance status to 'exception', but keeps all running activities.
   * Instance can be resumed afterwards via gui_resume_instance()
   * 
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */
  function gui_exception_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    // start a transaction
    $this->db->StartTrans();

    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'exception')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?";
      $this->query($query, array('exception',$instanceId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  /**
   * Resumes an instance by setting instance status from 'exception' back to 'active'
   * 
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */  
  function gui_resume_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    // start a transaction
    $this->db->StartTrans();

    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'resume')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?";
      $this->query($query, array('active',$instanceId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  /**
   * Restarts an automated activity (non-interactive) which is still in running mode (maybe it failed)
   * 
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */  
  function gui_restart_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    //start a transaction
    $this->db->StartTrans();
    
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'restart')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $instance = &Factory::newInstance('Instance');
      $instance->getInstance($instanceId);
      // we force the execution of the activity
      $result = $instance->executeAutomaticActivity($activityId, $instanceId);      
      //TODO handle information returned in the sendAutorouted like in the completed activity template
      //_debug_array($result);
      $this->error[] = $instance->get_error();
      unset($instance);
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  /**
   * This function send a non autorouted activity i.e. take the transition which was not
   * taken automatically. It can be as well used to walk a transition which failed the first time by the admin
   * 
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */   
  function gui_send_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    //start a transaction
    $this->db->StartTrans();
    
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'send')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $instance = &Factory::newInstance('Instance');
      $instance->getInstance($instanceId);
      // we force the continuation of the flow
      $result = $instance->sendAutorouted($activityId,true);
      //TODO handle information returned in the sendAutorouted like in the completed activity template
      //_debug_array($result);
      $this->error[] = $instance->get_error();
      unset($instance);
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  
  /**
   * Releases instances
   *  
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */ 
  function gui_release_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    // start a transaction
    $this->db->StartTrans();

    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'release')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user = ? 
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array('*',$instanceId,$activityId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }
  
  /**
   * Grabs instance for this activity and user if the security object agreed
   *  
   * @access public
   * @param int $activityId  
   * @param int $instanceId
   * @return bool  
   */  
  function gui_grab_instance($activityId,$instanceId)
  {
    $user = galaxia_retrieve_running_user();
    
    // start a transaction
    $this->db->StartTrans();
    //this check will as well lock the table rows
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'grab')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user = ? 
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array($user,$instanceId,$activityId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  
 
  /**
  * Gets avaible actions for a given user on a given activity and a given instance assuming he already have access to it.
  * To be able to decide this function needs the user id, instance_id and activity_id. 
  * Optional arguments can be retrieved by internal queries BUT if you want this function to be fast and if you already 
  * have theses datas you should give as well these fields (all or none)
  * 
  * @access public  
  * @param int $user User id (required)
  * @param int $instanceId Instance id (can be 0 if you have no instance - for start or standalone activities, required) 
  * @param int $activityId Activity id (can be 0 if you have no activity - for aborted or completed instances, required)
  * @param bool $readonly Role mode, if true this is a readonly access, if false it is a not-only-read access (required)
  * @param int $pId Process id
  * @param string $actType Activity type string ('split', 'activity', 'switch', etc.)
  * @param string $actInteractive Activity interactivity ('y' or 'n')
  * @param string $actAutorouted Activity routage ('y' or 'n')
  * @param string $actStatus Activity status ('completed' or 'running')
  * @param int $instanceOwner Instance owner user id
  * @param string $instanceStatus Instance status ('completed', 'active', 'exception', 'aborted')
  * @param mixed $currentUser Current user of the instance (user id or '*')
  * @return array In this form:
  * array('action name' => 'action description')
  * 'actions names' are: 'grab', 'release', 'run', 'send', 'view', 'exception', 'resume' and 'monitor'
  * Some config values can change theses rules but basically here they are:
  * 	* 'grab'	: be the user of this activity. User has access to it and instance status is ok.
  * 	* 'release'	: let * be the user of this activity. Must be the actual user or the owner of the instance.
  * 	* 'run'		: run an associated form. This activity is interactive, user has access, instance status is ok.
  * 	* 'send'	: send this instance, activity was non-autorouted and he has access and status is ok.
  * 	* 'view'	: view the instance, activity ok, always avaible except for start or standalone act or processes with view activities.
  *	* 'viewrun'	: view the instance in a view activity, need to have a role on this view activity
  * 	* 'abort'	: abort an instance, ok when we are the user
  * 	* 'exception' 	: set the instance status to exception, need to be the user 
  * 	* 'resume'	: back to running when instance status was exception, need to be the user
  * 	* 'monitor' 	: special user rights to administer the instance
  * 'actions description' are translated explanations like 'release access to this activity'
  * WARNING: this is a snapshot, the engine give you a snaphsots of the rights a user have on an instance-activity
  * at a given time, this is not meaning theses rights will still be there when the user launch the action.
  * You should absolutely use the GUI Object to execute theses actions (except monitor) and they could be rejected.
  * WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via this
  * GUI object theses access rights are allready checked.
  */
  function getUserActions($user, $instanceId, $activityId, $readonly, $pId=0, $actType='not_set', $actInteractive='not_set', $actAutorouted='not_set', $actStatus='not_set', $instanceOwner='not_set', $instanceStatus='not_set', $currentUser='not_set') 
  {
    $result= array();//returned array

    //check if we have all the args and retrieve the ones whe did not have:
    if ((!($pId)) ||
      ($actType=='not_set') || 
      ($actInteractive=='not_set') || 
      ($actAutorouted=='not_set') || 
      ($actStatus=='not_set') ||
      ($instanceOwner=='not_set') ||
      ($currentUser=='not_set') ||
      ($instanceStatus=='not_set'))
    {
      // get process_id, type, interactivity, autorouting and act status and others for this instance
      // we retrieve info even if ended or in exception or aborted instances
      // and if $instanceId is 0 we get all standalone and start activities
      //echo '<br> call gui_get_user_instance_status:'.$pId.':'.$actType.':'.$actInteractive.':'.$actAutorouted.':'.$actStatus.':'.$instanceOwner.':'.$currentUser.':'.$instanceStatus;
      $array_info = $this->gui_get_user_instance_status($user,$instanceId,0,true,true,true);
      
      //now set our needed values
      $instance = $array_info['instance'];
      $pId = $instance['instance_id'];
      $instanceStatus = $instance['instance_status'];
      $instanceOwner = $instance['owner'];
      
      if (!((int)$activityId))
      {
        //we have no activity Id, like for aborted or completed instances, we set default values
        $actType = '';
        $actInteractive = 'n';
        $actAutorouted = 'n';
        $actstatus = '';
        $currentUser = 0;
      }
      else
      {
        $find=false;
        foreach ($array_info['activities'] as $activity)
        {
          //_debug_array($activity);
          //echo "<br> ==>".$activity['id']." : ".$activityId;
          if ((int)$activity['id']==(int)$activityId)
          {
            $actType = $activity['type'];
            $actInteractive = $activity['is_interactive'];
            $actAutorouted = $activity['is_autorouted'];
            $actstatus = $activity['status'];
            $currentUser = $activity['user'];
            $find = true;
            break;
          }
        }
        //if the activity_id can't be find we return empty actions
        if (!($find))
        {
          return array();
        }
      }
    }
    
    //now use the security object to get actions avaible, this object know the rules
    $view_activity = $this->gui_get_process_view_activity($pId);
    $result =& $this->wf_security->getUserActions($user, $instanceId, $activityId, $readonly, $pId, $actType, $actInteractive, $actAutorouted, $actStatus, $instanceOwner, $instanceStatus, $currentUser, $view_activity);
    return $result;
  }

  
}
?>
