<?php
$GLOBALS['BASE'] = dirname(__FILE__).'/../../';
//$GLOBALS['BASE']           =   PHPGW_SERVER_ROOT;  // '/var/www/expresso';
$GLOBALS['dirtemp']        =   $GLOBALS['BASE'] . '/security/temp';
$GLOBALS['CAs']            =   $GLOBALS['BASE'] . '/security/cas/todos.cer';
# Informar $GLOBALS['CRLs'] = '' para o Expresso nao verificar se certificado esta revogado.
$GLOBALS['CRLs']           =   $GLOBALS['BASE'] . '/security/crls/';     // Tem de ter a barra no final
$GLOBALS['arquivos_crls']  =   $GLOBALS['BASE'] . '/security/crl_admin/crl_admin.conf';
$GLOBALS['log']            =   $GLOBALS['BASE'] . '/logs/arquivo_crls.log';
$GLOBALS['lenMax']         =   1048576; // 1MBytes = tamanho maximo do arquivo(em bytes) de log antes do rotate.....
$GLOBALS['bkpNum']         =   10;  // Número de arquivos de log mantidos pelo rotate....
?>
