<?php

/**
 * NanoRequest
 *
 * @package NanoAjax
 *
 */
class NanoRequest
{
    /**
     * Enter description here...
     *
     * @var array
     */
    private $_mArrRequestData = array();

    /**
     * path to user classes
     *
     * @var string
     */
    private $_mStrClassPath   = '';

    /**
     * default class suffix
     *
     * @var string
     */
    private $_mStrClassSuffix = '.inc.php';
    private $_mStrClassPreffix = 'class.ajax.';


    /**
     * Enter description here...
     *
     * @param unknown_type $request_data
     */
    public function __construct( $class_path, $class_suffix, $class_preffix )
    {
        $this->_mStrClassPath   = $class_path;
        $this->_mStrClassSuffix = $class_suffix;
        $this->_mStrClassPreffix = $class_preffix;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $request_data
     */
    public function setRequestData($request_data)
    {
        if( $this->_isRequestDataValid($request_data) )
        {
            $this->_mArrRequestData = $request_data;
        }
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function executeRequest()
    {
        $this->_loadUserDefinedClass( $this->_mArrRequestData['parameter']['action'] );

        if( false !== ($userclass = $this->_initializeUserClass($this->_mArrRequestData['parameter']['action'])) )
        {
            return $this->_callUserMethod( $userclass, $this->_mArrRequestData['parameter']['mode'], $this->_mArrRequestData['data'] );
        }
        else
        {
            $this->_throwException('user class ['.$this->_mArrRequestData['parameter']['action'].'] not found!!!');
        }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $data
     * @return unknown
     */
    private function _isRequestDataValid( $data )
    {
        if( is_array($data) && count($data) > 0 )
        {
            if( isset($data['parameter']) && !empty($data['parameter']) )
            {
                if( isset($data['parameter']['action']) && !empty($data['parameter']['action']) )
                {
                    if( isset($data['parameter']['mode']) && !empty($data['parameter']['mode']) )
                    {
                        return true;
                    }
                    else
                    {
                        $this->_throwException('missing or empty Parameter [mode] in (virtual) request!!!');
                    }
                }
                else
                {
                    $this->_throwException('missing or empty Parameter [action] in (virtual) request!!!');
                }
            }
            else
            {
                $this->_throwException('missing all parameter data in (virtual) request!!!');
            }
        }
        else
        {
            $this->_throwException('invalid formed (virtual) request!!!');
        }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $classname
     */
    protected function _loadUserDefinedClass( $classname )
    {
		( NanoUtil::isNotEmptyString($classname) && /*!class_exists($classname) &&*/ file_exists($this->_mStrClassPath.$this->_mStrClassPreffix.$classname.$this->_mStrClassSuffix) && is_readable($this->_mStrClassPath.$this->_mStrClassPreffix.$classname.$this->_mStrClassSuffix) )
             ? require_once( $this->_mStrClassPath.$this->_mStrClassPreffix.$classname.$this->_mStrClassSuffix )
             : $this->_throwException('given class {'.$this->_mStrClassPreffix.$classname.$this->_mStrClassSuffix.
                                      '} does not exists');
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $classname
     * @return unknown
     */
    protected function _initializeUserClass( $classname )
    {
        return ( class_exists($classname) )
                    ? new $classname()
                    : false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $userclass
     * @param unknown_type $usermethod
     * @param unknown_type $userdata
     * @return unknown
     */
    protected function _callUserMethod( $userclass, $usermethod, $userdata )
    {
        return ( method_exists( $userclass, $usermethod ) )
                    ? $userclass->$usermethod($userdata)
                    : $this->_throwException('given user method {'.$usermethod.
                                             '} does not exists in class ['.
                                             get_class($userclass).']');
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $msg
     */
    private function _throwException( $msg )
    {
        throw new Exception($msg);
    }
}

?>