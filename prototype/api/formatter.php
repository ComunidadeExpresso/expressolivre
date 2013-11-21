<?php

interface Formatter
{
    public function format( $data , $params = false);

    public function parse( $data , $params = false);
}