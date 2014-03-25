<?php

$fileDuckConfig = array();

//Modelo de cache utilizado  NONE | ETag | LastModified | Expires | MaxAge
$fileDuckConfig['cacheModel'] = 'MaxAge';

// Caso utilize cache Expire. 43200 Minutos = 30 Dias
$fileDuckConfig['cacheExpireTime'] = '43200';

// Caso utilize cache MaxAge. 2880 Minutos = 2 Dia
$fileDuckConfig['cacheMaxAgeTime'] = '2880';

//Pasta utilizada para cache do fileDuck
$fileDuckConfig['cacheFolder'] = '/tmp/fileDuck';

// Arquivo para de log Debug
$fileDuckConfig['debugFile'] = '/tmp/fileDuck/debug.log';

$fileDuckConfig['debug'] = false;

// Linguagem padrão
$fileDuckConfig['lang'] = 'pt_BR';

// Tokens de inicio  , fim , variavel
$fileDuckConfig['tokens'] = array('_[[' , ']]' , '$' );

// Operador incio , fim;  Usado para envolver variaveis
$fileDuckConfig['wrapVarTokens'] = array( '+' , '+' );

// YUICompressor
$fileDuckConfig['YUICompressor'] = false;

//Traduções obrigatoriamente nescessitam estar envolvidas em aspas
$fileDuckConfig['requireQuotes'] = false;

// Provedor de internacionalização
$fileDuckConfig['provider'] = 'expresso';

// Ambiente
$fileDuckConfig['environment'] = 'dev'; // ( dev | prod )
// Caso ambiente seja definido como prod o fileDuck ira fazer cache dos arquivos compilados e não verificara alterações nos arquivos originais.
// Alterações so serão refletidas após a limpeza do cache ( apagando arquivos da pasta configurada em cacheFolder )