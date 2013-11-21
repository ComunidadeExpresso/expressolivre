<?php

return array(

// -----------------------------------------------------------------------------
// {unsigned} TINYINT (integer) min 0 to 255
'tinyint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' =>   0,
                                                                 'max' => 255 ) ) ) ),

// -----------------------------------------------------------------------------
// {signed} TINYINT (integer) min -128 to 127
'signedtinyint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' => -128,
                                                                 'max' =>  127 ) ) ) ),

// -----------------------------------------------------------------------------
// {unsigned} SMALLINT (integer) min 0 to 65535
'smallint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' =>     0,
                                                                 'max' => 65535 ) ) ) ),

// -----------------------------------------------------------------------------
// {signed} SMALLINT (integer) min -32768 to 32767
'signedsmallint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' => -32768,
                                                                 'max' =>  32767 ) ) ) ),

// -----------------------------------------------------------------------------
// {unsigned} MEDIUMINT (integer) min 0 to 16777215
'mediumint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' =>        0,
                                                                 'max' => 16777215 ) ) ) ),

// -----------------------------------------------------------------------------
// {signed} MEDIUMINT (integer) min -8388608 to 8388607
'signedmediumint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' => -8388608,
                                                                 'max' =>  8388607 ) ) ) ),

// -----------------------------------------------------------------------------
// {unsigned} INTEGER (integer) min 0 to 4294967295
'integer' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' =>          0,
                                                                 'max' => 4294967295 ) ) ) ),

// -----------------------------------------------------------------------------
// {signed} INTEGER (integer) min -2147483648 to 2147483647
'signedinteger' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' => -2147483648,
                                                                 'max' =>  2147483647 ) ) ) ),

// -----------------------------------------------------------------------------
// {unsigned} BIGINT (integer) min 0 to 18446744073709551615
'bigint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' =>                    0,
                                                                 'max' => 18446744073709551615 ) ) ) ),

// -----------------------------------------------------------------------------
// {signed} BIGINT (integer) min -9223372036854775808 to 9223372036854775807
'signedbigint' =>
            array( 'type'    => 'integer',
                   'methods' => array( array( 'name'   => '_limitIntegerValue',
                                              'limits' => array( 'min' => -9223372036854775808,
                                                                 'max' =>  9223372036854775807 ) ) ) ),

// -----------------------------------------------------------------------------
// DEFAULT alphanumeric (string)
'alphanumeric' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeDefaultAlphaNumericString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 30 ) ) ) ),

// -----------------------------------------------------------------------------
// PARANOID alphanumeric (string)
'paranoid_alphanumeric' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeParanoidAlphaNumericString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 30 ) ) ) ),

// -----------------------------------------------------------------------------
// DEFAULT german alphanumeric (string)
'german_alphanumeric' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeDefaultGermanAlphaNumericString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 30 ) ) ) ),

// -----------------------------------------------------------------------------
// PARANOID german alphanumeric (string)
'paranoid_germanalphanumeric' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeParanoidGermanAlphaNumericString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 30 ) ) ) ),

// -----------------------------------------------------------------------------
'sql_query' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeDefaultSqlString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 100 ) ) ) ),

// -----------------------------------------------------------------------------
'paranoid_sql_query' =>
            array( 'type'    => 'string',
                   'methods' => array( array( 'name'   => '_sanitizeParanoidSqlString',
                                              'limits' => array( 'min' =>  0,
                                                                 'max' => 100 ) ) ) )

);

?>