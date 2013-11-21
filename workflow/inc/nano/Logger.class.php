<?php
/**
 * NanoController
 *
 * @package NanoAjax
 *
 */
class DummyLogger
{
    public function add( $entry ) {}

    public function rawAdd( $entry ) {}
}

class Logger
{
    public function add( $entry )
    {
        $this->rawAdd( $entry."<br/>\n" );
    }

    public function rawAdd( $entry )
    {
        echo $entry;
    }
}

?>