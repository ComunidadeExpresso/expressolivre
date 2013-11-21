<?php
$GLOBALS['phpgw_info']['flags'] = array(
		'disable_Template_class' => True,
		'login'                  => True,
		'currentapp'             => 'login',
		'noheader'               => True
	);
if(file_exists('../header.inc.php'))
    {
        include('../header.inc.php');
    }
else
    {
        echo '1'.chr(0x0D).chr(0x0A).lang('Error. header.inc.php not found');
        exit();
    }
if(!$_POST['certificado'])
    {
        echo '2'.chr(0x0D).chr(0x0A).lang('Fail to get certificate');
        exit();
    }
require_once('classes/CertificadoB.php');
require_once('classes/Verifica_Certificado.php');
include('classes/Verifica_Certificado_conf.php');
$cert = troca_espaco_por_mais(str_replace(chr(0x0D).chr(0x0A),chr(0x0A),str_replace(chr(0x0A).chr(0x0A),chr(0x0A),$_POST['certificado'])));
$c = new certificadoB();
$c->certificado($cert);
if (!$c->apresentado)
    {
       echo '3'.chr(0x0D).chr(0x0A).lang('Fail to get certificate');
       exit();
    }
$b = new Verifica_Certificado($c->dados,$cert);
if(!$b->status)
    {
       $msg = '4'.chr(0x0D).chr(0x0A).$b->msgerro;
       foreach($b->erros_ssl  as $linha)
           {
                $msg .= "\n" . $linha;
           }
       echo $msg;
       exit();
    }
if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
        (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
	(!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
    {
        $ds = $GLOBALS['phpgw']->common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
        $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
        $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
    }
else
    {
        $ds = $GLOBALS['phpgw']->common->ldapConnect();
    }
if (!$ds)
     {
	echo '5'.chr(0x0D).chr(0x0A).lang('Failure when get user data to login');
	exit();
     }
$cert_atrib_cpf = isset($GLOBALS['phpgw_info']['server']['certificado_atributo_cpf'])&&$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']!=''?$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']:"uid";
$filtro = $cert_atrib_cpf .'='. $c->dados['2.16.76.1.3.1']['CPF'];
$atributos = array();
$atributos[] = "usercertificate";
$atributos[] = "phpgwaccountstatus";
$atributos[] = "cryptpassword";
$atributos[] = "uid";
$sr=ldap_search($ds, $GLOBALS['phpgw_info']['server']['ldap_context'],$filtro,$atributos);
$info = ldap_get_entries($ds, $sr);
if($info["count"]!=1)
{
    echo '6'.chr(0x0D).chr(0x0A).lang('Invalid data from users directory');
    ldap_close($ds);
    exit();
}
if($info[0]['phpgwaccountstatus'][0]!='A')
    {
	echo '7'.chr(0x0D).chr(0x0A).lang('User account is inactive in Expresso');
	ldap_close($ds);
	exit();
    }
if($info[0]["cryptpassword"][0] && $info[0]["usercertificate"][0] && $cert == $info[0]["usercertificate"][0] )
    {
	echo '0'.chr(0x0D).chr(0x0A).$info[0]["uid"][0].chr(0x0D).chr(0x0A).$info[0]["cryptpassword"][0];
    }
else
    {
        echo '8'.chr(0x0D).chr(0x0A).lang('The current certificate not registered to login');
    }
ldap_close($ds);
?>