<?php

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem       = new Listagem("listResultado","Listagens",$conn);
$listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");

$acao = $_POST['FrmCadListagem_acao'];
if ($acao == "") { $acao = $_GET['acao']; }
if ($acao == "") { $acao = $_POST['acao']; }
$lstoid = $_POST['lstoid'];
if ($lstoid == "") { $lstoid = $_GET['lstoid']; }


    if ($acao == "alterarStatus") {
        $lstidlistagem = $_POST['lstidlistagem'];
        $novostatus = $_POST['novostatus'];
        $ret = atualizaStatus(array("$lstidlistagem"),$novostatus);
        $msg = "Status Alterado com Sucesso!"; 
        $acao = "editar";
    }
        

    if (($acao == "editar") || ($form->isSubmit("atualizar_listagem",true))) {

        $lstidlistagem = $_POST['lstidlistagem'];
        $lstversao = $_POST['lstversao'];
        $lstnome = $_POST['lstnome'];
        $lsttitulo = $_POST['lsttitulo'];
        $lstsql = $_POST['lstsql'];
        
        $lstmsg_totalizador = $_POST['lstmsg_totalizador'];
        $lstmsg_subtotalizador = $_POST['lstmsg_subtotalizador'];
        $lstmsg_registrosencontrados = $_POST['lstmsg_registrosencontrados'];
        $lstmsg_nenhumresultado = $_POST['lstmsg_nenhumresultado'];
        $lstdescricao = $_POST['lstdescricao'];
        
        $lstagrupamento_campo = $_POST['lstagrupamento_campo'];
        $lstagrupamento_titulo = $_POST['lstagrupamento_titulo'];
        $lstexclusao = $_POST['lstexclusao'];
        
        if ($lstmsg_totalizador == "")           { $lstmsg_totalizador = "TOTAL:"; }
        if ($lstmsg_subtotalizador == "")        { $lstmsg_subtotalizador = "SUB-TOTAL:"; }
        if ($lstmsg_registrosencontrados == "")  { $lstmsg_registrosencontrados = "registro(s) encontrado(s)."; }
        if ($lstmsg_nenhumresultado == "")       { $lstmsg_nenhumresultado = "Nenhum resultado encontrado."; }
        if ($lsttitulo == "")       { $lsttitulo = "Resultado da Pesquisa."; }
        
        if ($lstoid == "") {
              $lstexibe_resultados = true;
              $lstexibe_header = true;
              $lstexibe_totalizadores = true;
              $lstexibe_subtotais = true;
              $lstexibe_qtdregistros = true;
              $lstexibe_legendatopo = true;
              $lstexibe_titagrupamento = true;
              $lstexibe_agrupamento_alfabetico = false;
              $lstexclusao = true;
        }
        
        $arrOpcVisualizacao[] = array('lstexibe_resultados', ' Exibir resultados na tela.', $lstexibe_resultados);
        $arrOpcVisualizacao[] = array('lstexibe_header', ' Exibir titulos das colunas', $lstexibe_header);
        $arrOpcVisualizacao[] = array('lstexibe_totalizadores', ' Exibir totalizadores', $lstexibe_totalizadores);
        $arrOpcVisualizacao[] = array('lstexibe_subtotais', ' Exibir sub-totalizadores', $lstexibe_subtotais);
        $arrOpcVisualizacao[] = array('lstexibe_qtdregistros', ' Exibir Quantidade de registros encontrados', $lstexibe_qtdregistros);
        $arrOpcVisualizacao[] = array('lstexibe_checkbox', ' Exibir colunas com checkbox.', $lstexibe_checkbox);
        $arrOpcVisualizacao[] = array('lstexibe_csv', ' Gerar CSV', $lstexibe_csv);
        $arrOpcVisualizacao[] = array('lstexibe_legendatopo', ' Exibir legenda no topo', $lstexibe_legendatopo);
        $arrOpcVisualizacao[] = array('lstexibe_legendarodape', ' Exibir legenda no rodape', $lstexibe_legendarodape);
        $arrOpcVisualizacao[] = array('lstexibe_titagrupamento', ' Re-exibir titulos de agrupamento', $lstexibe_titagrupamento);
        $arrOpcVisualizacao[] = array('lstexibe_agrupamento_alfabetico', ' Exibir agrupamento alfabetico', $lstexibe_agrupamento_alfabetico);
        $arrOpcVisualizacao[] = array('lstexclusao', ' Ativa', $lstexclusao);
        foreach ($arrOpcVisualizacao as $opc) {
            $$opc[0] = "false";
        }
        $opcvisualizacao = $_POST['opcvisualizacao'];
        if (is_array($opcvisualizacao)) {
            foreach ($opcvisualizacao as $opc) {
                $$opc = "true";
            }
        }
        
        if ($lstexclusao == "false") {
        	$flstexclusao = "now()";
        }
        if ($lstexclusao == "true") {
        	$flstexclusao = "null";
        }
        
        if (($acao == "editar") && ($lstoid != "")) {
            $sql = "select 
                        lstoid,
                        lstversao,
                        lstidlistagem,
                        lstdescricao,
                        lstnome,
                        lsttitulo,
                        lstsql,
                        lstexibe_header,
                        lstexibe_totalizadores,
                        lstexibe_subtotais,
                        lstexibe_qtdregistros,
                        lstexibe_checkbox,
                        lstexibe_csv,
                        lstexibe_legendatopo,
                        lstexibe_legendarodape,
                        lstexibe_titagrupamento,
                        lstexibe_agrupamento_alfabetico,
                        lstagrupamento_campo,
                        lstagrupamento_titulo,
                        lstmsg_totalizador,
                        lstmsg_subtotalizador,
                        lstmsg_registrosencontrados,
                        lstmsg_nenhumresultado,
                        lstexclusao,
                        lstexibe_resultados
                    from 
                        listagem.listagem 
                    where 
                        lstoid = $lstoid";
            $res = pg_query($sql);
            $linha = pg_fetch_object($res);
            $lstidlistagem = "";
            foreach ($linha as $chave => $valor) {
                if ($valor == "f") { 
                    $valor = false;
                } 
                if ($valor == "t") { 
                    $valor = true;
                }
              //  $$chave= str_replace("[-]","'",$valor);;
                $$chave=  html_entity_decode($valor, ENT_QUOTES);
            } 
            if ($lstexclusao == "") {
            	$f_lstexclusao = true;
            } else {
            	$f_lstexclusao = false;
            }
            
            //$lstsql = str_replace("[-]","'",$lstsql);
            unset ($arrOpcVisualizacao);
            $arrOpcVisualizacao[] = array('lstexibe_resultados', ' Exibir resultados na tela.', $lstexibe_resultados);
            $arrOpcVisualizacao[] = array('lstexibe_header', ' Exibir titulos das colunas', $lstexibe_header);
            $arrOpcVisualizacao[] = array('lstexibe_totalizadores', ' Exibir totalizadores', $lstexibe_totalizadores);
            $arrOpcVisualizacao[] = array('lstexibe_subtotais', ' Exibir sub-totalizadores', $lstexibe_subtotais);
            $arrOpcVisualizacao[] = array('lstexibe_qtdregistros', ' Exibir Quantidade de registros encontrados', $lstexibe_qtdregistros);
            $arrOpcVisualizacao[] = array('lstexibe_checkbox', ' Exibir colunas com checkbox.', $lstexibe_checkbox);
            $arrOpcVisualizacao[] = array('lstexibe_csv', ' Gerar CSV', $lstexibe_csv);
            $arrOpcVisualizacao[] = array('lstexibe_legendatopo', ' Exibir legenda no topo', $lstexibe_legendatopo);
            $arrOpcVisualizacao[] = array('lstexibe_legendarodape', ' Exibir legenda no rodape', $lstexibe_legendarodape);
            $arrOpcVisualizacao[] = array('lstexibe_titagrupamento', ' Re-exibir titulos de agrupamento', $lstexibe_titagrupamento);
            $arrOpcVisualizacao[] = array('lstexibe_agrupamento_alfabetico', ' Exibir agrupamento alfabetico', $lstexibe_agrupamento_alfabetico);
            
            
            $arrOpcVisualizacao[] = array('lstexclusao', ' Ativa', $f_lstexclusao);
            
            $lstexclusao = $linha->lstexclusao;

        }

        if ($acao == "atualizar_listagem") {
            
            if ($lstoid != "") {
                 $sql = "select 
                            lstidlistagem
                        from 
                            listagem.listagem 
                        where 
                            lstoid = $lstoid";
                $res = pg_query($sql);
                $linha = pg_fetch_object($res);
                
                $lstidlistagem = $linha->lstidlistagem;
            
            }
            
            //$lstsql = str_replace("\\'","[-]",$lstsql);
            //$lstsql = str_replace("'","[-]",$lstsql);
            
            $lstdescricao = htmlspecialchars($lstdescricao,ENT_QUOTES);
            $lstnome = htmlspecialchars($lstnome,ENT_QUOTES);
            $lstsql = htmlspecialchars($lstsql,ENT_QUOTES);
            
            try {
                pg_query($conn,"BEGIN;");
                if ($lstoid == "") {
                    
                    $sql = "INSERT INTO listagem.listagem (  lstidlistagem,
                                                    lstnome,
                                                    lsttitulo,
                                                    lstsql,
                                                    lstexibe_header,
                                                    lstexibe_totalizadores,
                                                    lstexibe_qtdregistros,
                                                    lstexibe_subtotais,
                                                    lstexibe_checkbox,
                                                    lstexibe_csv,
                                                    lstexibe_legendatopo,
                                                    lstexibe_legendarodape,
                                                    lstexibe_titagrupamento,
                                                    lstmsg_totalizador,
                                                    lstmsg_subtotalizador,
                                                    lstmsg_registrosencontrados,
                                                    lstmsg_nenhumresultado, 
                                                    lstversao,
                                                    lstagrupamento_campo,
                                                    lstagrupamento_titulo,
                                                    lstdescricao,
                                                    lstexibe_agrupamento_alfabetico,
                                                    lstexibe_resultados,
                                                    lstexclusao
                                            ) 
                                            VALUES 
                                                (   '$lstidlistagem',
                                                    '$lstnome',
                                                    '$lsttitulo',
                                                    '$lstsql',
                                                    $lstexibe_header,
                                                    $lstexibe_totalizadores,
                                                    $lstexibe_qtdregistros,
                                                    $lstexibe_subtotais,
                                                    $lstexibe_checkbox,
                                                    $lstexibe_csv,
                                                    $lstexibe_legendatopo,
                                                    $lstexibe_legendarodape,
                                                    $lstexibe_titagrupamento,
                                                    '$lstmsg_totalizador',
                                                    '$lstmsg_subtotalizador',
                                                    '$lstmsg_registrosencontrados',
                                                    '$lstmsg_nenhumresultado',
                                                    1,
                                                    '$lstagrupamento_campo',
                                                    '$lstagrupamento_titulo',
                                                    '$lstdescricao',
                                                    $lstexibe_agrupamento_alfabetico,
                                                    $lstexibe_resultados,
                                                    $flstexclusao
                                            )";
                                                   
                                              //     echo $sql;
                    $result = pg_query($conn,$sql);
                    if (!$result) { throw new Exception("ERRO: Inserindo listagem."); }
                    
                    $sql = "SELECT max(lstoid) as lstoid from listagem.listagem ";
                    $result = pg_query($conn,$sql);
                    
                    $dados  = pg_fetch_array($result);
                    $lstoid = $dados['lstoid'];
                    
                    echo "<script>document.frm.lstoid.value='$lstoid';</script>";
                    $acao = "editar";
                                    
                    $msg = "Registro Inserido com Sucesso!";
    
                } else {
                    
                    $sql = "UPDATE listagem.listagem SET 
                                                lstnome = '$lstnome',
                                                lsttitulo = '$lsttitulo',  
                                                lstsql = '$lstsql',
                                                lstexibe_header = $lstexibe_header,
                                                lstexibe_totalizadores = $lstexibe_totalizadores,
                                                lstexibe_qtdregistros = $lstexibe_qtdregistros,
                                                lstexibe_subtotais = $lstexibe_subtotais,
                                                lstexibe_checkbox = $lstexibe_checkbox,
                                                lstexibe_csv = $lstexibe_csv, 
                                                lstexibe_legendatopo = $lstexibe_legendatopo,
                                                lstexibe_legendarodape = $lstexibe_legendarodape,
                                                lstexibe_titagrupamento = $lstexibe_titagrupamento,
                                                lstexibe_agrupamento_alfabetico = $lstexibe_agrupamento_alfabetico,
                                                lstmsg_totalizador = '$lstmsg_totalizador',
                                                lstmsg_subtotalizador  = '$lstmsg_subtotalizador',
                                                lstmsg_registrosencontrados = '$lstmsg_registrosencontrados',
                                                lstmsg_nenhumresultado = '$lstmsg_nenhumresultado',
                                                lstversao = lstversao + 1,
                                                lstagrupamento_campo = '$lstagrupamento_campo',
                                                lstagrupamento_titulo = '$lstagrupamento_titulo',
                                                lstdescricao = '$lstdescricao',
                                                lstexibe_resultados = $lstexibe_resultados,
                                                lstexclusao = $flstexclusao
                                        WHERE lstoid = $lstoid";
                    $result = pg_query($conn,$sql);
                    if (!$result) { throw new Exception("ERRO: Inserindo listagem."); }
                    $msg = "Registro Atualizado com Sucesso!";
                }
                
                pg_query($conn,"COMMIT;");
            
            } catch (exception $e) {
            	$msg = $e->getMessage();
                pg_query($conn,"ROLLBACK;");
            }
            
        }
        
        $lstsql =  html_entity_decode($lstsql, ENT_QUOTES);
        $lstnome =  html_entity_decode($lstnome, ENT_QUOTES);
        $lstdescricao =  html_entity_decode($lstdescricao, ENT_QUOTES);
        
        $form->adicionarHidden("lstoid",$lstoid);
        $form->adicionarHidden("abaMenu",$abaMenu);
        $form->adicionarHidden("acao","editar");
        $form->adicionarCampo("lstidlistagem","lstidlistagem","ID da Listagem","Identificador da Listagem",$lstidlistagem,true);
        
        $form->adicionarCampo("lstnome","lstnome","Nome:","Nome:",$lstnome,true,"80");
        
        $form->adicionarTextarea("lstdescricao","Descricao:",$lstdescricao,true,"80","8");
        $form->adicionarSubtitulo("Consulta");
        
        $form->adicionarTextarea("lstsql","",$lstsql,true,"120","15");
        
        $form->adicionarSubtitulo("Opcoes de Visualizacao");
        $form->adicionarCheckBox("opcvisualizacao","",$arrOpcVisualizacao,false);

        $form->adicionarSubtitulo("Agrupamento");
        $form->adicionarCampo("lstagrupamento_campo","lstagrupamento_campo","Campo de Agrupamento:","Campo de Agrupamento:",$lstagrupamento_campo,false,"","","Campo que sera usado para agrupar os sub-totalizadores (Nao usar {}).");
        $form->adicionarCampo("lstagrupamento_titulo","lstagrupamento_titulo","Titulo do Agrupamento:","Titulo do Agrupamento:",$lstagrupamento_titulo,false,"50","300","Ex.: Cliente: {clinome}");
        
        $form->adicionarSubtitulo("Mensagens Personalizadas");
        $form->adicionarCampo("lsttitulo","lsttitulo","Titulo:","Titulo:",$lsttitulo,true,"50");
        $form->adicionarCampo("lstmsg_totalizador","lstmsg_totalizador","Mensagem Totalizador:","Mensagem Totalizador:",$lstmsg_totalizador,true);
        $form->adicionarCampo("lstmsg_subtotalizador","lstmsg_subtotalizador","Mensagem Sub-Totalizador:","Mensagem Sub-Totalizador:",$lstmsg_subtotalizador,true);
        $form->adicionarCampo("lstmsg_registrosencontrados","lstmsg_registrosencontrados","Mensagem Registros Encontrados:","Mensagem Registros Encontrados:",$lstmsg_registrosencontrados,true);
        $form->adicionarCampo("lstmsg_nenhumresultado","lstmsg_nenhumresultado","Mensagem Nenhum Resultado:","Mensagem Nenhum Resultado:",$lstmsg_nenhumresultado,true);
        $form->agruparCampos("lstmsg_totalizador,lstmsg_subtotalizador;lstmsg_registrosencontrados,lstmsg_nenhumresultado");

        if ($lstexclusao) {
            $novostatus = "1";
            $titulo = "Ativar";
        } else {
            $novostatus = "0";
            $titulo = "Inativar";
        }
        $form->adicionarHidden("novostatus",$novostatus);

        $form->adicionarQuadro("quadro1","Cadastro de Listagem");
        
        $form->adicionarSubmit("quadro1","btn_cadastrar","Atualizar","atualizar_listagem");
        /*if ($lstoid != "") {
            $form->adicionarSubmit("quadro1","btn_status",$titulo,"alterarStatus");
        }*/

    }
    
if ($msg != "") {
    echo "<script>ExibirMensagem('$msg')</script>";
}
$form->desenhar();

if ($lstoid) {
	echo "<script>bloquear_campo('id_lstidlistagem');</script>";
}
?>