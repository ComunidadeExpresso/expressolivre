<?php

/**
 * NanoUtil class provides nice methods often used functionality
 *
 * @package NanoAjax
 *
 */
class NanoUtil
{
    /**
     * Enter description here...
     *
     * @param string $string
     * @return boolean
     */
    static public function isNotEmptyString( $string = '' )
    {
        return ( is_string($string) && '' != $string );
    }

    /**
     * Enter description here...
     *
     * @param array $array
     * @return boolean
     */
    static public function isNotEmptyArray( $array = array() )
    {
        return ( is_array($array) && count($array) > 0 );
    }

    /**
     * Enter description here...
     *
     * @param array $array
     * @param mixed $key
     * @param string $default
     * @return mixed
     */
    static public function getParam( $array, $key, $default = '' )
    {
        return ( ( is_array($array) && isset($key) && array_key_exists($key,$array) )
                       ? $array[$key]
                       : $default );
    }

    /**
     * Enter description here...
     *
     * @param array $array
     * @param unknown_type $key
     * @param unknown_type $fallback_array
     * @param unknown_type $fallback_key
     * @param unknown_type $default
     * @return unknown
     */
    static public function getParamFallback( $array, $key, $fallback_array, $fallback_key, $default = '' )
    {
        return ( '' != self::getParam( $array         , $key         , $default ) )
                     ? self::getParam( $array         , $key         , $default )
                     : self::getParam( $fallback_array, $fallback_key, $default );
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $object
     * @param unknown_type $property
     * @param unknown_type $key
     * @param unknown_type $default
     * @return unknown
     */
    static public function getProp( $object, $property, $key, $default = '' )
    {
        return ( ( is_object($object)
                   &&
                   isset($object->$property)
                   &&
                   !empty($key)
                   &&
                   is_array($object->$property)
                   &&
                   isset($object->{$property}[$key]) )
                      ? $object->{$property}[$key]
                      : $default );
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    static public function getThisScript()
    {
        return self::getRawFilename( $_SERVER['SCRIPT_NAME'] );
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    static public function getCurrentScriptWithPath()
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    static public function getFullUri()
    {
    	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    	                ? "https://"
    	                : "http://";

    	return $protocol.$_SERVER['HTTP_HOST'].self::getCurrentScriptWithPath();
    }
}

?>