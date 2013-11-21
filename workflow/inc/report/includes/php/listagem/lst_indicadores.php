<?php

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem       = new Listagem("listResultado","Indicadores",$conn);
$listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");


$lsioid = (isset($_POST['lsioid'])) ? $_POST['lsioid'] : $_GET['lsioid'];

if ($acao == "excluir_indicador") {
    $sql = "delete from listagem.listagem_indicador where lsioid = $lsioid ";
    pg_query($conn,$sql);
    $msg = "Parâmetro Excluído com sucesso!";
}

if ($form->isSubmit("atualizar_parametro",true)) {
    
    
    $lsiidindicador = $_POST['lsiidindicador'];
    $lsiidindicador = str_replace("{","",$lsiidindicador);
    $lsiidindicador = str_replace("}","",$lsiidindicador);
    
    $lsitipo = $_POST['lsitipo'];
    $lsiimagem = $_POST['lsiimagem'];
    $lsilegenda = $_POST['lsilegenda'];
    $lsilegenda_csv = $_POST['lsilegenda_csv'];
    $lsicondicao = $_POST['lsicondicao'];
    
    
    try {

        pg_query($conn,"BEGIN;");
        
        $lsilegenda =  htmlspecialchars($lsilegenda, ENT_QUOTES);
        $lsilegenda_csv =  htmlspecialchars($lsilegenda_csv, ENT_QUOTES);
        $lsicondicao =  htmlspecialchars($lsicondicao, ENT_QUOTES);    
        
        if ($lsioid == "") {
    
            
                $sql = "insert into 
                            listagem.listagem_indicador ( 
                            lsilstoid,
                            lsiidindicador,
                            lsitipo,
                            lsiimagem,
                            lsilegenda,
                            lsilegenda_csv,
                            lsicondicao
                        ) values (
                            $lstoid,
                            '$lsiidindicador',
                            '$lsitipo',
                            '$lsiimagem',
                            '$lsilegenda',
                            '$lsilegenda_csv',
                            '$lsicondicao'
                        );";
    
    			echo $sql;
                $res = pg_query($conn,$sql);
                //ATUALIZA A VERSÃO DA LISTAGEM PARA MANTER O PROCESSO DE SINCRONIZAÇÃO
                //atualizarVersao($conn,$lstoid);
                
                $msg=  "Indicador adicionado com Sucesso!";
           
        } else {
            $sql = "update 
                        listagem.listagem_indicador
                    set lsiidindicador = '$lsiidindicador',
                        lsitipo = '$lsitipo',
                        lsiimagem = '$lsiimagem',
                        lsilegenda = '$lsilegenda',
                        lsilegenda_csv = '$lsilegenda_csv',
                        lsicondicao = '$lsicondicao'
                    where lsioid = $lsioid ";
    
           $res = pg_query($conn,$sql);
           //ATUALIZA A VERSÃO DA LISTAGEM PARA MANTER O PROCESSO DE SINCRONIZAÇÃO
           //atualizarVersao($conn,$lstoid);
           $msg=  "Indicador atualizado com Sucesso!";
           $lsioid = "";
           $acao = "editar";
        }
     pg_query($conn,"COMMIT;");
    
     } catch (exception $e) {
                $msg = "ERRO: " . $e->getMessage();
                pg_query($conn,"ROLLBACK;");
     }
}

if ($acao == "editar") {
    if ($lsioid != "") {
        $sql = "select * from listagem.listagem_indicador where lsioid = $lsioid ";
        $res = pg_query($sql);
        $dados = pg_fetch_object($res);

        $lsiidindicador = $dados->lsiidindicador;
        $lsitipo = $dados->lsitipo;
        $lsiimagem = $dados->lsiimagem;
        $lsilegenda = $dados->lsilegenda;
        $lsilegenda_csv = $dados->lsilegenda_csv;
        $lsicondicao = $dados->lsicondicao;

    } else {
        $lsiidindicador = "";
        $lsitipo = "";
        $lsiimagem = "";
        $lsilegenda = "";
        $lsilegenda_csv = "";
        $lsicondicao = "";

    }
}


