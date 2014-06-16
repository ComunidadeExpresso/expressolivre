<?php


interface iProvider
{
    public function __construct( $lang  , $parameters);
    public function trans( $key );
}
