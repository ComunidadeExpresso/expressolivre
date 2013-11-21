<?php

/**
 * NanoController
 *
 * @package NanoAjax
 *
 */
class NanoController
{
    /**
     * holds NanoJsonConverter object
     *
     * @var NanoJsonConverter
     */
    protected $_mObjJsonConverter;

    /**
     * holds JSON object
     *
     * @var JSON
     */
    protected $_mObjJson;

    /**
     * path to user classes
     *
     * @var string
     */
    protected $_mStrClassPath   = '';

    /**
     * default class suffix
     *
     * @var string
     */
    protected $_mStrClassSuffix = '.inc.php';
    protected $_mStrClassPreffix = 'class.ajax.';

    /**
     * holds NanoRequest object for executing request
     *
     * @var NanoRequest
     */
    protected $_mObjNanoRequest;

    /**
     * holds final JSON encoded result of virtual requests
     *
     * @var string
     */
    protected $_mStrVirtualRequestsResult = '';


    /**
     * Constructor
     *
     */
    function __construct()
    {
        $this->_mObjJsonConverter = new NanoJsonConverter();
        $this->_mObjJson          = new Services_JSON();
    }


    /**
     * Enter description here...
     *
     * @param string $path
     */
    public function setClassPath( $path )
    {
        if( is_string($path) && !empty($path) && is_dir($path) && is_readable($path) )
        {
            $this->_mStrClassPath = $path . (($path[strlen($path)-1] !== '/') ? '/' : '');
        }
        else
        {
            $this->_throw(__METHOD__,'php class path ['.$path.'] not valid!!!');
        }
    }


    /**
     * Enter description here...
     *
     * @param string $suffix
     */
    public function setClassSuffix( $suffix )
    {
        if( is_string($suffix) && !empty($suffix) )
        {
            $this->_mStrClassSuffix = $suffix;
        }
        else
        {
            $this->_throw(__METHOD__,'php class suffix not valid!!!');
        }
    }

    public function setClassPreffix( $preffix )
    {
        if( is_string($preffix) && !empty($preffix) )
        {
            $this->_mStrClassPreffix = $preffix;
        }
        else
        {
            $this->_throw(__METHOD__,'php class preffix not valid!!!');
        }
    }

    /**
     * Enter description here...
     *
     * @param string $json_data
     */
    public function setJsonData( $json_data )
    {
        $this->_mObjJsonConverter->setJsonString($json_data);
    }


    /**
     * Enter description here...
     *
     * @param boolean $print_data
     * @return unknown
     */
    public function iterateOverVirtualRequests()
    {
        $this->_mObjNanoRequest = new NanoRequest( $this->_mStrClassPath, $this->_mStrClassSuffix, $this->_mStrClassPreffix );
        $return_data            = array();

        foreach( $this->_mObjJsonConverter->getJsonDecodedAsArray() as $request_identifier => $request_data)
        {
            $output = array();

            try
            {
                $this->_mObjNanoRequest->setRequestData($request_data);

                $output['data'] = $this->_mObjNanoRequest->executeRequest();
            }
            catch (Exception $exception)
            {
                $output['exception'] = $exception->getMessage();
            }

            $return_data[$request_identifier] = $output;
        }

        $this->_mStrVirtualRequestsResult = $this->_mObjJson->encode( $return_data );
    }


    /**
     * returns result of virtual request(s) as encoded JSON string
     *
     * @return string
     */
    public function getResultData()
    {
        return $this->_mStrVirtualRequestsResult;
    }


    /**
     * outputs result of virtual request(s) as encoded JSON string
     *
     */
    public function outputResultData()
    {
        echo $this->_mStrVirtualRequestsResult;
    }


    /**
     * generates an (json) exception and output it
     *
     * @param string $method
     * @param string $msg
     */
    protected function _throw( $method, $msg )
    {
        die( $this->_mObjJson->encode(array('__NANOAJAX_SYSTEM_EXCEPTION__' => array('exception' => $msg))) );
    }

    /**
     * Throw error on all virtual requests
     *
     * @param string $message The message of the exception
     */
    public function throwErrorOnAllVirtualRequests($message)
    {
		$output = array();
		foreach ($this->_mObjJsonConverter->getJsonDecodedAsArray() as $requestIdentifier => $requestData)
			$output[$requestIdentifier] = array('exception' => $message);
        die($this->_mObjJson->encode($output));
    }
}

?>