<?php

/**
 * NanoSanitizer
 *
 * @package NanoAjax
 *
 */
class NanoSanitizer
{

    /**
     * Enter description here...
     *
     * @access protected
     * @var array
     */
    protected $_mArrSignaturePresets       = array();

    /**
     * Enter description here...
     *
     * @access protected
     * @var boolean
     */
    protected $_mBlnMagicQuotes            = false;

    /**
     * Enter description here...
     *
     * @access protected
     * @var array
     */
    protected $_mArrUnSecureVariables      = array();

    /**
     * Enter description here...
     *
     * @access protected
     * @var array
     */
    protected $_mArrSignatures             = array();

    /**
     * Enter description here...
     *
     * @access protected
     * @var integer
     */
    protected $_mIntCountUnSecureVariables = 0;

    /**
     * Enter description here...
     *
     * @access protected
     * @var integer
     */
    protected $_mIntCountSignature         = 0;

    /**
     * Enter description here...
     *
     * @access protected
     * @var array
     */
    protected $_mArrSanitizedVariables     = array();

    /**
     * Enter description here...
     *
     * @access protected
     * @var string
     */
    protected $_mStrSignaturePreset        = '';

    /**
     * Enter description here...
     *
     * @access protected
     * @var boolean
     */
    protected $_mBlnReportErrors           = false;

    /**
     * Enter description here...
     *
     * @access protected
     * @var boolean
     */
    protected $_mBlnStopOnError            = false;

    protected $logger;