if(isset($lstoid{0})){
    
    $lsilegenda         =  html_entity_decode($lsilegenda, ENT_QUOTES);
    $lsilegenda_csv     =  html_entity_decode($lsilegenda_csv, ENT_QUOTES);
    $lsicondicao        =  html_entity_decode($lsicondicao, ENT_QUOTES);  

    $form->adicionarHidden("lstoid",$lstoid); 
    $form->adicionarHidden("lsioid",$lsioid); 
    $form->adicionarHidden("abaMenu",$abaMenu);
    $form->adicionarHidden("acao","editar");
    $form->adicionarCampo("lsiidindicador","lsiidindicador","ID do Indicador:","Identificador do Indicador",$lsiidindicador,true,"20","","Identificador do Indicador. (Não usar {}).");
    
    $arrTipoIndicador = array (  '' => '---',
                                 'Q'        => 'Quadrado' ,
                                 'R'      => 'Redondo' ,
                                 'T'      => 'Triangulo',
                                 'I'      => 'Ícone');
    $form->adicionarSelect("lsitipo","Tipo do Indicador:","Tipo do Indicador:",$lsitipo,$arrTipoIndicador,true);
    $form->adicionarCampoAcao("lsitipo","onchange","selecionaTipoIndicador(this.value,'$lsiimagem');");

    $arrImagemIndicador = array();
    $form->adicionarSelect("lsiimagem","Imagem do Indicador:","Imagem do Indicador:",$lsiimagem,$arrImagemIndicador,true,"","150");
    
    $form->adicionarCampo("lsilegenda","lsilegenda","Legenda:","Legenda",$lsilegenda,false,"30","","(Deixe em branco para não exibir)");
    $form->adicionarCampo("lsilegenda_csv","lsilegenda_csv","Legenda CSV:","Legenda",$lsilegenda_csv,false,"30","","Legenda que será exibida no arquivo CSV.");
    $form->adicionarCampo("lsicondicao","lsicondicao","Condição de Exibição:","Condição",$lsicondicao,false,"50","","Condição para exibir o indicador. Ex: (({meustatus} == 1) && ({tipo} > 200))");
    
    $form->adicionarQuadro("quadro2","Cadastro de Indicadores");
    $form->adicionarSubmit("quadro2","btn_cadastrar","Atualizar","atualizar_parametro");

    //LISTAGEM DE PARÂMETROS
    $sql = "SELECT * FROM listagem.listagem_indicador WHERE lsilstoid=$lstoid order by lsioid";
    $res = pg_query($sql);
    
    $listagem->carregar($sql);
    
    
    while ($indicador = pg_fetch_object($res)) {
        $lsioid = $indicador->lsioid;
        $tipo = $indicador->lsitipo;
        $imagem = $indicador->lsiimagem;
        $legenda = $indicador->lsilegenda;
        $listagem->adicionarIndicador("indicador","({lsioid} == $lsioid)",$tipo,$imagem,$legenda);
    }
    
    $listagem->adicionarColuna("indicador","","{indicador}","text","center","20px");
    $listagem->adicionarColuna("lsiidindicador","ID do indicador","{lsiidindicador}","text","left","100px");
    $listagem->adicionarColuna("lsilegenda","Legenda","{lsilegenda}","text","left","200px");
    $listagem->adicionarColuna("lsilegenda_csv","Legenda CSV","{lsilegenda_csv}","text","left","200px");
    $listagem->adicionarColuna("lsicondicao","Condição de Exibição","{lsicondicao}","text","left","200px");

    $listagem->adicionarColuna("lsioid","Excluir","[Excluir]","text","center","50px");
    $listagem->adicionarLink("lsioid",$BASE_URL . "&abaMenu=indicadores&lstoid=$lstoid&lsioid={lsioid}&acao=excluir_indicador");
    $listagem->adicionarLink("lsiidindicador",$BASE_URL . "&abaMenu=indicadores&lstoid=$lstoid&lsioid={lsioid}&acao=editar");
    $listagem->setMensagemRegistrosEncontrados("indicadores(s) cadastrado(s)");
    $listagem->setMostrarLegendaTopo(false);
    $listagem->setMostrarLegendaRodape(true);
}

if ($msg != "") {
    echo "<script>ExibirMensagem('$msg')</script>";
}
$form->desenhar();
$listagem->desenhar();
echo "<script>selecionaTipoIndicador('$lsitipo','$lsiimagem')</script>";
echo "<div id='div_result_colunas'></div>";
?>