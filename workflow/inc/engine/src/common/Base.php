<?php
/**
 * This class is derived by all the API classes so they get the
 * database connection and the database methods.
 *
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class Base {
	/**
	 * @var object $db Database abstraction object used to access the database
	 * @access public
	 */
	var $db;
	/**
	 * @var int	$num_queries Debugging var
	 * @access private
	 */
  	var $num_queries = 0;
	/**
	 * @var int	$num_queries_total Debugging var
	 * @access private
	 */
  	var $num_queries_total = 0;
	/**
	 * @var array  $error Error messages
	 * @access public
	 */
  	var $error= Array();
  	/**
  	 * @var array $warning Warning messages
  	 * @access public
  	 */
  	var $warning = array();
  	/**
  	 * @var string $child_name Name of the current object
  	 * @access public
  	 */
  	var $child_name = 'Base';

	/**
	 * @var object $db_shared_obj The database abstraction object shared between
	 *						      all instances of this class.
	 * @acess private
	 * @static
	 */
	private static $db_shared_obj = null;

  /**
   * Constructor receiving a database abstraction object
   * @package Galaxia
   * @param object &$db ADOdb
   * @return object Base instance
   * @access public
   */
  function Base()
  {
  	/**
	 * New Stuff!
	 * We decided to get here the database object. In a recent past,
	 * all the classes that specialize this one passed a db object.
	 * Now, to simplify and save memory, we store the database object
	 * into a single and static atribute shared among each instance
	 * of this class.
	 *
	 * To prevent to modify all sub-classes to use "self::$db" instead
	 * of "this->db", we made a very tiny workaround here. In the first
	 * instantiation of this class, we instantiate the database object
	 * and store it into 'self::$db_shared_obj'. Any subsequent
	 * instantiations will just point to the static one.
	 */
	if (!self::$db_shared_obj)
		self::$db_shared_obj = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;

	$this->db = &self::$db_shared_obj;


    if(!$this->db) {
      die('Invalid db object passed to '.$this->child_name.' constructor');
    }
    //Force transactionnal mysql (Innodb) -> mysqlt
    if ($this->db->databaseType=='mysql')
    {
    	$GLOBALS['phpgw']->db->disconnect();
    	$this->db = $GLOBALS['phpgw']->db->connect(
			$GLOBALS['phpgw_info']['server']['db_name'],
			$GLOBALS['phpgw_info']['server']['db_host'],
			$GLOBALS['phpgw_info']['server']['db_port'],
			$GLOBALS['phpgw_info']['server']['db_user'],
			$GLOBALS['phpgw_info']['server']['db_pass'],
			'mysqlt'
		);
    }
  }

  /**
   * Gets errors recorded by this object
   * Always call this function after failed operations on a workflow object to obtain messages
   *
   * @param bool $as_array if true the result will be send as an array of errors or an empty array. Else, if you do not give any parameter
   * or give a false parameter you will obtain a single string which can be empty or will contain error messages with <br /> html tags
   * @param bool $debug is false by default, if true you wil obtain more messages
   * @param string $prefix string appended to the debug message
   * @return mixed Error and debug messages or an array of theses messages and empty the error messages
   * @access public
   */
  function get_error($as_array=false, $debug=false, $prefix='')
  {
    //collect errors from used objects
    $this->collect_errors($debug, $prefix.$this->child_name.'::');
    if ($as_array)
    {
      $result = $this->error;
      $this->error= Array();
      return $result;
    }
    $result_str = implode('<br />',array_filter($this->error));
    $this->error= Array();
    return $result_str;
  }

  /**
   * Gets warnings recorded by this object
   *
   * @param bool $as_array if true the result will be send as an array of warnings or an empty array. Else, if you do not give any parameter
   * or give a false parameter you will obtain a single string which can be empty or will contain warning messages with <br /> html tags
   * @return mixed Warning messages or an array of theses messages and empty the warning messages
   * @access public
   */
  function get_warning($as_array=false)
  {
    if ($as_array)
    {
      $result = $this->warning;
      $this->warning= Array();
      return $result;
    }
    $result_str = implode('<br />',array_filter($this->warning));
    $this->warning= Array();
    return $result_str;
  }

  /**
   * Collect errors from all linked objects which could have been used by this object
   * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
   *
   * @param bool $debug is false by default, if true debug messages can be added to 'normal' messages
   * @param string $prefix is a string appended to the debug message
   * @abstract
   * @access public
   * @return void
   */
  function collect_errors($debug=false, $prefix = '')
  {
  	if ($debug)
  	{
  		$this->num_queries_total += $this->num_queries;
  		$this->error[] = $prefix.': number of queries: new='.$this->num_queries.'/ total='.$this->num_queries_total;
  		$this->num_queries = 0;
	}
  }

	/**
	 * Performs a query on the AdoDB database object
	 *
	 * @param string $query sql query, parameters should be replaced with ?
	 * @param array $values array containing the parameters (going in the ?), use it to avoid security problems. If
	 * one of theses values is an array it will be serialized and encoded in Base64
	 * @param int $numrows maximum number of rows to return
	 * @param int $offset starting row number
	 * @param bool $reporterrors is true by default, if false no warning will be generated in the php log
	 * @param string $sort is the sort sql string for the query (without the "order by ")
	 * @param bool $bulk is false by default, if true the $values array parameters could contain arrays vars for bulk statement
	 * (see ADOdb help) theses arrays wont be serialized and encoded in Base64 like current arrays parameters,
	 * it will be checked for security reasons before being appended to the sql
	 * @return mixed false if something went wrong or the resulting recordset array if it was ok
	 * @access public
	 */
	function query($query, $values = null, $numrows = -1, $offset = -1, $reporterrors = true, $sort='', $bulk=false)
	{
		//clean the parameters
		$clean_values = Array();
		if (!($values===null))
		{
			if (!(is_array($values)))
			{
				$values= array($values);
			}
			foreach($values as $value)
			{
				$clean_values[] = $this->security_cleanup($value, !($bulk));
			}
		}
		//clean sort order as well and add it to the query
		if (!(empty($sort)))
		{
			$sort = $this->security_cleanup($sort, true, true);
			$query .= " order by $sort";
		}


		//conversion must be done after oder by is set
		$this->convert_query($query);
		// Galaxia needs to be call ADOdb in associative mode
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		if ($numrows == -1 && $offset == -1)
			$result = $this->db->Execute($query, $clean_values);
		else
			$result = $this->db->SelectLimit($query, $numrows, $offset, $clean_values);
		if (empty($result))
		{
			$result = false;
		}
		$this->num_queries++;
		if (!$result)
		{
			$this->error[] = "there were some SQL errors in the database, please warn your sysadmin.";
			if ($reporterrors) $this->sql_error($query, $clean_values, $result);
		}
		return $result;
	}

	/**
	 * @see Base::query
	 * @param string $query sql query, parameters should be replaced with ?
     * @param array $values array containing the parameters (going in the ?), use it to avoid security problems
	 * @param bool $reporterrors is true by default, if false no warning will be generated in the php log
	 * @return mixed NULL if something went wrong or the first value of the first row if it was ok
	 * @access public
	 */
	function getOne($query, $values = null, $reporterrors = true) {
		$this->convert_query($query);
		$clean_values = Array();
		if (!($values===null))
		{
			if (!(is_array($values)))
			{
				$values= array($values);
			}
			foreach($values as $value)
			{
				$clean_values[] = $this->security_cleanup($value);
			}
		}
		$result = $this->db->SelectLimit($query, 1, 0, $clean_values);
		if (empty($result))
		{
			$result = false;
		}
		if (!$result && $reporterrors )
			$this->sql_error($query, $clean_values, $result);
		if (!!$result)
		{
			$res = $result->fetchRow();
		}
		else
		{
			$res = false;
		}
		$this->num_queries++;
		if ($res === false)
			return (NULL); //simulate pears behaviour
		list($key, $value) = each($res);
		return $value;
	}

	/**
	 * Throws error warnings
	 *
	 * @param string $query
	 * @param array $values
	 * @param mixed $result
	 * @access public
	 * @return void
	 */
	function sql_error($query, $values, $result) {
		trigger_error($this->db->databaseType . " error:  " . $this->db->ErrorMsg(). " in query:<br/>" . $query . "<br/>", E_USER_WARNING);
		// DO NOT DIE, if transactions are there, they will do things in a better way
	}

	/**
	 * Clean the data before it is recorded on the database
	 *
	 * @param $value is a data we want to be stored in the database.
	 * If it is an array we'll make a serialize and then an base64_encode
	 * (you'll have to make an unserialize(base64_decode())
	 * If it is not an array we make an htmlspecialchars() on it
	 * @param bool $flat_arrays is true by default, if false arrays won't be serialized and encoded
	 * @param bool $check_for_injection is false by default, if true we'll perform some modifications
	 * on the string to avoid SQL injection
	 * @return mixed @access public
	 */
	function security_cleanup($value, $flat_arrays = true, $check_for_injection = false)
	{
		if (is_array($value))
		{
			if ($flat_arrays) {
				//serialize and \' are a big #!%*
				$res = base64_encode(serialize($value));
			}
			else
			{
				//recursive cleanup on the array
				$res = Array();
				foreach ($value as $key => $item)
				{
					$res[$this->security_cleanup($key,$flat_arrays)] = $this->security_cleanup($item, $flat_arrays);
				}
			}
		}
		else
		{
			$res = ($check_for_injection)? addslashes(str_replace(';','',$value)) : $value;
		}
		return $res;
	}

	/**
	 * Supports DB abstraction
	 *
	 * @param string &$query
	 * @return void
	 * @access public
	 */
	function convert_query(&$query) {

		switch ($this->db->databaseType) {
		case "oci8":
			$query = preg_replace("/`/", "\"", $query);
			// convert bind variables - adodb does not do that
			$qe = explode("?", $query);
			$query = '';
			for ($i = 0; $i < sizeof($qe) - 1; ++$i) {
				$query .= $qe[$i] . ":" . $i;
			}
			$query .= $qe[$i];
			break;
		case "postgres7":
		case "sybase":
			$query = preg_replace("/`/", "\"", $query);
			break;
		}
	}
	/**
	 * Supports DB abstraction
	 *
	 * @param string $sort_mode
	 * @return string
	 * @access public
	 */
	function convert_sortmode($sort_mode) {
		$sort_mode = str_replace("__", "` ", $sort_mode);
		$sort_mode = "`" . $sort_mode;
		return $sort_mode;
	}
	/**
	 * Supports DB abstraction
	 *
	 * @return mixed
	 * @access public
	 */
	function convert_binary() {

		switch ($this->db->databaseType) {
		case "pgsql72":
		case "oci8":
		case "postgres7":
			return;
			break;
		case "mysql3":
		case "mysql":
			return "binary";
			break;
		}
	}

}
?>
