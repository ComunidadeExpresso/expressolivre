<?php

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem       = new Listagem("listResultado","Colunas",$conn);
$listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");

$lslcoid = (isset($_POST['lslcoid'])) ? $_POST['lslcoid'] : $_GET['lslcoid'];

if ($acao == "excluir") {
	$sql = "delete from listagem.listagem_coluna where lslcoid = $lslcoid ";
    pg_query($conn,$sql);
    $msg = "Coluna Excluída com sucesso!";
}

if ($form->isSubmit("atualizar_coluna",true)) {
    
    $lslcidcoluna         = $_POST['lslcidcoluna'];
    $lslcidcoluna = str_replace("{","",$lslcidcoluna);
    $lslcidcoluna = str_replace("}","",$lslcidcoluna);
    
    $lslctitulo           = $_POST['lslctitulo'];
    $lslctipo             = $_POST['lslctipo'];
    $lslchtml             = $_POST['lslchtml'];
    $lslcalign            = $_POST['lslcalign'];
    $lslcwidth            = $_POST['lslcwidth'];
    $lslclink             = $_POST['lslclink'];
    $lslclink_condicao    = $_POST['lslclink_condicao'];
    $lslcordem            = $_POST['lslcordem'];
    
    //print_r($_POST['lslclink_blank']);
    $lslclink_blank = (isset($_POST['lslclink_blank'])) ? true: false;
    $lslcnowrap =  (isset($_POST['lslcnowrap'])) ? true: false;
    $lslcvisivel =  (isset($_POST['lslcvisivel'])) ? true: false;
    $lslcexibe_csv = (isset($_POST['lslcexibe_csv'])) ? true: false;
    $lslccheckbox = (isset($_POST['lslccheckbox'])) ? true: false;
    $lslccalculada = (isset($_POST['lslccalculada'])) ? true: false;
    
    $flslclink_blank = ($lslclink_blank) ?  "'t'" : "'f'"; 
    $flslcnowrap = ($lslcnowrap) ?  "'t'" : "'f'"; 
    $flslcexibe_csv = ($lslcexibe_csv) ?  "'t'" : "'f'"; 
    $flslcvisivel = ($lslcvisivel) ?  "'t'" : "'f'";
    $flslccheckbox = ($lslccheckbox) ?  "'t'" : "'f'"; 
    $flslccalculada = ($lslccalculada) ?  "'t'" : "'f'";  
    
    try {
        pg_query($conn,"BEGIN;");
       if ($lslcoid == "") {
            
            $lslcidcoluna =  htmlspecialchars($lslcidcoluna, ENT_QUOTES);
            $lslctitulo =  htmlspecialchars($lslctitulo, ENT_QUOTES);
            $lslchtml =  htmlspecialchars($lslchtml, ENT_QUOTES);
            $lslclink =  htmlspecialchars($lslclink, ENT_QUOTES);
            $lslclink_condicao =  htmlspecialchars($lslclink_condicao, ENT_QUOTES);    
            
            $sqlver = "select lslcoid from listagem.listagem_coluna where lslcidcoluna = '$lslcidcoluna' and lslclstoid = '$lstoid'";
            $res = pg_query($sqlver);
            $qtd = pg_num_rows($res);
    
            if ($qtd == 0) {
                $sql = "insert into 
                            listagem.listagem_coluna ( 
                            
                            lslclstoid,
                            lslcidcoluna,
                            lslctipo,
                            lslctitulo,
                            lslchtml,
                            lslcalign,
                            lslcexibe_csv,
                            lslcvisivel,
                            lslccheckbox,
                            lslccalculada,
                            lslcnowrap,
                            lslcordem,
                            lslclink,
                            lslclink_condicao,
                            lslcwidth
                        ) values (
                        
                            $lstoid,
                            '$lslcidcoluna',
                            '$lslctipo',
                            '$lslctitulo',
                            '$lslchtml',
                            '$lslcalign',
                            $flslcexibe_csv,
                            $flslcvisivel,
                            $flslccheckbox,
                            $flslccalculada,
                            $flslcnowrap,
                            $lslcordem,
                            '$lslclink',
                            '$lslclink_condicao',
                            '$lslcwidth'
                        );";
                 //       echo $sql;
                $res = pg_query($conn,$sql);
                
                //atualizarVersao($conn,$lstoid);
                if (!$res) { throw new Exception("Incluindo nova coluna."); }
                
                $msg=  "Coluna adicionada com Sucesso!";
                
            } else {
                $msg = "Identificador de Coluna já está adicionado a essa listagem.";
            }
            
        } else {
            $sql = "update 
                        listagem.listagem_coluna 
                    set lslcidcoluna = '$lslcidcoluna', 
                        lslctipo='$lslctipo', 
                        lslctitulo = '$lslctitulo', 
                        lslchtml = '$lslchtml',
                        lslcalign = '$lslcalign',
                        lslcwidth = '$lslcwidth',
                        lslcexibe_csv = $flslcexibe_csv,
                        lslcvisivel = $flslcvisivel,
                        lslccalculada = $flslccalculada,
                        lslccheckbox = $flslccheckbox,
                        lslcnowrap = $flslcnowrap,
                        lslcordem = $lslcordem,
                        lslclink = '$lslclink',
                        lslclink_condicao = '$lslclink_condicao',
                        lslclink_blank = $flslclink_blank
                    where lslcoid = $lslcoid
    
     ";
           $res = pg_query($conn,$sql);
           if (!$res) { throw new Exception("Atualizando informações da coluna."); }
           
           //atualizarVersao($conn,$lstoid);
           
           $msg=  "Coluna atualizada com Sucesso!";
           $lslcoid = "";
           $acao = "editar";
        }
        
        pg_query($conn,"COMMIT;");
    
     } catch (exception $e) {
                $msg = "ERRO: " . $e->getMessage();
                pg_query($conn,"ROLLBACK;");
     }
}

