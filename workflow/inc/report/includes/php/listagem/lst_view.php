<?php

$conn = &Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID->_connectionID;

$BASE_URL = "index.php?menuaction=workflow.reports.view";

$lstoid = $_POST['lstoid'];
if ($lstoid == "") { $lstoid = $_GET['lstoid']; }

$lstidlistagem = $_POST['idlistagem'];
if ($lstidlistagem == "") { $lstidlistagem = $_GET['idlistagem']; }

if ($lstoid != "") { 
	$sql = "select lstidlistagem, lstnome from listagem.listagem where lstoid = $lstoid";
	$res = pg_query($conn,$sql);
	$dados= pg_fetch_array($res);
	$lstidlistagem = $dados["lstidlistagem"];
} else {
	$sql = "select lstoid, lstidlistagem, lstnome from listagem.listagem where lstidlistagem = '$lstidlistagem'";
	$res = pg_query($conn,$sql);
	$dados= pg_fetch_array($res);
	$lstidlistagem = $dados["lstidlistagem"];
	$lstoid = $dados["lstoid"];
}

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem = new Listagem($lstidlistagem,"",$conn);
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


echo "<center>";
$listagem->desenhar();

?>