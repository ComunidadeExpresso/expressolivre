<?php

/**
 * NanoJsonConverter
 *
 * @package NanoAjax
 *
 */
class NanoJsonConverter
{
    /**
     * JSON object
     *
     * @var JSON
     */
    protected $_mObjJson;

    /**
     * holds JSON string data (if manually set)
     *
     * @var string
     */
    protected $_mStrJsonData    = '';

    /**
     * holds max read input data (from php:input) [raw input]
     *
     * @var integer
     */
    protected $_mIntMaxReadSize = 2048;

    /**
     * holds JSON decoded result (array, object)
     *
     * @var mixed
     */
    protected $_mMxdJsonResult;


    /**
     * sets max PHP input size (default: 2048 Bytes)
     *
     * @param integer $max_input_size
     */
    public function setMaxInputSize( $max_input_size = 2048 )
    {
        if( is_numeric($max_input_size) )
        {
            $this->_mIntMaxReadSize = $max_input_size;
        }
    }

    /**
     * return max input size (for unit tests only)
     *
     * @return unknown
     */
    public function getMaxInputSize()
    {
        return $this->_mIntMaxReadSize;
    }

    /**
     * set JSON input string (only for manually setting JSON string, unit tests)
     *
     * @param string $json_string
     */
    public function setJsonString( $json_string )
    {
        if( is_string($json_string) && !empty($json_string) )
        {
            $this->_mStrJsonData = $json_string;
        }
    }

    /**
     * return current JSON input String (for testing only)
     *
     * @return unknown
     */
    public function getJsonString()
    {
        return $this->_mStrJsonData;
    }

    /**
     * decode JSON string into an assoziative array
     *
     * @return array
     */
    public function getJsonDecodedAsArray()
    {
        $this->_initializeJson( 'array' );

        return (array)$this->_mObjJson->decode( $this->_getJsonData() );
    }

    /**
     * decode JSON string into an object (with instance variables)
     *
     * @return object
     */
    public function getJsonDecodedAsObject()
    {
        $this->_initializeJson();

        return (object)$this->_mObjJson->decode( $this->_getJsonData() );
    }


    /**
     * initializes JSON object with parameter
     *
     * @param string $return_type
     */
    protected function _initializeJson( $return_type = 'object' )
    {
        // initialize JSON object
        $this->_mObjJson = ( strtolower($return_type) != 'object' )
                                ? new Services_JSON( SERVICES_JSON_LOOSE_TYPE )
                                : new Services_JSON();
    }

    /**
     * load string dtaa from raw input or manually set input
     *
     * @return string
     */
    protected function _getJsonData()
    {
        return ($this->getJsonString() == '' )
                    ? trim(file_get_contents('php://input',$this->_mIntMaxReadSize))
                    : $this->getJsonString();
    }
}

?>