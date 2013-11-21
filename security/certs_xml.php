<?php
        $GLOBALS['phpgw_info'] = array();
        $GLOBALS['phpgw_info']['flags'] = array('noheader'   => True,'nonavbar'   => True,'currentapp' => 'admin');
        include('../header.inc.php');
	require_once('classes/CertificadoB.php');
        $xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	// pega caminho completo do arquivo de CAS..
        $path3 =$GLOBALS['CAs'];
        // se não pude acessar o arquivo com certificados retornar .....
	if(!is_file($path3))
            {
                $xml .= '<certificados><cert><item>' . $path3 .  '</item><nome>Path para pasta com certificados esta invalida</nome><validade></validade></cert></certificados>';
                Header('Content-type: application/xml; charset=utf-8');
                echo $xml;
                exit();
            }
	$todos_certificados = ler_certificados_CAS($path3);
	$CB = new CertificadoB();
        //$_POST['id'] = 'A';
	if($_POST['id'])
            {
                if($_POST['id'] != 'A')
                    {
                        // id indica o certificado solicitado ....
                        $aux = explode('-',$_POST['id']);
                        if(count($aux) > 1)
                            {
                                $id =$aux[0];
                                $id = $id -1;
                            }
                        else
                            {
                                $id = 0;
                            }
                        // Pega o certificado solicitado ...
                        $certificado = $todos_certificados[$id];
                        // Vai parsear, e gerar o xml ...
                        $CB -> certificado($certificado);
                        // Pega o xml com os dados do certificado ..
                        $xml .= $CB -> dados_xml;
                    }
                else
                    {
                        // Requisitado todo o conteudo do arquivo de CAs ..
                        $item = 1;
                        $processados = array();
                        $xml .= "<certificados>";
                        //$xml .= '<cert><item>0</item><nome>Parametro  xxxxxxxxxx xxxxxxxxxxxxxxxxxxxx invalido.</nome><validade>  asdad </validade></cert>';
                        
                        foreach($todos_certificados as $certificado)
                            {
                                $CB -> certificado($certificado);
                                $df = $CB -> dados['FIM_VALIDADE'];
                                $xml .= '<cert>';
                                $xml .= '<item>' . $item++ . '</item>';
                                $xml .= '  <nome>' . $CB->dados['SUBJECT']['CN'] . '</nome>';
                                if($processados[$CB->dados['SUBJECT']['CN']])
                                    {
                                        $alerta = '<font color="#FF0000"><b>DUPLICADO (veja o item ' . $processados[$CB->dados['SUBJECT']['CN']] . ' acima) </b></font>';
                                    }
                                else
                                    {
                                        $alerta = '';
                                        $processados[$CB->dados['SUBJECT']['CN']] = $item-1;
                                    }
                                $xml .= '<validade> ' . $alerta . ' Valido ate ' . substr($df,0,4) . '/' . substr($df,4,2) .
                                                '/' .
                                                substr($df,6,2) .
                                                '  -  ' .
                                                substr($df,8,2) .
                                                ':' .
                                                substr($df,10,2) .
                                                ':' .
                                                substr($df,12,2) .
                                        ' GMT' . '</validade>';
                                $xml .= '</cert>';
                            }
                         
                         
                        $xml .= "</certificados>";

                    }
            }
	else 
            {
                $xml .= '<certificados><cert><item>99</item><nome>Parametro invalido.</nome><validade> </validade></cert></certificados>';
            }
        # Fecha o processamento de geracao do xml  com um CABEÇALHO
        Header('Content-type: application/xml; charset=utf-8');
	echo $xml;
    ?>
  
  