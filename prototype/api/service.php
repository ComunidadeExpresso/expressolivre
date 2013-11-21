<?php


// interface Service
// {
// //---------------
// 
//     public function load   ( $concept, $parents = false, $id = false );
// 
// //---------------
// 
//     public function search ( $justthese = false, $params = false, $criteria = false );
// 
// //---------------
// 
//     public function delete ( $justthese = false, $params = false, $criteria = false );
// 
// //---------------
//    
//     public function update ( $data,              $params = false, $criteria = false );
// 
// //---------------
// 
//     public function create ( $data,              $params = false );
// 
// //---------------
// 
//     public function connect( $config );
// 
//     public function close();
// 
//     public function setup();
// 
//     public function teardown();
// }


interface Service
{
//---------------

    public function find     ( $uri, $justthese = false, $criteria = false );

    public function read     ( $uri, $justthese = false/*, $criteria = false*/ );

//---------------

    public function deleteAll( $uri, $justthese = false, $criteria = false ); 

    public function delete   ( $uri, $justthese = false/*, $criteria = false*/ );// avaliar

//---------------

    public function replace  ( $uri, $data, $criteria = false );

    public function update   ( $uri, $data/*, $criteria = false*/ );

//---------------

    public function create   ( $uri, $data/*, $criteria = false*/ );

//---------------

    public function open     ( $config );

    public function close    ();


    public function begin     ( $uri );

    public function commit    ( $uri );

    public function rollback  ( $uri );


    public function setup     ();

    public function teardown  ();
}