<?php
        $GLOBALS['phpgw_info'] = array();
        $GLOBALS['phpgw_info']['flags'] = array('noheader'   => True,'nonavbar'   => True,'currentapp' => 'admin');
        include('../header.inc.php');
	require_once('classes/CertificadoB.php');
        require_once('security-lib.php');

        Header('Content-type: application/xml; charset=utf-8');
        // Se nao puder identificar o certificado e ou o arquivo, retornar .....
	if(!$_POST['arquivo'] || !$_POST['id'])
        {
            echo "<certificados>NOK1</certificados>";
            exit();
        }

	// pega caminho completo do arquivo de CAS..
        $path_parts = pathinfo($GLOBALS['CAs']);
        $path3 = $path_parts['dirname'] . '/' . $_POST['arquivo'];
	$todos_certificados = ler_certificados_CAS($path3);
        $flg = 0;
        $aux = '';
       
        foreach ($todos_certificados as $key => $value)
            {
                // Se for o mesmo id vai desprezar o certificado.
                if($key != $_POST['id']-1)
                    {
                        if($aux == '')
                            {
                                $aux .= $value;
                            }
                        else
                            {
                                $aux .= chr(0x0D) . chr(0x0A) . $value;
                            }
                    }
                else
                    {
                        $flg = 1;
                    }
            }

        if($flg == 0)
            {
                // Nao foi possivel executar a operacao solicitada...
                echo "<certificados>NOK2" . $path3 . "</certificados>";
                exit();
            }

        // Primeiro salva o arquivo original.
        $novo_nome = gera_nome_arquivo_bkp($path3);
        if($novo_nome == $path3)
            {
                echo "<certificados>NOK" . 'Falhou ao salvar arquivo (CD01)' . "</certificados>";
                exit();
            }
        $ret = salva_arquivo_bkp($path3,$novo_nome);
        if($ret != 0)
            {
                echo "<certificados>NOK" . 'Falhou ao salvar arquivo (CD02)' . "</certificados>";
                exit();
            }
        // Agora salva o arquaivo alterado.....
        file_put_contents($path3, $aux);
	echo "<certificados>OK" . $path3 . "</certificados>";
    ?>
  
  