if ($acao == "editar") {
    if ($lslcoid != "") {
        $sql = "select 
                    lslcoid,
                    lslclstoid,
                    lslcidcoluna,
                    lslcordem,
                    lslctipo,
                    lslctitulo,
                    lslchtml,
                    lslcalign,
                    lslcwidth,
                    lslcnowrap,
                    lslcvisivel,
                    lslcexibe_csv,
                    lslccalculada,
                    lslccheckbox,
                    lslclink,
                    lslclink_condicao,
                    lslclink_blank,
                    lslctotalizador_condicao,
                    lslcsubtotalizador_condicao,
                    lslccheckbox_condicao
                from 
                    listagem.listagem_coluna 
                where 
                    lslcoid = $lslcoid ";
        $res = pg_query($sql);
        $dados = pg_fetch_object($res);
        $lslcidcoluna = $dados->lslcidcoluna;
        $lslctitulo = $dados->lslctitulo;
        $lslctipo = $dados->lslctipo;
        $lslchtml = $dados->lslchtml;
        $lslcalign = $dados->lslcalign;
        $lslcwidth = $dados->lslcwidth;
        $lslclink = $dados->lslclink;
        $lslclink_condicao = $dados->lslclink_condicao;
        $lslclink_blank = ($dados->lslclink_blank == "t") ? true: false;
        $lslcnowrap = ($dados->lslcnowrap == "t") ? true: false;
        $lslcvisivel = ($dados->lslcvisivel == "t") ? true: false;
        $lslcexibe_csv = ($dados->lslcexibe_csv == "t") ? true: false;
        $lslccheckbox = ($dados->lslccheckbox == "t") ? true: false;
        $lslccalculada = ($dados->lslccalculada == "t") ? true: false;
        $lslcordem = $dados->lslcordem;
    } else {
    	$lslcidcoluna = "";
        $lslctitulo = "";
        $lslctipo = "";
        $lslchtml = "";
        $lslcalign = "";
        $lslcwidth = "";
        $lslclink = "";
        $lslclink_condicao = "";
        $lslclink_blank = false;
        $lslcnowrap = false;
        $lslcvisivel = true;
        $lslcexibe_csv = true;
        $lslccheckbox = false;
        $lslccalculada = false;
        $lslcordem = "";
    }
}


