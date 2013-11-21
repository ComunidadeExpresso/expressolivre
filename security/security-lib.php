<?php

    function salva_arquivo_bkp($nome_atual,$novo_nome)
        {
            if(!is_file($nome_atual)) return;
            $path_parts = pathinfo($novo_nome);
            if(!is_dir($path_parts['dirname'])) return;
            if(is_file($novo_nome)) return;
            $cmd = 'cp -p ' . $nome_atual . ' ' . $novo_nome;
            $ret = system($cmd,$status);
            return $status;
        }

    function gera_nome_arquivo_bkp($path3)
        {
            if(!is_file($path3)) return NULL;
/*
            $tab = 'ABCDEFGHIJKLMNOPQRSTUVXYZW';
            $i = rand(0,25);
            for($a1=0;$a1<3;++$a1)
                {
                    $i = rand(0,25);
                    $xx .= substr($tab,$i,1);
                }
 */
            $partes = explode('.',$path3);
/*
            if($partes[1])
                {
                    $novo = $partes[0] . '-' . date('ymd-His') . '-' . $xx . '.' . $partes[1];
                }
            else
                {
                    $novo = $partes[0] . '-' . date('ymd-His') . '-' . $xx;
                }
*/
            if($partes[1])
                {
                    $novo = $partes[0] . '~' . '.' . $partes[1];
                }
            else
                {
                    $novo = $partes[0] . '~';
                }

            return $novo;
        }

    function ler_arquivos_de_uma_pasta($dir)
        {
            // Abre um diretorio conhecido, e lista nome dos arquivos...
            $retorno = array();
            if (is_dir($dir))
                {
                    $dh = opendir($dir);
                    while (($file = readdir($dh)) !== false)
                    {
                        if($file[0] != '.')  $retorno[] = $file;
                    }
                    closedir($dh);
                }
            return $retorno;
        }

    function Gerar_Estruturas_Certificados($todos_certificados)
        {
            if(!is_array($todos_certificados)) return array();
            $item = 1;
            //$x_conta = 1;
            $aux_emissores= array();
            $tab_certs = array();
            $CB = new CertificadoB();
            foreach($todos_certificados as $certificados)
                {
                    $CB -> certificado($certificados);

                    $proprietario = $CB->dados['SUBJECT']['CN'];
                    $df = $CB->dados['FIM_VALIDADE'];
                    $di =  $CB->dados['INICIO_VALIDADE'];
                    if(gmdate("YmdHis") > $df)
                        {
                            $cor = '<label style="color: #FF0000" >Expirado em: </label>';
                            $dt_df_x = 'Expirado em: ';
                        }
                    else
                        {
                            $cor = '<label>Valido at&eacute; </label>';
                            $dt_df_x = 'Valido at&eacute; ';
                        }
                    $dt_df = substr($df,0,4) . '/' . substr($df,4,2) . '/' . substr($df,6,2) . '  -  ' . substr($df,8,2) . ':' . substr($df,10,2) . ':' . substr($df,12,2) . ' GMT';
                    $info = $cor . $dt_df;
                    $info = '<font size="1"' . $info . '</font>';
                    // Armazena alguns dados do certificado. $tabs_certs esta na mesma ordem em que os certificados aparecem no arquivo todos.cer.....
                    $tab_certs[$proprietario]['item'] = $item++;
                    $tab_certs[$proprietario]['certificado'] = $certificados;
                    $tab_certs[$proprietario]['emissor'] =  $CB->dados['EMISSOR_CAMINHO_COMPLETO']['CN'];
                    $tab_certs[$proprietario]['fim_validade'] = $info;
                    $tab_certs[$proprietario]['inicio_validade'] = $di;
                    if($CB->dados['EMISSOR_CAMINHO_COMPLETO']['CN'] !=  $CB->dados['SUBJECT']['CN'])
                        {
                            // Se nao he um auto assinado (identifica um raiz), salva emissor , faz a chave a tdata de inicio de validade do certificado...
                            $aux_emissores[$di][$CB->dados['EMISSOR_CAMINHO_COMPLETO']['CN']][$CB->dados['SUBJECT']['CN']] = '9' ;
                        }
                }
            // ordena certificados pela data de inicio de validade ......
            ksort($aux_emissores);
            return array($aux_emissores,$tab_certs);
        }

?>
