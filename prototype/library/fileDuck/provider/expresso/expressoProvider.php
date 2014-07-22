<?php

require_once __DIR__ . '/../iProvider.php';

class expressoProvider implements iProvider
{
    var $lang;
    var $trans;

    public function __construct( $lang  , $config = array())
    {
        $this->lang = $lang;
        $this->trans = array();

        $lang = strtolower(str_replace('_' , '-' , $lang ));

        if(isset($config['module'])){

            $langFile = realpath(__DIR__ . '/../../../../../' . $config['module'] . '/setup/phpgw_'. $lang . '.lang');

            if ( file_exists( $langFile ) ){
                $fp = fopen( $langFile , 'r' );
                while ($data = fgets($fp,8000)){
                    $data = mb_convert_encoding( $data , 'UTF-8' , 'ISO-8859-1' );
                    list($messageId,,,$content) = explode("\t",$data);
                    $this->trans[preg_replace( "/\%[0-9]/" , '%s', trim($messageId)) ] = preg_replace( array("/\r?\n/", "/\%[0-9]/"),array('','%s'),$content);
                }
                fclose($fp);
            }
        }
    }

    public function trans( $key )
    {
        return  isset($this->trans[$key]) ?  $this->trans[$key] : false;
    }

}