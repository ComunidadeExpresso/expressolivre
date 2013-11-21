<?php
    $GLOBALS['phpgw_info'] = array();
    $GLOBALS['phpgw_info']['flags']['currentapp'] = 'admin';
    include('../header.inc.php');
    require_once('classes/CertificadoB.php');

    if($GLOBALS['CRLs'] == '')
        {
            echo '<br/><br/><br/><div align="center"><h3><b>' . lang('CRLs use is disable') . '.<b><h3><br/><br/>';
            echo '<a href="security_admin.php" style="text-decoration:none"><input type="button" value="' . lang('Back') . '"/></a></div>';
            exit();
        }

    if($_POST)
        {
            if($_POST['atualizar'])
                {
                    $saida = array();
                    $aa = pathinfo($GLOBALS['arquivos_crls']);
                    $a = $aa['dirname'] . '/crl_admin.py';
                    $w = exec($a,$saida);
                }
        }

    $path1 = $GLOBALS['arquivos_crls'];
    $arq = file_get_contents($path1);
    echo '<script type="text/javascript" src="certificados.js"  ></script>';
    echo '<div style="padding-left:90px" >';
    echo '<a href="../security/security_admin.php" style="text-decoration:none"><input type="button" value="' . lang('Back') . '"/></a>';
    echo '<input type="hidden" name="adicionar" value="adicionar" />';
    $Linhas = explode(chr(0x0A),$arq);
    if($path1)
        {

            echo '<h2  style="color: #000066">' . lang('RCLs configurated') . ':</h2>';
            echo '<div id="xdiv2" style="border: #000000 1px solid; overflow: auto; width: 870px; height: 190px; white-space: pre;  padding: 3px; " >';
            echo '<table border ="1" style="margin-top: 8px; width: 770px">';
            echo '<th nowrap align="left" style=" padding: 5px">' . lang('RCLs get in') . ':</th><th nowrap align="left" style=" padding: 5px">' . lang('Where save RCLs') . ':</th>';
            foreach($Linhas as $linhaz)
                {
                    $linha = trim($linhaz);
                    if($linha[0] != '#' && $linha != '')
                        {
                            $n = explode('/',$linha);
                            $f = $GLOBALS['CRLs'] . $n[count($n)-1];
                            echo '<tr><td nowrap valign="top" style=" padding: 5px">' . $linha . '</td><td nowrap style=" padding: 5px">';
                            echo $f;
                            if(!is_file($f))
                                {
                                    echo '<p style="margin-bottom: 5px"><b  style="color: #FF0000">' . lang('File not found') . '.</b></p>';
                                }
                            else
                                {
                                    $data = file_get_contents($f);
                                    $dados = Crl_parseASN($data);
                                    echo '<br/><br/>' . lang ('Issuer') . ': ';
                                    $aux = $dados[1][0][1][2][1]; // pega dados do emissor.
                                    $aux = $aux[count($aux)-1];  // ultimo item he o do CN.....
                                    echo $aux[1][1][1][1];
                                    echo '<br/>' . lang('Num. Certificates') . ': ';
                                    $num = 0;
                                    if(count($dados[1][0][1]) > 6)        // qtd de itens esperado he 7. o 6 contem os certificados revogados.
                                        {
                                            $num = count($dados[1][0][1][5][1]);  // pega o numero de certificados revogados na LCR.
                                        }
                                    echo $num;
                                    echo '<br/>' . lang('Num. RCL') . ': ';
                                    $oid_Num_crl = recupera_dados_oid($data,'2.5.29.20');  // oid que informa o numero de geracao da LCR.
                                    $num = $oid_Num_crl[0][1][1][1];
                                    if($num)
                                        {
                                            echo $num;
                                        }
                                    else
                                        {
                                            echo '0';
                                        }
                                    echo '<br/>';
                                    $di = data_hora($dados[1][0][1][3][1]);             // data, hora em que foi gerada a LCR.
                                    $df = data_hora($dados[1][0][1][4][1]);		   // data, hora em que expira a LCR.
                                    if(gmdate("YmdHis") < $di)
                                        {
                                            $cor = 'style="color: #FF0000"';
                                        }
                                    else
                                        {
                                            $cor = 'style="color: #000066"';
                                        }
                                echo '<p><b  ' . $cor . '>' . lang('Create in') . ' : </b>' . substr($di,0,4) . '/' . substr($di,4,2) . '/' . substr($di,6,2) . '  -  ' . substr($di,8,2) . ':' . substr($di,10,2) . ':' . substr($di,12,2) . ' GMT</p>';
                                    if(gmdate("YmdHis") > $df)
                                        {
                                            $cor = 'style="color: #FF0000" >' . lang('Expired on') . ': ';
                                        }
                                    else
                                        {
                                            $cor = 'style="color: #000066" >' . lang('Expire on') . ': ';
                                        }
                                    echo '<p><b  ' . $cor . ' </b>' . substr($df,0,4) . '/' . substr($df,4,2) . '/' . substr($df,6,2) . '  -  ' . substr($df,8,2) . ':' . substr($df,10,2) . ':' . substr($df,12,2) . ' GMT</p>';
                                }
                            echo  '</td></tr>';
                        }
                }
            echo '</table>';
            echo '</div>';
            echo '<form id="frm2" enctype="multipart/form-data" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
            echo '<input type="hidden" name="atualizar" value="atualizar" />';
            echo '<br/>';
            echo '<input type="button" name="atualiz" value="' . lang('Execute RCLs update') . '" onclick="javascript:Salvar_arq(\'frm2\',\'' . lang('Confirm RCLs update') . ' ?\')"/>';
            echo '<br/>';
            echo '</form>';
        }

    $path2 = $GLOBALS['log'];
    if($path2)
        {
            echo '<h2  style="color: #000066">' . lang('Log of RCLs update') . ':</h2>';
            echo '<div style="border: #000000 1px solid; overflow: auto; width: 770px; height:180px; white-space: pre; padding: 5px" >';
            echo '<pre>';
            if(is_file($path2))
                {
                    $saida = array();
                    $ret = exec('cat ' . $path2 . ' | grep -a --text ' . date('Y-m-d') ,$saida);
                    //$ret = exec('cat ' . $path2);
                    foreach($saida as $linha)
                        {
                            echo str_replace(chr(0x00),'',$linha) . chr(0x0A);
                        }
                }
            else
                {
                    //$ret = exec('cat ' . $path2 ,$saida);
                    echo '<p style="margin-bottom: 5px"><b  style="color: #FF0000">' . lang('File') . ' ' . $path2 . ' ' . lang('not found') . '</b></p>';
                }
            echo '</pre>';
            echo '</div><br/><br/>';
            echo '<a href="../security/security_admin.php" style="text-decoration:none"><input type="button" value="' . lang('Back') . '"/></a>';
            echo '<div>';
        }
?>