if(isset($lstoid{0})){


    $lslcidcoluna       =  html_entity_decode($lslcidcoluna, ENT_QUOTES);
    $lslctitulo         =  html_entity_decode($lslctitulo, ENT_QUOTES);
    $lslchtml           =  html_entity_decode($lslchtml, ENT_QUOTES);
    $lslclink           =  html_entity_decode($lslclink, ENT_QUOTES);
    $lslclink_condicao  =  html_entity_decode($lslclink_condicao, ENT_QUOTES);
    

    $form->adicionarHidden("lstoid",$lstoid); 
    $form->adicionarHidden("lslcoid",$lslcoid); 
    $form->adicionarHidden("abaMenu",$abaMenu);
    $form->adicionarHidden("acao","editar");
    $form->adicionarCampo("lslcidcoluna","lslcidcoluna","ID da Coluna:","Identificador da Coluna",$lslcidcoluna,true,"20");
    $form->adicionarCampo("lslctitulo","lslctitulo","Titulo da Coluna:","Titulo da Coluna",$lslctitulo,true,"","","");
    
    $arrTipoColunas = array ( 
                              ''      => '---' ,
                              'text'      => 'Texto' ,
                              'int'       => 'Número' ,
                              'data'      => 'Data' ,
                              'hora'      => 'Hora' ,
                              'moeda'     => 'Moeda' 
           );
    $form->adicionarSelect("lslctipo","Tipo:","Tipo da Coluna",$lslctipo,$arrTipoColunas,true);
    $form->adicionarCampoAcao("lslctipo","onchange","xajax_selecionarTipoColuna(this.value);");
    
    $form->adicionarCampo("lslchtml","lslchtml","Html da Listagem:","Html da Listagem",$lslchtml,true,"","","campo que será exibido entre chaves. Ex: {nomedocampo}");

    $arrTipoAlign = array ( 
                            ''      => '---' ,
                            'left'   => 'Esquerda',
                            'center' => 'Centralizado',
                            'right'  => 'Direita' );
    $form->adicionarSelect("lslcalign","Alinhamento (align):","Alinhamento da Coluna",$lslcalign,$arrTipoAlign,true);

    $form->adicionarCampo("lslcwidth","lslcwidth","Tamanho (width):","Tamanho da Coluna",$lslcwidth,false,"10","","Ex: (100px ou 50%)");
    $form->adicionarCampo("lslcordem","lslcordem","Ordem:","Ordem da Coluna",$lslcordem,true,"10","","Ordem que a coluna irá aparecer.");
    
    $form->adicionarSubTitulo("Coluna com Link");
    $form->adicionarCampo("lslclink","lslclink","URL:","URL",$lslclink,false,"40","","Ex.: arquivo.php?acao=editar&codigo={codigo} OU javascript:editar({codigo});");

    $form->adicionarCampo("lslclink_condicao","lslclink_condicao","Link Condição:","Link Condição",$lslclink_condicao,false,"","","Condição para exibir o link. Ex: (({meustatus} == 1) && ({tipo} > 200)) OU 1 para sempre exibir.");
    
    $arrCheckBlank[] = array('t', ' Abrir em uma nova janela.', $lslclink_blank);
    $form->adicionarCheckBox("lslclink_blank","",$arrCheckBlank,false);

    //$form->agruparCampos("lslclink,lslclink_blank",true);
    
    $form->adicionarSubTitulo("Outras Opções");
    $arrCheckNowRap[] = array('t', ' Não habilitar quebra de linha (nowrap)', $lslcnowrap);
    $form->adicionarCheckBox("lslcnowrap","",$arrCheckNowRap,false);
    $form->adicionarCampoAcao("lslcnowrap","onchange","nowrapCheck(this.checked);");

    $arrCheckVisivel[] = array('t', ' Coluna é Visível ', $lslcvisivel);
    $form->adicionarCheckBox("lslcvisivel","",$arrCheckVisivel,false);

    $arrCheckCSV[] = array('t', ' Exibir coluna no arquivo CSV:', $lslcexibe_csv);
    $form->adicionarCheckBox("lslcexibe_csv","",$arrCheckCSV,false);

    $arrCheckCalculada[] = array('t', ' Coluna é calculada, com Totalizador e Sub-Totalizador', $lslccalculada);
    $form->adicionarCheckBox("lslccalculada","",$arrCheckCalculada,false);

    $arrCheckCheck[] = array('t', ' Possui Checkbox para seleção de itens.', $lslccheckbox);
    $form->adicionarCheckBox("lslccheckbox","",$arrCheckCheck,false);
   
    
    $form->adicionarQuadro("quadro2","Cadastro de Colunas");
    
    if ($lslcoid == "") {
    	$titbotao = "Adicionar";
    } else {
    	$titbotao = "Atualizar";
    }
    
    $form->adicionarSubmit("quadro2","btn_cadastrar",$titbotao,"atualizar_coluna");
    
    
    //LISTAGEM DE COLUNAS
    $sql = "SELECT 
                lslcoid,
                lslclstoid,
                lslcidcoluna,
                lslcordem,
                lslctipo,
                lslctitulo,
                lslchtml,
                lslcalign,
                lslcwidth,
                lslcnowrap,
                lslcvisivel,
                lslcexibe_csv,
                lslccalculada,
                lslccheckbox,
                lslclink,
                lslclink_condicao,
                lslclink_blank,
                lslctotalizador_condicao,
                lslcsubtotalizador_condicao,
                lslccheckbox_condicao, 
                case when lslctipo = 'int' then 'Número' when lslctipo = 'hora' then 'Hora' when lslctipo = 'text' then 'Texto' when lslctipo = 'data' then 'Data' when lslctipo = 'moeda' then 'Moeda' end as novo_tipo  
            FROM 
                listagem.listagem_coluna 
            WHERE 
                lslclstoid=$lstoid 
            order by lslcordem";
    $listagem->carregar($sql);
    
    $listagem->adicionarIndicador("indvisivel","({lslcvisivel} == 't')","I","v","Coluna Visível");
    $listagem->adicionarIndicador("indcsv","({lslcexibe_csv} == 't')","I","v","Exibir no CSV");
    $listagem->adicionarIndicador("indnowrap","({lslcnowrap} == 't')","I","v","Não habilitar quebra de Linha");
    $listagem->adicionarIndicador("indcalculada","({lslccalculada} == 't')","I","v","Coluna Calculada");
    
    $listagem->adicionarColuna("lslcidcoluna","Coluna","{lslcidcoluna}","text","left","100px");
    $listagem->adicionarColuna("lslctitulo","Titulo","{lslctitulo}","text","left","100px");
    $listagem->adicionarColuna("lslchtml","Html","{lslchtml}","text","left","100px");
    $listagem->adicionarColuna("lslctipo","Tipo","{novo_tipo}","text","left","50px");
    $listagem->adicionarColuna("lslcwidth","Tamanho","{lslcwidth}","text","left","50px");
    $listagem->adicionarColuna("lslcvisivel","Visível","{indvisivel}","text","center","10px");
    $listagem->adicionarColuna("lslcexibe_csv","CSV","{indcsv}","text","center","10px");
    $listagem->adicionarColuna("lslcnowrap","Nowrap","{indnowrap}","text","center","10px");
    $listagem->adicionarColuna("lslccalculada","Calculada","{indcalculada}","text","center","10px");
    $listagem->adicionarColuna("lslcordem","Ordem","{lslcordem}","text","right","50px");
    
    $listagem->adicionarColuna("lslcoid","Excluir","[Excluir]","text","center","20px");
    $listagem->adicionarLink("lslcoid",$BASE_URL . "&abaMenu=colunas&lstoid=$lstoid&lslcoid={lslcoid}&acao=excluir");
    $listagem->adicionarLink("lslcidcoluna",$BASE_URL . "&abaMenu=colunas&lstoid=$lstoid&lslcoid={lslcoid}&acao=editar");
    $listagem->setMensagemRegistrosEncontrados("coluna(s) cadastrada(s)");
    $listagem->setMostrarLegendaTopo(false);
    $listagem->setMostrarLegendaRodape(false);
}

if ($msg != "") {
    echo "<script>ExibirMensagem('$msg')</script>";
}
echo "<script>xajax_selecionarTipoColuna('');</script>";
if ($lscnowrap != "") {
    echo "<script>nowrapCheck('$lslcnowrap');</script>";
}
$form->desenhar();
$listagem->desenhar();
?>