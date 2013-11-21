<?php
  /**
   * Realiza operações com os dados de configuração do workflow 
   * 
   * @author  viani
   * @version 1.0
   * @license http://www.gnu.org/copyleft/gpl.html GPL
   * @package Galaxia
   */ 
class ajax_config
{	
	/**		
 	* @var object $db database
	* @access public
	*/
	var $db;
  	/**		
   	* @var string $appname Application (module) to config
   	* @access public
   	*/
	var $appname;
  	/**		
   	* @var array $config_data Actual config-data
   	* @access public
   	*/
	var $config_data;	
  	/**		
   	* @var array $read_data Config-data as read from db
   	* @access public
   	*/
	var $read_data;		

  	/**
   	* Set appname and db for ajax config
   	* 
   	* @param string $appname Which application to config. Default workflow.
   	* @return void
   	* @access public
   	*/
	function ajax_config($appname = '')
	{
		if (! $appname)
		{
			$appname = 'workflow';
		}

		$this->db      =& Factory::getInstance('WorkflowObjects')->getDBExpresso();
		$this->appname = $appname;
  	}

 	/**
   	* Reads the whole repository for $this->appname, appname has to be set via the constructor
   	*
   	* @return array the whole config-array for that app
   	* @access public
   	*/
  	function read_repository()
  	{
		$this->config_data = array();

		$this->db->query("select * from phpgw_config where config_app='" . $this->appname . "'",__LINE__,__FILE__);
		while ($this->db->next_record())			
		{
			$test = @unserialize($this->db->f('config_value'));
			if($test)
			{
				$this->config_data[$this->db->f('config_name')] = $test;
			}
			else
			{
				$this->config_data[$this->db->f('config_name')] = $this->db->f('config_value');
			}
		}
		return $this->read_data = $this->config_data;
	}

  	/** 
   	* Updates the whole repository for $this->appname, you have to call read_repository() before (!)
   	* @access public	
   	* @return void
   	*/
	function save_repository()
	{
		if (is_array($this->config_data))
		{
			$this->db->lock(array('phpgw_config','phpgw_app_sessions'));
			if($this->appname == 'phpgwapi')
			{
				$this->db->query("delete from phpgw_app_sessions where sessionid = '0' and loginid = '0' and app = '".$this->appname."' and location = 'config'",__LINE__,__FILE__);
			}
			foreach($this->config_data as $name => $value)
			{
				$this->save_value($name,$value);
			}
			foreach($this->read_data as $name => $value)
			{
				if (!isset($this->config_data[$name]))	// has been deleted
				{
					$this->db->query("DELETE FROM phpgw_config WHERE config_app='$this->appname' AND config_name='$name'",__LINE__,__FILE__);
				}
			}
			$this->db->unlock();
		}
		$this->read_data = $this->config_data;
	}

	/**
	* Updates or insert a single config-value
	* 
	* @param string $name name of the config-value
	* @param mixed $value content
 	* @param string $app app-name, defaults to $this->appname set via the constructor
 	* @return array
	* @access public
	*/
	function save_value($name,$value,$app=False)
	{
		//echo "<p>config::save_value('$name','".print_r($value,True)."','$app')</p>\n";
		if (!$app || $app == $this->appname)
		{
			$app = $this->appname;
			$this->config_data[$name] = $value;
		}
		$name = $this->db->db_addslashes($name);

		if ($app == $this->appname && $this->read_data[$name] == $value)
		{
			return True;	// no change ==> exit
		}
		$this->db->query($sql="select * from phpgw_config where config_app='$app' AND config_name='$name'",__LINE__,__FILE__);
		if ($this->db->next_record())
		{
			$value_read = @unserialize($this->db->f('config_value'));
			if (!$value_read)
			{
				$value_read = $this->db->f('config_value');
			}
			if ($value_read == $value)
			{
				return True;	// no change ==> exit
			}
			$update = True;
		}
		//echo "<p>config::save_value('$name','".print_r($value,True)."','$app')</p>\n";

		if(is_array($value))
		{
			$value = serialize($value);
		}
		$value = $this->db->db_addslashes($value);

		$query = $update ? "UPDATE phpgw_config SET config_value='$value' WHERE config_app='$app' AND config_name='$name'" :
				"INSERT INTO phpgw_config (config_app,config_name,config_value) VALUES ('$app','$name','$value')";

		return $this->db->query($query,__LINE__,__FILE__);
	}

	/**
	* Deletes the whole repository for $this->appname, appname has to be set via the constructor
	* 
	* @access public
	* @return void 		
	*/	
	function delete_repository()
	{
		$this->db->query("delete from phpgw_config where config_app='" . $this->appname . "'",__LINE__,__FILE__);
	}

	/**
	* Deletes a single value from the repository, you need to call save_repository after
	* 
	* @param string $variable_name name of the config
	* @return void
	* @access public
	*/
	function delete_value($variable_name)
	{
		unset($this->config_data[$variable_name]);
	}
	/**
	* Sets a single value in the repository, you need to call save_repository after
	* 
	* @param string $variable_name name of the config
	* @param mixed $variable_data the content
	* @return void
	* @access public
	*/
	function value($variable_name,$variable_data)
	{
		$this->config_data[$variable_name] = $variable_data;
	}
	
}
?>
