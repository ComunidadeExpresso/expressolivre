<?php
        $GLOBALS['phpgw_info'] = array();
        $GLOBALS['phpgw_info']['flags'] = array('noheader'   => True,'nonavbar'   => True,'currentapp' => 'admin');
        include('../header.inc.php');
	require_once('classes/CertificadoB.php');
        require_once('security-lib.php');
        $xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        
        if($_POST['caminho'])
            {
                $dir = dirname($_POST['caminho']);
            }
        else
            {    
                $Linhas = explode(chr(0x0A),file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/security/crl_admin/crl_admin_confg.py'));
                foreach($Linhas as $linha)
                    {
                        $path = pega_path(array( 'CAfile =', 'CAfile='),$linha);
                        if($path)
                            {
                                $path3 = $path;
                                break;
                            }
                    }
                $dir = dirname($path3);
            }

        if(!is_dir(dirname($dir))) exit();

        $ret = ler_arquivos_de_uma_pasta($dir);
        sort($ret);
        $xml .= '<arquivos>';
        foreach($ret as $file)
            {
                if($file[0] != '.')  $xml .= '<arq>' . $file . '</arq>';
            }
        $xml .= '</arquivos>';
        Header('Content-type: application/xml; charset=utf-8');
        echo $xml;
?>
