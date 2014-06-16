<?php

require_once __DIR__ . '/../src/FileDuck.php';

//Sobrescrevendo do arquivo config.php em tempo de execução
$config = array();
$config['compress'] = false ;
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR'; // Definindo linguagem via QueryString

$fileDuck = new FileDuck( $config );

$fileDuck->add( __DIR__ .'/js/example.js' );
$fileDuck->add( __DIR__ .'/js/example2.js' );

//( text/javascript | text/css  | text/plain | etc..)
$fileDuck->renderFile( 'text/javascript' ); //Renderizando o a saida com MimeType especificado.

