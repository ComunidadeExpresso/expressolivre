<?php
        $GLOBALS['phpgw_info'] = array();
        $GLOBALS['phpgw_info']['flags'] = array('noheader'   => True,'nonavbar'   => True,'currentapp' => 'admin');
        include('../header.inc.php');
	require_once('classes/CertificadoB.php');
        require_once('security-lib.php');
	// pega caminho completo do arquivo de CAS..
        if( $_POST['path3'])
        {
            $path3 = $_POST['path3'];
        }
        else
        {
            $path3 =$GLOBALS['CAs'];
        }
        $path3 =$GLOBALS['CAs'];
        $arquivo = basename($path3);
        // se não pude acessar o arquivo com certificados retornar .....
	if(!is_file($path3)) exit();
        $dir = dirname($path3);
        if($_POST['arquivo'] & $_POST['caminho'] )
        {
            if(substr($_POST['arquivo'],0,25) != '--Selecione um arquivo co')
            {
                $path3 = $_POST['caminho'] . '/' . $_POST['arquivo'];
                $dir = $_POST['caminho'];
            }
        }
	if($path3)
            {
                $todos_certificados =  ler_certificados_CAS($path3);
                $ret = Gerar_Estruturas_Certificados($todos_certificados);
                $aux_emissores= $ret[0];
                $tab_certs = $ret[1];
                $NOVO = array();
                $AUX3 = array();
                $emissores = array();
                foreach($aux_emissores as $kchave1 => $emis1)
                    {
                        foreach($emis1 as $Kchave2 => $emis2)
                            {
                                foreach($emis2 as $Kchave3 => $emis3)
                                    {
                                        $emissores[$Kchave2][$Kchave3] = '9';
                                    }
                            }
                    }
                // O array $NOVO vai conter a cadeia dos certificados de CAs ....
                foreach($emissores as $K => $V)
                    {
                        if($AUX3[$K] != '0')
                            {
                                foreach($V as $K1 => $V1)
                                    {
                                        if($emissores[$K1])
                                            {
                                                $NOVO[$K][$K1] = $emissores[$K1];
                                                $AUX3[$K1] = '0';
                                            }
                                        else
                                            {
                                                $NOVO[$K][$K1] = '0';
                                            }
                                    }
                            }
                    }
                // Valores auxiliares para fazer a identacao .....
                $prefixo0 = '&nbsp;&nbsp;' . '|';
                $prefixo1 =  '&nbsp;&nbsp;' .  '|' .  '____' ;
                $prefixo2 =  '&nbsp;&nbsp;' . '|' .  '&nbsp;&nbsp;' . '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' . '|' ;
                $prefixo2A =  '&nbsp;&nbsp;' . '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' . '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' . '|' ;
                $prefixo3 =  '&nbsp;&nbsp;' . '|' .  '&nbsp;&nbsp;' . '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' . '|'  . '____' ;
                $prefixo3A =  '&nbsp;&nbsp;' .  '&nbsp;&nbsp;'  .  '&nbsp;&nbsp;' . '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' .  '&nbsp;&nbsp;' . '|'  . '____' ;
                $tudo = '';
                foreach($NOVO as $K => $V)
                    {
                        $lnk = '<b><a href="javascript:Um_Certificado(\''. $tab_certs[$K]['item'] . ' - ' . $K .'\',\'' .  $arquivo . '\')" style="text-decoration: none" >' . $K . '</a></b>';
                        if($tab_certs[$K]['fim_validade'])
                            {
                                $msg =  $tab_certs[$K]['fim_validade'];
                            }
                        else
                            {
                                $msg =  '<font color="FF0000" size="4"><b>' . lang('Certificate not in chain') . '.</b></font> ';
                                $lnk = '<b>' . $K . '</b>';
                            }
                        $tudo .= '<br/><font color="0000FF" size="4">' . $lnk . '</font>  ' . $msg . '<br/>';
                        if(is_array($V))
                            {
                                $num = count($V);
                                $item = 0;
                                foreach($V as $K1 => $V1)
                                    {
                                        $item = $item + 1;
                                        $tudo .=  $prefixo0 . '<br/>';
                                        if($tab_certs[$K1]['fim_validade'])
                                            {
                                                $msg =  $tab_certs[$K1]['fim_validade'];
                                            }
                                        else
                                            {
                                                $msg =  '<font color="FF0000" ><b>' . lang('Certificate not in chain') . '.</b></font> ';
                                            }
                                        $tudo .=  $prefixo1 .  '<font color="#000000" ><a href="javascript:Um_Certificado(\''. $tab_certs[$K1]['item']  . ' - ' . $K1 .'\',\'' .  $arquivo . '\')" style="text-decoration: none" >' . $K1 . '</a></font> ' .  '  ' . $msg . '<br/>';
                                        if(is_array($V1))
                                            {
                                                foreach($V1 as $K2 => $V2)
                                                    {
                                                        if($tab_certs[$K1]['fim_validade'])
                                                            {
                                                                $msg =  $tab_certs[$K1]['fim_validade'];
                                                            }
                                                        else
                                                            {
                                                                $msg =  '<font color="FF0000" ><b>' . lang('Certificate not in chain') . '.</b></font> ';
                                                            }
                                                        if($num>$item)
                                                            {
                                                                $tudo .=   $prefixo2 . '<br/>';
                                                                $tudo .= $prefixo3 . '<font color="#000000" ><a href="javascript:Um_Certificado(\''. $tab_certs[$K2]['item']  . ' - ' . $K2 .'\',\'' .  $arquivo . '\')" style="text-decoration: none" >' . $K2 . '</a></font> ' .  '  ' . $msg . '<br/>';
                                                            }
                                                        else
                                                            {
                                                                $tudo .=   $prefixo2A . '<br/>';
                                                                $tudo .=  $prefixo3A .  '<font color="#000000" ><a href="javascript:Um_Certificado(\''. $tab_certs[$K2]['item']  . ' - ' . $K2 .'\',\'' .  $arquivo . '\')" style="text-decoration: none" >' . $K2 . '</a></font> ' .  '  ' . $msg . '<br/>';
                                                            }
                                                    }
                                            }
                                    }
                            }
                    }
                Header('Content-type: application/xml; charset=utf-8');
                $tudo = "<certificados>" . base64_encode($tudo) . '</certificados>';
                echo $tudo;
            }
?>