    /**
     * Constructor, initializes object
     *
     * @access public
     * @return NanoSanitizer
     */
    public function __construct($logger)
    {
        $this->logger           = $logger;
        $this->_mBlnMagicQuotes = (bool) get_magic_quotes_gpc();
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $preset_file
     */
    public function loadPresets( $preset_file = '' )
    {
        $this->_mArrSignaturePresets = ( '' != $preset_file && is_file($preset_file) && is_readable($preset_file) )
                                            ? include($preset_file)
                                            : include(dirname(__FILE__).'/include/NanoSanitizer.presets.inc.php');
    }

    /**
     * Enter description here...
     *
     * @param string $signature_preset
     */
    public function setSignaturePreset( $signature_preset = '' )
    {
        $this->logger->rawAdd('setting signature preset...');

        if( NanoUtil::isNotEmptyString($signature_preset) && array_key_exists($signature_preset,$this->_mArrSignaturePresets) )
        {
            $this->_mStrSignaturePreset = $signature_preset;
            $this->logger->add('done.');
        }
        else
        {
            $this->_throw( __METHOD__,
                           ' signatur preset ('.$signature_preset.') {'.
                           gettype($signature_preset).'} NOT valid!' );
        }
    }


    /**
     * Sets the signature array with paramters for later verification
     *
     * @param array $signature_array
     */
    public function setSignatures( $signatures_array )
    {
        $this->logger->rawAdd('setting signatures...');

        if( NanoUtil::isNotEmptyArray($signatures_array) )
        {
            $this->_mArrSignatures     = $signatures_array;
            $this->_mIntCountSignature = count($signatures_array);
            $this->logger->add('done.');
        }
        else
        {
            $this->_throw( __METHOD__,
                           ' signatur array ('.implode('|',$signatures_array).
                           'is NOT valid!' );
        }
    }

    /**
     * Set variables which will be sanitized
     *
     * @param array $variables_array
     */
    public function setUnSecureData( $unsecure_variables_array )
    {
        $this->logger->rawAdd('setting unsecure variable array...');

        if( is_array($unsecure_variables_array) && count( $unsecure_variables_array ) > 0 )
        {
            $this->logger->add('done.');
            $this->_mArrUnSecureVariables      = $unsecure_variables_array;
            $this->_mIntCountUnSecureVariables = count($unsecure_variables_array);
        }
        else
        {
            $this->_throw( __METHOD__, ' unsecure variables array is NOT valid!' );
        }
    }

    /**
     * sets reporting of variable unequality
     *
     * @param boolean $bln_switch
     */
    public function setErrorReporting( $bln_switch )
    {
        if( is_bool($bln_switch) )
        {
            $this->_mBlnReportErrors = $bln_switch;
        }
    }

    /**
     * sets reporting of variable unequality
     *
     * @param boolean $bln_switch
     */
    public function setStopOnError( $bln_switch )
    {
        if( is_bool($bln_switch) )
        {
            $this->_mBlnStopOnError = $bln_switch;
        }
    }


    /**
     * Executes sanitization
     *
     */
    public function executeSanitization()
    {
        $this->logger->rawAdd('checking all parameters...');
        if( false == $this->_areParemetersValid() )
        {
            $this->_throw( __METHOD__,
                           'Signature / Input Variables Error !!! [S:'.
                           $this->_mIntCountSignature.'|I:'.
                           $this->_mIntCountUnSecureVariables.']' );
        }
        $this->logger->add('valid!');
        $this->logger->add('<br/>iterating over all sigantures...');

        foreach ($this->_mArrSignatures as $varname => $signature)
        {
            $this->logger->rawAdd('<br/>checking variable <b>'.$varname.'</b> is required but not present...');

            if( $this->_isRequiredVariableNotPresent($varname,$signature) )
            {
                $this->_throw( __METHOD__,
                               'Variable ['.$varname.'] is required, but NOT present!' );
            }

            $this->logger->add('OK. {'.(($this->_isVariableRequired($signature))?'required':'NOT required').'}');

            $this->logger->rawAdd('checking variable exists in unsecure array...');

            if( array_key_exists($varname,$this->_mArrUnSecureVariables) )
            {
                $this->logger->add('exists!');
                $variable_container = trim($this->_mArrUnSecureVariables[$varname]);

                $this->logger->rawAdd('searching for preset in signature...');

                // -------------------------------------------------------------
                // PRESET:
                // is preset in signature ?
                // set preset data to siganture
                if( true === $this->_isSignaturePresetValid($signature) )
                {
                    $this->logger->add('found! {<b>'.NanoUtil::getParam($signature,'preset').'</b>}');
                    $this->logger->rawAdd('setting signsture preset data to signature...');
                    $signature = $this->_getSignaturePresetData($signature);
                    $this->logger->add('done.');
                }
                else
                {
                    $this->logger->add('NOT found, using given signature.');
                }

                $this->logger->rawAdd('checking signature given type...');

                // -------------------------------------------------------------
                // TYPE:
                // apply type (integer, string, array,...) to variable
                // { brute force mode }
                if( $this->_isSignatureVarTypeValid($signature) )
                {
                    $this->logger->rawAdd('valid. setting type [<b>'.$signature['type'].'</b>]...');
                    settype( $variable_container, $signature['type'] );
                    $this->logger->add('done.');
                }

                $this->logger->rawAdd('searching for (filter) methods in signature...');

                // -------------------------------------------------------------
                // METHODS:
                // apply method(s) to variable
                if( $this->_isSignatureMethodsValid($signature) )
                {
                    $this->logger->add('found.');

                    // check if is not an array
                    if( !is_array($signature['methods']) )
                    {
                        // put methodname into array for later iteration
                        $signature['methods'] = array($signature['methods']);
                    }

                    $this->logger->add('iterating over given filter methods...');

                    // iterate over method array
                    foreach ( $signature['methods'] as $method_data )
                    {
                        $this->logger->rawAdd('executing method <b>'.$method_data['name'].'</b>...');

                        if( method_exists( $this, $method_data['name'] ) )
                        {
                            // execute method with variable as parameter
                            $variable_container = $this->$method_data['name']($variable_container,$method_data['limits']);
                        }
                        else
                        {
                            $logger_message = 'method ['.$method_data['name'].'] NOT found!!!';

                            if( true == $this->_mBlnStopOnError )
                            {
                                $this->_throw( __METHOD__,'method ['.$method_data['name'].'] NOT found!!!');
                            }
                            else { $this->logger->add('method ['.$method_data['name'].'] NOT found!!!'); }
                        }

                        $this->logger->add('done.');
                    }
                }
                else
                {
                    $this->logger->add('NOT found. (nothing to do)');
                }

                // -------------------------------------------------------------
                // REPORTING (report variable change / stop if has changed)
                if( true == $this->_mBlnReportErrors)
                {
                    // -------------------------------------------------------------
                    // VERIFICATION (is variable changed in value (after sanitization)
                    if( $this->_isVariableChanged($varname,$variable_container) )
                    {
                        $this->logger->add('NOT equal');

                        $message = '~~ Variable <b>'.$varname.'</b> '. /* $logger */
                        /* $logger */     'seems not to be same after sanitization '.
                        /* $logger */     '<div style="border:1px solid #CC0000;'.
                        /* $logger */     'background-color:#EEEEEE"><b>'.
                        /* $logger */     htmlentities($this->_mArrUnSecureVariables[$varname]).
                        /* $logger */     '</b>&nbsp;</div> {before} != <div style="'.
                        /* $logger */     'border:1px solid #CC0000;background-color:'.
                        /* $logger */     '#EEEEEE"><b>'.$variable_container.
                        /* $logger */     '</b>&nbsp;</div> {after}';

                        if( true == $this->_mBlnStopOnError )
                        {
                            $this->_throw( __METHOD__,$message);
                        }
                        else { $this->logger->add($message); }
                    }
                    else { $this->logger->add('IS equal'); }
                }

                // -------------------------------------------------------------
                // ASSIGNMENT
                $this->_assignCleanedVariable($varname,$variable_container);

            }
            else { $this->logger->add('not found! (continue with next)'); }
        }

        return $this->_mArrSanitizedVariables;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $varname
     * @param unknown_type $cleaned_variable
     */
    protected function _assignCleanedVariable( $varname, $cleaned_variable )
    {
        // -------------------------------------------------------------
        // ASSIGNMENT
        // set sanitized variable to new (output) array
        $this->logger->rawAdd('assigning cleaned input variable to output array...');
        $this->_mArrSanitizedVariables[$varname] = $cleaned_variable;
        $this->logger->add('done.');
    }

    protected function _isVariableChanged( $varname, $new_variable )
    {
        $this->logger->rawAdd('checking variable has changed while sanitization [<b>'.
                              htmlentities($this->_mArrUnSecureVariables[$varname]).'</b>] ?? [<b>'.
                              $new_variable.'</b>]...');

        return ( !empty($this->_mArrUnSecureVariables[$varname]) && $this->_mArrUnSecureVariables[$varname] != (string)$new_variable);
    }

    protected function _isRequiredVariableNotPresent( $varname, $signature )
    {
        return ( false == isset($this->_mArrUnSecureVariables[$varname])
                 &&
                 $this->_isVariableRequired($signature) );
    }

    protected function _isVariableRequired( $signature = array() )
    {
        return NanoUtil::getParam($signature,'required',false);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $signature
     * @return unknown
     */
    protected function _isSignaturePresetValid( $signature = array() )
    {
        return ( '' != NanoUtil::getParam($signature,'preset') && array_key_exists(NanoUtil::getParam($signature,'preset'),$this->_mArrSignaturePresets) )
                    ? true
                    : false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $signature
     * @return unknown
     */
    protected function _getSignaturePresetData( $signature = array() )
    {
        $preset_signature = $this->_mArrSignaturePresets[$signature['preset']];

        unset($signature['preset']);

        return array_merge($signature,$preset_signature);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $signature
     * @return unknown
     */
    protected function _isSignatureVarTypeValid( $signature = array() )
    {
        return ( '' != NanoUtil::getParam($signature,'type') )
                    ? true
                    : false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $signature
     * @return unknown
     */
    protected function _isSignatureMethodsValid( $signature = array() )
    {
        return ( '' != NanoUtil::getParam($signature,'methods') )
                    ? true
                    : false;
    }

    protected function _areParemetersValid()
    {
        // Check all needed parameter (arrays) are loaded, not empty
        // and size of both is equal
        return ( $this->_mIntCountSignature         > 0
                 &&
                 $this->_mIntCountUnSecureVariables > 0
                 &&
                 $this->_mIntCountSignature        == $this->_mIntCountUnSecureVariables )

                    // check passed
                    ? true
                    // check failed
                    : true;

    }

    // addslashes wrapper to check for gpc_magic_quotes
    protected function _addNiceSlashes($string)
    {
        // if magic quotes is on the string is already quoted, just return it
        return (MAGIC_QUOTES)

                    // return raw string
                    ? $string

                    // add slashes and return new string
                    : addslashes($string);
    }

    // internal function for utf8 decoding
    // PHP's utf8_decode function is a little
    // screwy
    protected function _decodeUtf8($string)
    {
        return utf8_decode($string);
        /*
        return strtr($string,
        "???????¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
        "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
        */
    }


    // default string sanitization -- only let the alphanumeric set through
    protected function _sanitizeDefaultAlphaNumericString($string, $limits = array() )
    {
        return $this->_getLimitedString(preg_replace("/[^a-zA-Z0-9_\-\ ]/", "", $string), $limits);
    }


    // paranoid sanitization -- only let the alphanumeric set through
    protected function _sanitizeParanoidAlphaNumericString($string, $limits = array() )
    {
        return $this->_getLimitedString(preg_replace("/[^a-zA-Z0-9]/", "", $string), $limits);
    }


    // default string sanitization -- only let the alphanumeric set through
    protected function _sanitizeDefaultGermanAlphaNumericString($string, $limits = array() )
    {
        return $this->_getLimitedString(preg_replace("/[^a-zA-Z0-9_\-\ äöüÄÖÜß]/", "", $string), $limits);
    }


    // paranoid sanitization -- only let the alphanumeric set through
    protected function _sanitizeParanoidGermanAlphaNumericString($string, $limits = array() )
    {
        return $this->_getLimitedString(preg_replace("/[^a-zA-Z0-9äöüÄÖÜß]/", "", $string), $limits);
    }

    // sanitize a string in prep for passing a single argument to
    // system() or exec() or passthru()
    protected function _sanitizeSystemString($string, $min = null, $max = null )
    {
        // no piping, passing possible environment variables ($),
        // seperate commands, nested execution, file redirection,
        // background processing, special commands (backspace, etc.), quotes
        // newlines, or some other special characters
        $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i';

        $string  = preg_replace($pattern, '', $string);

        //make sure this is only interpretted as ONE argument

        return $this->_getLimitedString('"'.preg_replace('/\$/', '\\\$', $string).'"', $min, $max);
    }


    // sanitize a string for SQL input (simple slash out quotes and slashes)
    protected function _sanitizeDefaultSqlString($string, $min = null, $max = null)
    {
        return $this->_getLimitedString($this->_saveEscapeString($string), $min, $max);
    }


    // sanitize a string for SQL input (simple slash out quotes and slashes)
    protected function _sanitizeParanoidSqlString($string, $min = null, $max = null)
    {
        return $this->_sanitizeDefaultSqlString(strip_tags($string), $min, $max);
    }


    // sanitize a string for HTML (make sure nothing gets interpretted!)
    protected function _sanitizeHtmlString($string)
    {
        $pattern = array( '/\&/',   //  0
                          '/</',    //  1
                          "/>/",    //  2
                          '/\n/',   //  3
                          '/"/',    //  4
                          "/'/",    //  5
                          "/%/",    //  6
                          '/\(/',   //  7
                          '/\)/',   //  8
                          '/\+/',   //  9
                          '/-/'  ); // 10

        $replace = array( '&amp;',  //  0
                          '&lt;',   //  1
                          '&gt;',   //  2
                          '<br/>',  //  3
                          '&quot;', //  4
                          '&#39;',  //  5
                          '&#37;',  //  6
                          '&#40;',  //  7
                          '&#41;',  //  8
                          '&#43;',  //  9
                          '&#45;' );// 10

        return preg_replace($pattern, $replace, $string);
    }

    // make float float!
    function _sanitizeFloat($float, $min='', $max='')
    {
        $float = floatval($float);
        if((($min != '') && ($float < $min)) || (($max != '') && ($float > $max)))
        return false;
        return $float;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $input
     * @param unknown_type $min
     * @param unknown_type $max
     * @return unknown
     */
    function check_float($input, $min='', $max='')
    {
        if($input != _sanitizeFloat($input, $min, $max))
        return false;
        return true;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $input
     * @param unknown_type $min
     * @param unknown_type $max
     * @return unknown
     */
    function check_sql_string($input, $min='', $max='')
    {
        if($input != _sanitizeSqlString($input, $min, $max))
        return false;
        return true;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $input
     * @param unknown_type $min
     * @param unknown_type $max
     * @return unknown
     */
    function check_ldap_string($input, $min='', $max='')
    {
        if($input != sanitize_string($input, $min, $max))
        return false;
        return true;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $input
     * @param unknown_type $min
     * @param unknown_type $max
     * @return unknown
     */
    function check_system_string($input, $min='', $max='')
    {
        if($input != _sanitizeSystemString($input, $min, $max, true))
        return false;
        return true;
    }


    /**
     * Enter description here...
     *
     * @param string $integer
     * @param array $limits
     * @return mixed
     */
    function _limitIntegerValue($integer, $limits = array() )
    {
        return ( $this->_isIntegerInRange($integer, $limits) )

                    ? (( true == $this->_mBlnReportErrors )

                          ? (( true == $this->_mBlnStopOnError )

                                ? $this->_throw(__METHOD__,'Length limit applies!!!')
                                : $this->logger->add('Length limit applies!!!'))

                          : false)

                    : $integer;
    }


    /**
     * Enter description here...
     *
     * @param string $string
     * @param array $limits
     * @return mixed
     */
    protected function _getLimitedString( $string, $limits = array() )
    {
        return ( $this->_isStringInRange($string,$limits) )

                    ? (( true == $this->_mBlnReportErrors )

                          ? (( true == $this->_mBlnStopOnError )

                                ? $this->_throw(__METHOD__,'Length limit applies!!!')
                                : $this->logger->add('Length limit applies!!!'))

                          : false)

                    : $string;
    }


    /**
     * Enter description here...
     *
     * @param string $string
     * @param array $limits
     * @return boolean
     */
    protected function _isStringInRange( $string, $limits = array() )
    {
        return ( ( isset($limits['min']) && strlen($string) < $limits['min'] )
                   ||
                 ( isset($limits['max']) && strlen($string) > $limits['max'] ) );
    }


    /**
     * Enter description here...
     *
     * @param string $string
     * @param array $limits
     * @return boolean
     */
    protected function _isIntegerInRange( $integer, $limits = array() )
    {
        return ( ( isset($limits['min']) && $integer < $limits['min'] )
                   ||
                 ( isset($limits['max']) && $integer > $limits['max'] ) );
    }


    /**
     * Enter description here...
     *
     * @param string $string
     * @return string mysql escaped string
     */
    private function _saveEscapeString( $string )
    {
        if( true == $this->_mBlnMagicQuotes )
        {
            $string = stripslashes($string);
        }

        return mysql_real_escape_string($string);
    }


    /**
     * Enter description here...
     *
     * @param string $method
     * @param string $msg
     */
    private function _throw( $method, $msg )
    {
        throw new Exception("[".$method.']: '.$msg);
    }
}

?>