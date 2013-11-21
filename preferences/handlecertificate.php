<?php
$GLOBALS['phpgw_info']['flags'] = array(
        'noheader'   => True,
        'nonavbar'   => True,
        'currentapp' => 'preferences'
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
if($_POST['certificado'])
    {
        $GLOBALS['phpgw_info']['flags']['app_header'] = lang('Digital Certificate Registration');
        $GLOBALS['phpgw']->common->phpgw_header();
        echo parse_navbar();
        echo '<form id="answerForm" name="answerForm" method="POST" action="index.php" >';
        echo '<BR/><BR/><BR/>';
        
        require_once('../security/classes/CertificadoB.php');
        require_once('../security/classes/Verifica_Certificado.php');
        include('../security/classes/Verifica_Certificado_conf.php');
        $cert = troca_espaco_por_mais(str_replace(chr(0x0D).chr(0x0A),chr(0x0A),str_replace(chr(0x0A).chr(0x0A),chr(0x0A),$_POST['certificado'])));
        $c = new certificadoB();
        $c->certificado($cert);
        if (!$c->apresentado)
            {
                echo '<div align="center"><h2>'.lang('Fail to get certificate').'</h2>';
                exit();
            }
        $b = new Verifica_Certificado($c->dados,$cert);
        // Testa se Certificado OK.
        if(!$b->status)
            {
               $msg = '3'.chr(0x0D).chr(0x0A).$b->msgerro;
               foreach($b->erros_ssl  as $linha)
                   {
                        $msg .= "\n" . $linha;
                   }
                   echo '<div align="center"><h2>'.$msg.'</h2>';
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
                 echo '<div align="center"><h2>'.lang('Failure when get user data to login').'</h2>';
                exit();
             }
        $cert_atrib_cpf = isset($GLOBALS['phpgw_info']['server']['certificado_atributo_cpf'])&&$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']!=''?$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf']:"uid";
        // CPF he valor obrigatório no certificado ICP-BRASIL.
        $filtro = $cert_atrib_cpf .'='. $c->dados['2.16.76.1.3.1']['CPF'];
        $atributos = array();
        if(isset($GLOBALS['phpgw_info']['server']['atributoexpiracao']) && $GLOBALS['phpgw_info']['server']['atributoexpiracao'])
            {
                $atributos[] = $GLOBALS['phpgw_info']['server']['atributoexpiracao'];
            }
        else
            {
                $atributos[] = 'phpgwlastpasswdchange';
            }
         $atributos[] = "userCertificate";
         $atributos[] = "uid";
        $sr=ldap_search($ds, $GLOBALS['phpgw_info']['server']['ldap_context'],$filtro,$atributos);
        // Pega resultado ....
        $info = ldap_get_entries($ds, $sr);
        // Tem de achar só uma entrada.....ao menos uma....
        if($info["count"]!=1)
            {
                echo '<div align="center"><h2>'.lang('Invalid data from users directory').'('.$cert_atrib_cpf.' = ' . $c->dados['2.16.76.1.3.1']['CPF'] . ')'.'</h2>';
                ldap_close($ds);
                exit();
            }
            if($info[0]["uid"][0] != $GLOBALS['phpgw_info']['user']['userid'])
            {
                echo '<div align="center"><h2>'.lang('Invalid data from users directory').'('.$cert_atrib_cpf.' = ' . $c->dados['2.16.76.1.3.1']['CPF'] . ' - ' . $info[0]["uid"][0] . ' - ' . $GLOBALS['phpgw_info']['user']['userid'] . ')'.'</h2>';
                ldap_close($ds);
                exit();
            }
            if($info[0]["userCertificate"][0] && $cert == $info[0]["userCertificate"][0] )
            {
                //echo '0'.chr(0x0D).chr(0x0A).$info[0]["uid"][0].chr(0x0D).chr(0x0A).$info[0]["cryptpassword"][0];
                echo '<div align="center"><h2>'.lang('Certificate already registered').'</h2>';
                ldap_close($ds);
                exit();
            }
        $user_info = array();
        $aux1 = $info[0]["dn"];
        $user_info['userCertificate'] = $cert;
        if(isset($GLOBALS['phpgw_info']['server']['atributoexpiracao']) && $GLOBALS['phpgw_info']['server']['atributoexpiracao'])
            {
                if(substr($info[0][$GLOBALS['phpgw_info']['server']['atributoexpiracao']][0],-1,1)=="Z")
                    {
                        $user_info[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = '19800101000000Z';
                    }
                else
                    {
                        $user_info[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = '0';
                    }
            }
        else
            {
                $user_info['phpgwlastpasswdchange'] = '0';
            }
        if(!ldap_modify($ds,$aux1,$user_info))
            {
                echo '<div align="center"><h2>'.lang('Error in Certificate registration'). '  -  ' . $aux1.'</h2>';
            }
        else
            {
                echo '<div align="center"><h2>'.lang('To conclude your Certificate registration change your password').'</h2>';
            }

            echo '<h2><img style="border:0px;margin:31px 0px 58px 0px;" src="../phpgwapi/templates/default/images/acao.gif" /></h2>';
            echo '<input type="submit" name="ok" value="' . lang('ok') . '" ></div></form>';
            $GLOBALS['phpgw']->common->phpgw_footer();
        ldap_close($ds);
        exit();
    }
else
    {
        $GLOBALS['phpgw_info']['flags']['app_header'] = lang('Digital Certificate Registration');
        $GLOBALS['phpgw']->common->phpgw_header();
        echo parse_navbar();
        if ($GLOBALS['phpgw_info']['server']['certificado']==1)
            {
                    $var_tokens = '';
                    for($ii = 1; $ii < 11; ++$ii)
                    {
                            if($GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'])
                                    $var_tokens .= $GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'] . ',';
                    }
                    if(!$var_tokens)
                    {
                            $var_tokens = 'ePass2000Lx;/usr/lib/libepsng_p11.so,ePass2000Win;c:/windows/system32/ngp11v211.dll';
                    }
                    $param1 = "
                                                                                            '<param name=\"token\" value=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\"> ' +
                                                                                       ";
                    $param2 = "
                                                                                            'token=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\" ' +
                                                                                       ";
                    $cod_applet =
        /*    // com debug ativado
                '<script type="text/javascript">
                                            if (navigator.userAgent.match(\'MSIE\')){
                                                    document.write(\'<object style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" \' +
                                                    \'classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"> \' +
                                                    \'<param name="type" value="application/x-java-applet;version=1.5"> \' +
                                                    \'<param name="code" value="LoginApplet.class"> \' +
                                                    \'<param name="locale" value="' . $lang . '"> \' +
                                                    \'<param name="mayscript" value="true"> \' + '
                                                    . $param1
                                                    . ' \'<param name="archive" value="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar"> \' +
                            \'<param name="debug" value="true"> \' +
                                                    \'</object>\');
                                            }
                                            else {
                                                    document.write(\'<embed style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" code="LoginApplet.class" locale="' . $lang . '"\' +
                                                    \'archive="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar" \' + '
                                                    . $param2
                                                    . ' \'type="application/x-java-applet;version=1.5" debug= "true" mayscript > \' +
                                                    \'<noembed> \' +
                                                    \'No Java Support. \' +
                                                    \'</noembed> \' +
                                                    \'</embed> \');
                                            }
                                    </script>';
        */
                // sem debug ativado
                '<script type="text/javascript">
                                            if (navigator.userAgent.match(\'MSIE\')){
                                                    document.write(\'<object style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" \' +
                                                    \'classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"> \' +
                                                    \'<param name="type" value="application/x-java-applet;version=1.5"> \' +
                                                    \'<param name="codebase" value="/security/">\' +
                                                    \'<param name="code" value="LoginApplet.class"> \' +
                                                    \'<param name="locale" value="' . $lang . '"> \' +
                                                    \'<param name="mayscript" value="true"> \' + '
                                                    . $param1
                                                    . ' \'<param name="archive" value="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar"> \' +
                                                    \'</object>\');
                                            }
                                            else {
                                                    document.write(\'<embed style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" codebase="/security/" code="LoginApplet.class" locale="' . $lang . '"\' +
                                                    \'archive="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar" \' + '
                                                    . $param2
                                                    . ' \'type="application/x-java-applet;version=1.5" mayscript > \' +
                                                    \'<noembed> \' +
                                                    \'No Java Support. \' +
                                                    \'</noembed> \' +
                                                    \'</embed> \');
                                            }
                                    </script>';
                  echo $cod_applet;
                  echo '<form id="certificateForm" name="certificateForm" method="POST" action="handlecertificate.php" >';
                  echo '<BR/><BR/><BR/>';
                  echo '<div align="center"><h2>'.lang('Getting your Certificate').'</h2>';
                  echo '<h2><img style="border:0px;margin:31px 0px 58px 0px;" src="../phpgwapi/templates/default/images/acao.gif" /></h2>';
                  echo '<input type="hidden" name="certificado" value="" />';
                  echo '<input type="submit" name="cancel" value="' . lang('cancel') . '" ></div></form>';
                  $GLOBALS['phpgw']->common->phpgw_footer();
        }
    }
?>
