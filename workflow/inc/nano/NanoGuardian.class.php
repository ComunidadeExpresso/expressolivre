<?php

/**
 * NanoGuardian helps to secure user defined classes against XSS or
 * SQL injection, etc.; it provides an easy to use interface to
 * NanoSanitizer
 *
 * @package NanoAjax
 *
 */
abstract class NanoGuardian
{
    /**
     * holds a NanoSanitizer object
     *
     * @var NanoSanitizer
     */
    protected $_mObjNanoSanitizer;

    /**
     * holds signatures for unsafe parameter variables
     *
     * @var array
     */
    protected $_mArrSignatures = array();


    /**
     * a Constructor
     *
     */
    public function __construct()
    {
        $this->_mObjNanoSanitizer = new NanoSanitizer(new DummyLogger);
    }


    /**
     * Enter description here...
     *
     * @param array $params
     */
    protected function _getSanatizedParameter( $params = array() )
    {
        $this->_mObjNanoSanitizer->setErrorReporting(true);
        $this->_mObjNanoSanitizer->loadPresets();
        $this->_mObjNanoSanitizer->setSignatures($this->_mArrSignatures);

        $this->_mObjNanoSanitizer->setUnSecureData($params);

        return $this->_mObjNanoSanitizer->executeSanitization();
    }


    /**
     * Enter description here...
     *
     * @param array $data
     */
    protected function _returnUtf8EncodedData( $data = array() )
    {
        array_walk_recursive($data,array($this,'_encodeUtf8'));
        return $data;
    }


    /**
     * Enter description here...
     *
     * @param mixed $item
     * @param unknown_type $key
     */
    protected function _encodeUtf8( &$item, $key )
    {
        $item = utf8_encode($item);
    }
}

?>