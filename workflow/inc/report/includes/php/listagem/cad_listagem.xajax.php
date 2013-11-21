<?php

//require_once ('./xajax/xajax.inc.php');

$xajax = new xajax();
$xajax->registerFunction('selecionarTipoColuna');
$xajax->registerFunction('sincronizar');

$xajax->setCharEncoding('ISO-8859-1');
$xajax->decodeUTF8InputOn();

$xajax->processRequests();

// Funes XAJAX
function selecionarTipoColuna($tipo) {
    global $conn;
    $objResponse = new xajaxResponse();
    
    $objResponse->addScript("formUtilOcultaCampos('lslccalculada,lslccheckbox');");
    
    if ($tipo == "int") {
        $objResponse->addScript("formUtilExibeCampos('lslccalculada,lslccheckbox,lslctotalizador_condicao,lslcsubtotalizador_condicao,lslccheckbox_condicao');");
    }
    
    if ($tipo == "moeda") {
        $objResponse->addScript("formUtilExibeCampos('lslccalculada,lslctotalizador_condicao,lslcsubtotalizador_condicao');");
    }
    
    if ($tipo == "hora") {
        $objResponse->addScript("formUtilExibeCampos('lslccalculada,lslctotalizador_condicao,lslcsubtotalizador_condicao');");
    }
    
    return $objResponse->getXML();
}


function sincronizar($idlistagem,$conexao = "1") {
    global $conn;
    $objResponse = new xajaxResponse();
    
    $xml_params = '<?xml version="1.0" encoding="ISO-8859-1"?>
    <raiz xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <sincronizaVersao>
            <lstidlistagem>'. $idlistagem . '</lstidlistagem>
            <conexao>'. $conexao . '</conexao>
        </sincronizaVersao>
    </raiz>';
    
     $xml_params = utf8_encode($xml_params);
     
     
     $server_url = "http://url_servidor/webservice/cad_listagem_sincronizacao/cadListagem_server.php?wsdl";

    $client = new SoapClient(   $server_url,
                            array(  'trace' => 1, 'exceptions' => 1,'soap_version' => SOAP_1_1));
                                    
    try {

        $save_result = $client->sincronizar($xml_params);
        
        $objResponse->addAssign("botoes_sincronizacao","innerHTML","<br><br><br><img src='./images/icones/v.gif'>");
        
        if ($conexao == "1") {
            $objResponse->addScript("document.getElementById('versao_producao').innerHTML = document.getElementById('versao_labirinto').innerHTML;");
        } else {
            $objResponse->addScript("document.getElementById('versao_desenvolvimento').innerHTML = document.getElementById('versao_producao').innerHTML;");
        }

        $objResponse->addScript("ExibirMensagem('Sincronizacao Efetuada com sucesso.')");
        $objResponse->addAlert('Sincronizacao Efetuada com sucesso.');
        
    } catch (SoapFault $e){
        $objResponse->addAlert("FALHOU! SOAP Fault: ".$e->getMessage());
    } 
    
    
    return $objResponse->getXML();
}

function atualizaStatus($listagems,$status,$debug = false) {
    $versoes = "";
    foreach ($listagems as $listagem) {
       $versoes .= '
    <atualizaStatus>
        <lstidlistagem>' . $listagem . '</lstidlistagem>
        <status>' . $status . '</status>
    </atualizaStatus>';   
    }
    
    $xml_params='<?xml version="1.0" encoding="ISO-8859-1"?>
<raiz xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $versoes . '
</raiz>';
    //$xml_params = utf8_encode($xml_params);
    
    //echo $xml_params;

	 $server_url = "http://url_servidor/webservice/cad_listagem_sincronizacao/cadListagem_server.php?wsdl";

    $client = new SoapClient(   $server_url,
                            array(  'trace' => 1, 'exceptions' => 1,'soap_version' => SOAP_1_1));
                                    
    try {

        $save_result = $client->atualizaStatus($xml_params);
        

        if ($debug) { 
            echo "<hr>";
            echo "<pre>\n\n";
            echo "Request Cabe�alho:\n";
            echo htmlspecialchars($client->__getLastRequestHeaders())."\n";
            echo "</pre>";
            
            echo "<pre>\n\n";
            echo "Request:\n";
            echo htmlspecialchars($client->__getLastRequest())."\n";
            echo "</pre>";
            echo "<hr>";
            
            echo "<pre>\n\n";
            echo "Retorno Cabe�alho:\n";
            echo htmlspecialchars($client->__getLastResponseHeaders())."\n";
            echo "</pre>";
            
            echo "<pre>\n\n";
            echo "Retorno:\n";
            echo htmlspecialchars($client->__getLastResponse())."\n";
            echo "</pre>";
            
            echo "<br>ENVIO FINALIZADO!<br>";
        }
        
        return $save_result;
        
    } catch (SoapFault $e){
        echo "<br>FALHOU! SOAP Fault: ".$e->getMessage()."<br>";        
    } 
}


function verificaVersao($listagems,$debug = false) {
    $versoes = "";
    foreach ($listagems as $listagem) {
       $versoes .= '
    <verificaVersao>
        <lstidlistagem>' . $listagem . '</lstidlistagem>
    </verificaVersao>';   
    }
    
    $xml_params='<?xml version="1.0" encoding="ISO-8859-1"?>
<raiz xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $versoes . '
</raiz>';
    //$xml_params = utf8_encode($xml_params);
    
    //echo $xml_params;
    
    $server_url = "http://url_servidor/webservice/cad_listagem_sincronizacao/cadListagem_server.php?wsdl";

    $client = new SoapClient(   $server_url,
                            array(  'trace' => 1, 'exceptions' => 1,'soap_version' => SOAP_1_1));
                                    
    try {

        $save_result = $client->verificaVersao($xml_params);
        

        if ($debug) { 
            echo "<hr>";
            echo "<pre>\n\n";
            echo "Request Cabecalho:\n";
            echo htmlspecialchars($client->__getLastRequestHeaders())."\n";
            echo "</pre>";
            
            echo "<pre>\n\n";
            echo "Request:\n";
            echo htmlspecialchars($client->__getLastRequest())."\n";
            echo "</pre>";
            echo "<hr>";
            
            echo "<pre>\n\n";
            echo "Retorno Cabecalho:\n";
            echo htmlspecialchars($client->__getLastResponseHeaders())."\n";
            echo "</pre>";
            
            echo "<pre>\n\n";
            echo "Retorno:\n";
            echo htmlspecialchars($client->__getLastResponse())."\n";
            echo "</pre>";
            
            echo "<br>ENVIO FINALIZADO!<br>";
        }
        
        return $save_result;
        
    } catch (SoapFault $e){
        echo "<br>FALHOU! SOAP Fault: ".$e->getMessage()."<br>";        
    } 
}

function atualizarVersao($conn,$lstoid) {
	$sql = "update listagem.listagem set lstversao = lstversao + 1 where lstoid = $lstoid";
    $res = pg_query($conn,$sql);
    return ($res);
}

function retornaParametros($sql) {
    $sqlparametros = array();
    $abre_chaves = explode("{",$sql);
    foreach ($abre_chaves as $chave) {
        $_chaves = array();
        if (stristr($chave,"}")) {
            $_chaves = explode("}",$chave);
            $sqlparametros[] = $_chaves[0];
        }
    }
    return $sqlparametros;
}

$xajax->printJavascript("workflow/inc/report/");

?>