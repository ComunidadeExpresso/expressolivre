<?php

$sql = "select lstidlistagem, lstnome from listagem.listagem where lstoid = $lstoid";
$res = pg_query($conn,$sql);
$dados= pg_fetch_array($res);
$lstidlistagem = $dados["lstidlistagem"];

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem = new Listagem($idlistagem,"",$conn);
$listagem->setDebug(true);
$listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");
$listagem->carregarIDListagem($lstidlistagem);

$sql_par = "select 
                lspoid,
                lsplstoid,
                lspidparametro,
                lsptitulo,
                lsptipo,
                lspvalor_padrao,
                lspobrigatorio 
            from 
                listagem.listagem_parametro 
            where 
                lsplstoid = $lstoid 
            order by lspoid ";
$resu_par = pg_query($conn,$sql_par);

$form->adicionarHidden("lstoid",$lstoid);
$form->adicionarHidden("abaMenu",$abaMenu);
$form->adicionarHidden("acao","editar");
$exibeform = false;

while ($parametro = pg_fetch_object($resu_par)) {
    $idparametro = $parametro->lspidparametro;
    $titulo = $parametro->lsptitulo;

    
    if ($titulo == "") { 
    	$titulo = $idparametro;
    }
    $tipo = $parametro->lsptipo;
    $obrigatorio = ($parametro->lspobrigatorio == "t") ? true : false;
    $valor_padrao = $parametro->lspvalor_padrao;
    
    $exibeform = true;    
    
    $valor = $_POST[$idparametro];
    if ($tipo == "data") {
    	$valor = $_POST[$idparametro . "_inicio"];
    }
    if (($valor == "") && ((!isset($_POST[$idparametro])) && (!isset($_POST[$idparametro . "_inicio"])))) { $valor = $valor_padrao; }
    
    if ($obrigatorio) { $titulo .= " *"; }

    $form->adicionarCampo($idparametro,$tipo,$titulo. ":",$idparametro . ":",$valor,$obrigatorio);
    
    $listagem->setParametro($idparametro,$valor);
    
    $addobr = "";
    if ($obrigatorio) { $addobr = " //OBRIGATÓRIO"; }
    
    $adicionarcodigo .= "\$report->setParam('$idparametro',\$$valor_$idparametro);$addobr<br>";
}

if ($exibeform) {
    $form->adicionarQuadro("quadro1","Formulário de Pesquisa");
    $form->adicionarSubmit("quadro1","btn_cadastrar","Pesquisar","pesquisar");
    $form->desenhar();
}



$html = "<center>
            <table class='tableMoldura'>
               <tr class='tableSubTitulo'><td><h2>CÓDIGO FONTE:</h2></td></tr>
               <tr><td>
                <br>
                <div style='margin: 10px;'>
                \$report = Factory::newInstance('wf_report');<br>
                \$report->loadReport('$lstidlistagem');<br>$adicionarcodigo
                \$html_report = \$report->getHTML();<br>
                \$this->addViewVar('report_result', \$html_report);
                <br><br>
                </div>
                </td></tr>
            </table>
</center>";

echo $html;

$listagem->desenhar();
$consulta = $listagem->getSQL();


$res = pg_query($conn,"EXPLAIN " . $consulta);
if (pg_num_rows($res)) {
    $tr .= "<table class='tableMoldura'>
                    <tr class='tableSubTitulo'><td colspan=5><h2>EXPLAIN</h2></td></tr>
                    <tr class='tableTituloColunas'>
                        <td><h3>Possíveis problemas encontrados no plano de consulta</h3></td>
                    </tr>";
    
            $qtd = 0;
            $qtd1 = 0;
            while ($plan = pg_fetch_array($res)) {
                $qtd1 = $qtd1+1;
                if ((stristr($plan["QUERY PLAN"],"Seq Scan")) || ($qtd1 == 1)) {
                    $class = ( $class == "tdc" ) ? "tde" : "tdc";
                    $tr .= "<tr class='$class'>";
                    $tr .= "<td>" .$plan["QUERY PLAN"]. "</td>";
                    $tr .= "</tr>";
                    $qtd = $qtd + 1;
                }
            }
            if ($qtd == 0) { $msgres = "Nenhum Resultado Encontrado."; } else { $msgres = "$qtd resultado(s) encontrado(s)."; }
            $tr .= "<tr class='tableRodapeModelo1'><td colspan=5 align='center'><h3>$msgres</h3></td></tr>";
            $tr .= "</table>";
}

echo "<center><table class='tableMoldura'>
                   <tr class='tableSubTitulo'><td><h2>CONSULTA EXECUTADA:</h2></td></tr>
                   <tr><td>
                    <br>
                    <div style='margin: 10px;'>
                    $consulta
                    </div>
    <br><center>$tr</center>
                    </td></tr>
                </table></center>";

?>