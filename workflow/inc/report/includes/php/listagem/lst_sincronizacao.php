<?

          
$sql = "select lstidlistagem from listagem where lstoid = $lstoid";
$res = pg_query($sql);
$linha = pg_fetch_object($res);          
$idlistagem = $linha->lstidlistagem;

$listas = array("$idlistagem");
$retorno = utf8_decode(verificaVersao($listas,false));

if ($retorno) {
    
    //echo $retorno;
$resposta = new SimpleXMLElement($retorno);

    
foreach ($resposta->listagem as $listagem) {
        //echo $listagem->lstidlistagem . ":" . $listagem->sincronizado . "<br>";
        //print_r($listagem->dependencias);
        
        $versao_desenvolvimento = $listagem->versaoTESTES;
        $versao_producao = $listagem->versaoPRODUCAO;
        
        $tr .= "<table class='tableMoldura'>
                <tr class='tableSubTitulo'><td colspan=3><h2>Módulos dependentes desta listagem em producao.</h2></td></tr>
                <tr class='tableTituloColunas'><td><h3>Arquivo</h3></td><td align='center'><h3>Data Primeiro Acesso</h3></td><td align='center'><h3>Data Último Acesso</h3></td></tr>";
        foreach ($listagem->dependencias as $dep) {
            if (is_object($dep)) {
            	foreach ($dep as $dependencia) {
                    $class = ( $class == "tdc" ) ? "tde" : "tdc";
                    $tr .= "<tr class='$class'>";
                    $tr .= "<td>" . $dependencia->arquivo . "</td><td align='center'>" . date("d/m/Y H:i",strtotime($dependencia->dt_cadastro)) ."</td><td align='center'>" . date("d/m/Y H:i",strtotime($dependencia->dt_acesso)) . "</td>";
                    $tr .= "</tr>";
            	}
            } else {
            	$tr .= "<tr class='tableRodapeModelo1'><td colspan=3 align='center'><h3>Nenhum Resultado Encontrado.</h3></td></tr>";
            }
        }
        $tr .= "</table>";
        
        if ($versao_desenvolvimento == "0") {
            $versao_desenvolvimento = "NÃO EXISTE.";
        } 
        
        if ($versao_producao == "0") {
        	$versao_producao = "NÃO EXISTE.";
        } 
        
        if ($listagem->sincronizado == "1") {
        	$html_botoes = "<div id='botoes_sincronizacao' style='margin: 10px; width: 60px; float: left;'><br><br><br><img src='./images/icones/v.gif'></div>";
        } else {
        	$html_botoes = "<div id='botoes_sincronizacao' style='margin: 10px; width: 60px; float: left;'><br><br><img src='./images/icones/t3/cetaDireita.jpg' style='cursor: pointer;' onclick=\"if (confirm('Deseja Realmente Sincronizar?')) { xajax_sincronizar('$idlistagem','1'); } \"><br><br><img src='./images/icones/t3/cetaEsquerda.jpg' style='cursor: pointer;' onclick=\"if (confirm('Deseja Realmente Sincronizar?')) { xajax_sincronizar('$idlistagem','2'); } \"></div>";
        }
}



$html = "<center>
            <table class='tableMoldura'>
               <tr class='tableSubTitulo'><td><h2>Sincronização de Servidores</h2></td></tr>
               <tr><td align='center'>
                <div style='text-align: center; width: 100%;'>
                <div style='margin: 10px; width: 300px; border: 1px solid #E0E0E0; float: left;'>
                    <img src='./images/icones/databaseGrande.gif' style='float: left;'>
                    <br><br><br>
                    <h2>DESENVOLVIMENTO</h2>
                    <br>
                    <br>
                    <h2>VERSÃO:<span id='versao_desenvolvimento'>$versao_desenvolvimento</span></h2> 
                    <div style='clear: both;'></div>
                </div>
                $html_botoes
                <div style='margin: 10px; width: 300px; border: 1px solid #E0E0E0; float: left;'>
                    <img src='./images/icones/databaseGrande.gif' style='float: left;'>
                    <br><br><br>
                    <h2>PRODUCAO</h2>
                    <br>
                    <br>
                    <h2>VERSÃO:<span id='versao_producao'>$versao_producao</span></h2> 
                    <div style='clear: both;'></div>
                </div>
                <div style='clear: both;'></div>
</div>
                </td></tr>
            </table>

$tr
</center>";
            
}

echo $html;

  
?>