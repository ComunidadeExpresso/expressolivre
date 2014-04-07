<?php

require_once __DIR__ . '/../iProvider.php';

class arrayProvider implements iProvider
{
    var $lang;
    var $trans;

    public function __construct( $lang  , $parameters = array())
    {
        $this->lang = $lang;
        $trans = array();
        include_once( __DIR__ . '/data/'.$lang.'.php');
        $this->trans =  $trans;
    }

    public function trans( $key )
    {
        return  isset($this->trans[$key]) ?  $this->trans[$key] : false;
    }

}
