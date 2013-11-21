<?php
/**
 * Implements workflow module processes providing support for MVC architecture
 * @author Carlos Eduardo Nogueira Gonçalves
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.3
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class BaseModel
{
	/* begin constants declaration */
	/**
	 * @var string $CANCELAR Stopping activities shortcut
	 * @access public
	 */
	var $CANCELAR = '__leave_activity';

	/* end constants declaration */

	/* begin attributes declaration */
	/**
	 * @var array $workflow Workflow module information
     * @access public
     */
	var $workflow;

	/**
     * @var object $activity Activity class instance
     * @access public
     */
    var $activity;

	/**
     * @var object $factory Factory class reference
     * @access public
     */
    var $factory;

	/**
     * @var object $instance Instance class reference
     * @access public
     */
    var $instance;

	/**
	 * @var object $DAO DAO (Data Access Object) instance
     * @access public
     */
    var $DAO;

	/**
	 * @var object $natural Natural instance
     * @access public
     */
    var $natural;

	/**
     * @var array $viewData Data sent to view layer by Controller.
     * Template vars must have the same names of this array's keys
     * @access public
     */
    var $viewData = array();

	/**
	 * @var array $request Data received from the user
     * @access public
     */
    var $request;

	/**
     * @var string $commandText Holds SQL commands
     * @access public
     */
    var $commandText;

	/**
     * @var object Holds query resultsets
     * @access public
     */
    var $resultSet;

	/**
     * @var array $resultRow Holds resultset records
     * @access public
     */
    var $resultRow;
	/* end attributes declaration */
	
	/**
     * Constructor
     *
     * @param array $env MVC settings
     * @param boolean $autoAssign Automatically fills layers attributes with request data
     * @return object
     * @access public
     */
	function BaseModel(&$env, $autoAssign = false)
    {
        $this->DAO      =& $env['dao']     ;
        $this->workflow =& $env['workflow'];
        $this->instance =& $env['instance'];
        $this->activity =& $env['activity'];
        $this->request  =& $env['request'] ;
        $this->factory  =& $env['factory'] ;
        $this->natural  =& $env['natural'] ;

        if ( $autoAssign ) {
        	$this->getRequest();
        }
	 }
	/* begin methods declaration */

   /**
    * Returns layer's attributes along with their values
    *
    * @return array result vector
    * @access public
    */
	function getAttributes()
    {
		/* gets associative vector with name and declaration values of attributes */
		$attributes = get_class_vars(get_class($this));
		/* result vector */
		$result = array();
		/* iterates over attributes list */
		foreach ($attributes as $attribute => $value) {
			/* parses only process-level attributes, whose names start with a _ signal */
			if (preg_match('/^_{1}/', $attribute)) {
				$result[$attribute] = $this->{$attribute};
			}
		}
		/* return result vector */
		return $result;
	}

   /**
    * Returns the user supplied attributes that are also an attribute of the model class
	* @param mixed $fields Use an array of attributes names to use only those in the array. Use null (default value) to select all the attributes. Also, it's possible to use a string to specify only one attribute.
    * @return mixed The filtered array or false in case of errors.
    * @access public
    */
	function filterUserSuppliedAttributes($fields)
	{
		$attributes = $this->getAttributes();
		if (is_null($fields))
		{
			$fields = $attributes;
		}
		else
		{
			if (is_string($fields))
				$fields = array($fields);

			if (!is_array($fields))
				return false;

			$fields = array_flip(array_intersect(array_keys($attributes), $fields));
		}

		return $fields;
	}

   /**
    * Creates module API class instance
    *
    * @param string $obj Class name
    * @return mixed
    * @deprecated 2.2.00.000
    * @access public
    */
	function &getInstance($obj)
	{
		wf_warn_deprecated_method('Factory', 'getInstance');
		return(wf_create_object(strtolower($obj)));
	}

   /**
    * Maps request vars to layer attributes
	* @param mixed $fields Use an array of attributes names to use only those in the array. Use null (default value) to select all the attributes. Also, it's possible to use a string to specify only one attribute.
	* @return bool true in case of success of false otherwise.
    * @access public
    */
	function getRequest($fields = null, $from = null)
	{
		if (($fields = $this->filterUserSuppliedAttributes($fields)) === false)
			return false;

		/* define the origin of the variables */
		$request = is_null($from) ? $this->request : $from;
		foreach ($request as $key => $value)
			if (array_key_exists($key, $fields))
				@$this->{$key} = $value;

		return true;
	}

   /**
    * Stores value to be shown on view layer
    *
    * @param string $var Template var name
    * @param string $value Template var value
    * @return void
    * @access public
    */
	function addViewVar($var, $value)
    {
		@$this->viewData[$var] = $value;
	}

   /**
    * Retrieves view layer vars
    *
    * @param string $var Template var name
    * @return mixed
    * @access public
    */
	function getViewVar($var)
    {
		if ( array_key_exists($var , $this->viewData) ) {
			return ( $this->viewData[$var] );
		}
	}

   /**
    * Catches module properties
    *
    * @param string $property Property name
    * @return mixed property value
    * @access public
    */
	function getWfProperty($property)
    {
		return( $this->workflow[$property] );
	}

   /**
    * Sets module properties
    *
    * @param string $property property name
    * @param string $value value
    * @return void
    * @access public
    */
	function setWfProperty($property , $value)
    {
		if ( array_key_exists($property , $this->workflow) ) {
			$this->workflow[$property] = $value;
		}
	}

	/**
	 * Assign an identifier to the instance
	 * 
	 * @param string $param Indentifier to instance
	 * @return void
	 * @access public
	 */
	function setNameInstance($param)
	{
		$this->instance->setName($param);
	}

    /**
     * Serializes an array
     *
     * @param array $input Array var to be serialized
     * @access public
     * @return object ArrayObject
     * @since PHP 5
     * @throws Exception
     */
    function toObject($input = array())
    {
        if (is_array($input)) {
            return new ArrayObject($input);
    	}
        return false;
    }

    /**
     * Unserializes an array
     *
     * @param object $input ArrayObject to be unserialized
     * @since PHP 5
     * @return array
     * @access public
     */
    function fromObject(ArrayObject $input)
    {
        return iterator_to_array($input->getIterator());
    }

    /**
     * Give useful runtime information about classes and objects
     *
     * @param mixed $target Target class name or class instance
     * @return object Object with runtime information as properties
     * @access public
     * @since PHP 5
     */
    function inspect($target = null)
    {
        if (!is_null($target)) {
            switch(gettype($target)) {
                case 'string':
                    return new ReflectionClass($target);
                    break;
                case 'object':
                    return new ReflectionObject($target);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return false;
    }

   /**
     * Builds SQL commands
     *
     * @param string $commandText SQL command
     * @return void
     * @access public
     */
	function commandBuilder($commandText)
    {
		$this->commandText .= $commandText;
	}

   /**
     * Finalizes an activity life cycle
     *
     * @return void
     * @access public
     */
	function commitInstance()
    {
		$this->instance->complete();
	}

   /**
     * Updates module data with layer attributes
	 * @param mixed $fields Use an array of attributes names to use only those in the array. Use null (default value) to select all the attributes. Also, it's possible to use a string to specify only one attribute.
     * @return bool true in case of success of false otherwise.
     * @access public
     */
    function updateInstance($fields = null)
    {
    	$attributes = $this->getAttributes();
		if (($fields = $this->filterUserSuppliedAttributes($fields)) === false)
			return false;

		$fields = array_keys($fields);
    	foreach ($fields as $fieldName)
			$this->instance->set($fieldName, $attributes[$fieldName]);
		return true;
    }

   /**
     * Updates layer attributes with module data
	 * @param mixed $fields Use an array of attributes names to use only those in the array. Use null (default value) to select all the attributes. Also, it's possible to use a string to specify only one attribute.
     * @return bool true in case of success of false otherwise.
     * @access public
     */
    function updateAttributes($fields = null)
    {
		if (($fields = $this->filterUserSuppliedAttributes($fields)) === false)
			return false;

		$fields = array_keys($fields);
		foreach ($fields as $fieldName)
			if ($this->instance->exists($fieldName))
				$this->{$fieldName} = $this->instance->get($fieldName); /* now fills the current object with the values retrieved from the instance */

		return true;
    }

   /**
     * Sends a file for download. This method halts the execution of php by using the 'exit' "function".
     *
	 * @param string $filename The filename
	 * @param string $data The file's data
	 * @param string $mime The mime type of the file. If not used, a mime type that forces the download will be used.
     * @return bool false in case of error. In case of success, the script is interrupted to avoid incorrect behavior of php
     * @access public
     */
	function sendFile($filename, $data, $mime = null)
	{
		/* check if everything is ok */
		if (empty($filename))
			return false;

		/* check if the user supplied a mime type */
		if (is_null($mime))
			$mime = 'application/force-download';

		/* use the download mode */
		$this->setWfProperty('downloadMode', true);

		/* send the file */
		header('Pragma: public');
		header('Cache-Control: cache, must-revalidate');
		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Content-length: ' . strlen($data));
		echo $data;

		/* ends PHP execution to avoid incorret behavior */
		exit();
	}

   /**
     * Provides constructor access to its subclasses for processes startup settings
     *
     * @param array   $env MVC settings
     * @param boolean $autoAssign Automatically fills layers attributes with request data
     * @return object
     * @access public
     */
	function super(&$env, $autoAssign = false)
    {
		BaseModel::BaseModel(&$env, $autoAssign);
	}

	/* end methods declaration */
}
?>
