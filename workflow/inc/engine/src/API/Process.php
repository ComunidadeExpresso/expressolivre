<?php
require_once (GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
/**
 * Represents the process that is being executed when an activity
 * is executed. You can access this class methods using $process from any activity.
 * No need to instantiate a new object
 *
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Process extends Base {
	/**
	* @var string $name
	* @access protected 
	*/
	var $name;
	/**
	* @var string $description
	* @access protected 
	*/
	var $description;
	/**
	* @var int $version
	* @access protected 
	*/
	var $version;
	/**
	* @var string $normalizedName
	* @access protected 
	*/
	var $normalizedName;
	/**
	* @var string $pId Process id
	* @access protected 
	*/
	var $pId = 0;
	/**
	* @var array $config
	* @access protected 
	*/
	var $config = array();

	/**
	 * Constructor
	 *
	 * @param object $db
	 * @return object
	 * @access public
	 */
	function Process() 
	{
		$this->child_name = 'Process';
		parent::Base();
	}

  	/**
  	 * Loads a process from the database
  	 *
  	 * @param int $pId
  	 * @return bool
  	 * @access public
  	 */
	function getProcess($pId) 
	{
		$query = "select * from `".GALAXIA_TABLE_PREFIX."processes` where `wf_p_id`=?";
		$result = $this->query($query,array($pId));
		if(!$result->numRows()) return false;
		$res = $result->fetchRow();
		$this->name = $res['wf_name'];
		$this->description = $res['wf_description'];
		$this->normalizedName = $res['wf_normalized_name'];
		$this->version = $res['wf_version'];
		$this->pId = $res['wf_p_id'];
		//config is load only on the first getConfigValues call
	}
  
  /**
   * Gets the process Id
   *
   * @return int
   * @access public
   */
	function getProcessId() 
	{
		return $this->pId;
	}
  
  /**
   * Gets the normalized name of the process
   *
   * @return string
   * @access public
   */
	function getNormalizedName() 
	{
		return $this->normalizedName;
	}
  
	/**
	 * Gets the process name
	 *
	 * @return string
	 * @access public
	 */
	function getName() 
	{
		return $this->name;
	}
  
	/**
	 * Gets the process version
	 *
	 * @return int
	 * @access public
	 */
	function getVersion() 
	{
		return $this->version;
	}

  /**
   * Gets information about an activity in this process by name
   *
   * @param string $actname
   * @return array
   * @access public
   */
	function getActivityByName($actname) 
	{
		// Get the activity data
		$query = "select * from `".GALAXIA_TABLE_PREFIX."activities` where `wf_p_id`=? and `wf_name`=?";
		$pId = $this->pId;
		$result = $this->query($query,array($pId,$actname));
		if(!$result->numRows()) return false;
		$res = $result->fetchRow();
		return $res;
	}

	/**
	 * Store config values for this process
	 *
	 * @param array $parameters pairs of (config_variables_names => (type => value))
     * type can be int or text, anything else is considered text
     * int value of -1 is considered as 'default global configuration option'. So nothing
     * will be stored for this process (and existing values are erased)
	 * @return bool
	 * @access public
	 */
	function setConfigValues(&$parameters) 
	{
		if (!is_array($parameters))
		{
		  return false;
		}
		$array_delete=array();
		$array_set=array();
		$pId = (string)$this->pId;
		foreach ($parameters as $config_var => $config_value)
		{
		  //all config values will be deleted
		  $array_delete[] = array($pId, $config_var);
		  
		  //foreach but normally there's only one loop
		  foreach($config_value as $value_type => $value_zone)
		  {
		    $ok = true;
		    if ($value_type=='int')
		    {
		      //special value, refer to global config values for this process conf variable
		      //we don't want any value stored for this process conf variable
		      //so we break the foreach before setting the $array_set
		      if ($value_zone == -1)
		      {
		        $ok=false;
		        break;
		      }
		      //else it's classic
		      $value_int = $value_zone;
		      $value= '';
		    }
		    else
		    {
		      $value = $value_zone;
		      $value_int = null;
		    }
		  }
		  //we are going to set this config value if $ok says so
		  if ($ok) $array_set[] = array($config_var, $value, $value_int, $pId);
		}
		//delete previous config values if they are in a bulk statement
		if (count($array_delete)>0) 
		{
		  $result= $this->query("DELETE from ".GALAXIA_TABLE_PREFIX."process_config where wf_p_id=? and wf_config_name=?",$array_delete, -1,-1,true,'',true);
		}
		//insert in a bulk statement
		if (count($array_set)>0) 
		{
		    $result= $this->query("INSERT into ".GALAXIA_TABLE_PREFIX."process_config 
		      (wf_config_name,wf_config_value,wf_config_value_int,wf_p_id) values (?,?,?,?)"
		      ,$array_set, -1,-1,true,'', true);
		}
	}
  
	/**
	 * Gets all the process configuration values. The configuration data is then cached for this process object life
	 *
	 * @param array $parameters pairs of (config_variables_names => default_values)
     * For a variable name which has no previous value and no global value (default process value)
     * it will return default_value and this default value will be the NEW STORED value 
     * If no default value is given we assume it's a false
	 * @return array
	 * @access public
	 */
	function getConfigValues(&$parameters) 
	{
		if (!is_array($parameters))
		{
		  return false;
		}
		if (count($this->config) == 0) 
		{ // first time we come
		  // Get all the config data for this process
		  $query = "select * from ".GALAXIA_TABLE_PREFIX."process_config where wf_p_id=?";
		  $pId = $this->pId;
		  $result = $this->query($query,array($pId));
		
		  if($result->numRows()>0) 
		  {
		    //we add process datas for some config_name, we store it in $this->config
		    while ($res=$result->fetchRow())
		    {
		      //int values are not stored in the same field
		      $int_value= $res['wf_config_value_int'];
		      if (isset($int_value))
		      {
		        $this->config[$res['wf_config_name']] = $int_value;
		      }
		      else
		      {
		        $this->config[$res['wf_config_name']] = $res['wf_config_value'];
		      }
		    }
		  }
		}// the second time we jump here
		
		//parse config_name asked
		$local_array = array();
		$global_default_array = array();
		foreach ($parameters as $config_var => $default_value)
		{
		  if (isset($this->config[$config_var]))
		  {// we already know this config value
		    //echo "<br>ok we had one for ".$config_var;
		    $local_array[$config_var] = $this->config[$config_var];
		  }
		  else
		  {
		    // we have no value for it here, we'll ask it in the global conf
		    //echo "<br>we had nothing for ".$config_var;
		    $global_default_array[$config_var] = $default_value;
		  }
		}
		
		// if we have some not set value that we need to check in global conf
		if (count($global_default_array) > 0)
		{
		  $global_array =& galaxia_get_config_values($global_default_array);
		}
		$result = (array)$local_array + (array)$global_array;
		return $result;
	}
}

?>
