<?php
	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'admin';
	include('../header.inc.php');
	require_once('classes/CertificadoB.php');
        require_once('security-lib.php');
        $msgs_alertas = '';
        $path1 = $GLOBALS['arquivos_crls'];
        $path2 = $GLOBALS['log'];
        $path3 = $GLOBALS['CAs'];
        if(!is_dir(dirname($path3)))
        {
            echo lang('Erro.') . ' ' . lang('Configuration file not found in server.');
            exit();
        }
        $dir = dirname($path3);
        /*
        if($_POST['arquivo'] & $_POST['caminho'] )
        {
            if(substr($_POST['arquivo'],0,25) != '--Selecione um arquivo co')
            {
                $path3 = $_POST['arquivo'];
                $dir = $_POST['caminho'];
            }
        }
        */
        
        if ($_FILES['file'])
            {
                if($_FILES['file']['tmp_name'])
                    {
                        $saida = array();
                        $arquivo = $_FILES['file']['tmp_name'];
                        $w = file_get_contents($arquivo);
                        if(strpos($w,'-----BEGIN CERTIFICATE-----') === false)
                            {
                                $w = '';
                                if(count(explode(chr(0x0A),$w)) < 7 )
                                    {
                                        // Convertendo DER para PEM (Entrada deve ser um certificado x509).
                                        $w = shell_exec('openssl x509 -inform DER -in ' . $arquivo . ' 2>&1');
                                    }
                                if(count(explode(chr(0x0A),$w)) < 7 )
                                    {
                                        // Convertendo PKCS7 para PEM (Entrada deve ser PEM iniciando com -----BEGIN PKCS7----- ).
                                        $w = shell_exec('openssl pkcs7 -inform PEM -outform PEM -print_certs -in ' . $arquivo . ' 2>&1');
                                    }
                                if(count(explode(chr(0x0A),$w)) < 7 )
                                    {
                                        // Convertendo p7b para PEM ( Entrada deve ser DER).
                                        $w =  shell_exec('openssl pkcs7 -inform DER -outform PEM -print_certs -in ' . $arquivo . ' 2>&1');
                                    }
                                if(count(explode(chr(0x0A),$w)) < 7 )
                                    {
                                        $msgs_alertas .= lang('File') . ' '. $_FILES['file']['name'] . lang('not processed. Invalid format') . '.<br/>';
                                    }
                            }
                        if(!$msgs_alertas)
                            {
                                $conteudo = '';
                                $saida = explode(chr(0x0A),$w);
                                foreach ($saida as $linha)
                                    {
                                        if($linha != '')
                                            {
                                                if(substr($linha,0,7) != 'subject' && substr($linha,0,6) != 'issuer')
                                                    {
                                                        $conteudo .= $linha . chr(0x0A);
                                                    }
                                            }
                                    }
                                $todos = ler_certificados_CAS($conteudo,true);
                                $conteudo = '';
                                foreach ($todos as $cert)
                                    {
                                        // Trata sho certificados de CA?
                                        $a = new certificadoB();
                                        $a->certificado($cert);
                                        if($a->dados['CA'])
                                            {
                                                $conteudo .= chr(0x0D) . chr(0x0A) . $cert;
                                                $msgs_alertas .= lang('Certificate added to') . ' ' . $a->dados['NOME'] . ' .<br/>';
                                            }
                                    }
                                if($conteudo)
                                    {
                                        $novo_nome = gera_nome_arquivo_bkp($path3);
                                        if($novo_nome != $path3)
                                            {
                                                $ret = salva_arquivo_bkp($path3,$novo_nome);
                                                if($ret == 0)
                                                    {
                                                        file_put_contents($path3,$conteudo,FILE_APPEND);
                                                        $msgs_alertas .= lang('File updated and save') . '.';
                                                    }
                                                else
                                                    {
                                                        $msgs_alertas .= lang('Failure on save file (CD04). The requested operation is not concluded') . '.<br/>';
                                                    }
                                            }
                                        else
                                            {
                                                $msgs_alertas .= lang('Failure on save file (CD03). The requested operation is not concluded') . '.<br/>';
                                            }
                                    }
                                 else
                                     {
                                        $msgs_alertas .= lang('ACs certificates not found') . '.';
                                     }
                            }
                    }
            }
        
        echo '<script type="text/javascript" src="certificados.js"></script>';
        echo '<div style="padding-left:90px" >';
        echo '<form id="frm3" enctype="multipart/form-data" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
        echo '<a href="../security/security_admin.php" style="text-decoration:none"><input type="button" value="' . lang('Back') . '"/></a>';
        $aux99 = explode('/',$path3);
        $path3 = $aux99[count($aux99)-1];
        echo '<br/><br/>';
        echo '<div id="msgs"/>';
        echo $msgs_alertas;
        echo '</div>';
        echo '<div id="files"/>';
        echo '<h4 style="color: #000066">' . lang('Choose a file with CAs to add') . ':<h5>';
        echo '<input id="file" type="file" name="file" />';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="button" name="adicionar" value="' . lang('Add') . '" onclick="javascript:Submete_Cas(\'frm3\',\'' . lang('Add file contents to ACs file') . ' ?\')" />';
        echo '</div>';
        echo '</form>';
        echo '<h2 id="titulo1" style="color: #000066">' . lang('Certificates in') . ' ' . $path3 . ' :</h2>';
        echo '<div id="xdiv1" style="border: #000000 1px solid; overflow: auto; width: 870px; height: 160px; white-space: pre;  padding: 3px; " >';
        echo '<br/><font color="#000066"><b> ' . lang('Loading ...') . '</b></font>';
        echo '</div>';
        echo '<br/><pre>';
        echo '<div id="xdiv2" style="border: #000000 1px solid; overflow: auto; width: 870px; height: 180px; white-space: pre;  padding: 3px; " >';
        echo '<br/><font color="#000066"><b> ' . lang('Loading ...') . '</b></font>';
        echo '</div></pre>';
        echo '<br/>';
        echo '<a href="../security/security_admin.php" style="text-decoration:none"><input type="button" value="' . lang('Back') . '"/></a>';
        echo '<div>';
        echo '<script type="text/javascript"> Lista_de_Certificados(\'' . $path3 . '\'); </script>';
        
 ?>
