<?php

$form           = new Formulario("FrmCadListagem");
$form->setAction($BASE_URL);
$listagem       = new Listagem("listResultado","Par�metros",$conn);
$listagem->setUrlBasePath($GLOBALS['phpgw_info']['server']['webserver_url'] . "/workflow/inc/report");


$lspoid = (isset($_POST['lspoid'])) ? $_POST['lspoid'] : $_GET['lspoid'];

if ($acao == "excluir") {
    $sql = "delete from listagem.listagem_parametro where lspoid = $lspoid ";
    pg_query($conn,$sql);
    
    //ATUALIZA A VERS�O DA LISTAGEM PARA MANTER O PROCESSO DE SINCRONIZA��O
    atualizarVersao($conn,$lstoid);
    $msg = "Par�metro Exclu�do com sucesso!";
}

if ($form->isSubmit("atualizar_parametro",true)) {
    
    $lspidparametro         = $_POST['lspidparametro'];
    $lspidparametro = str_replace("{","",$lspidparametro);
    $lspidparametro = str_replace("}","",$lspidparametro);
    
    $lsptitulo              = $_POST['lsptitulo'];
    $lspvalor_padrao        = $_POST['lspvalor_padrao'];
    $lsptipo                = $_POST['lsptipo'];
    
    $lspobrigatorio = (isset($_POST['lspobrigatorio'])) ? true: false;

    $flspobrigatorio = ($lspobrigatorio) ?  "'t'" : "'f'"; 
    
    if ($lspoid == "") {
        
        $sqlver = "select lspoid from listagem.listagem_parametro where lspidparametro = '$lspidparametro' and lsplstoid = '$lstoid'";
        $res = pg_query($sqlver);
        $qtd = pg_num_rows($res);
        
        if ($qtd == 0) {
            $sql = "insert into 
                        listagem.listagem_parametro ( 
                        
                        lsplstoid,
                        lspidparametro,
                        lsptipo,
                        lspvalor_padrao,
                        lspobrigatorio,
                        lsptitulo
                    ) values (
                        $lstoid,
                        '$lspidparametro',
                        '$lsptipo',
                        '$lspvalor_padrao',
                        $flspobrigatorio,
                        '$lsptitulo'
                    );";
            $res = pg_query($conn,$sql);
            
            //ATUALIZA A VERS�O DA LISTAGEM PARA MANTER O PROCESSO DE SINCRONIZA��O
            atualizarVersao($conn,$lstoid);
            
            $msg=  "Coluna adicionada com Sucesso!";
        } else {
            $msg = "Identificador de Coluna j� est� adicionado a essa listagem.";
        }
        
    } else {
        $sql = "update 
                    listagem.listagem_parametro 
                set lspidparametro = '$lspidparametro',
                    lsptitulo = '$lsptitulo',  
                    lsptipo='$lsptipo', 
                    lspvalor_padrao = '$lspvalor_padrao', 
                    lspobrigatorio = $flspobrigatorio
                where lspoid = $lspoid ";
                
       //ATUALIZA A VERS�O DA LISTAGEM PARA MANTER O PROCESSO DE SINCRONIZA��O
       atualizarVersao($conn,$lstoid);
       
       $res = pg_query($conn,$sql);
       $msg=  "Coluna atualizada com Sucesso!";
       $lspoid = "";
       $acao = "editar";
    }
}

if ($acao == "editar") {
    if ($lspoid != "") {
        $sql = "select * from listagem.listagem_parametro where lspoid = $lspoid ";
        $res = pg_query($sql);
        $dados = pg_fetch_object($res);
        
        $lspidparametro = $dados->lspidparametro;
        $lspvalor_padrao =  $dados->lspvalor_padrao;
        $lsptitulo =  $dados->lsptitulo;
        $lsptipo = $dados->lsptipo;
        $lspobrigatorio = ($dados->lspobrigatorio == "t") ? true: false;

    } else {
        $lspidparametro = "";
        $lspvalor_padrao = "";
        $lsptipo = "";
        $lsptitulo = "";
        $lspobrigatorio = false;
        
    }
}


if(isset($lstoid{0})){

    $form->adicionarHidden("lstoid",$lstoid); 
    $form->adicionarHidden("lspoid",$lspoid); 
    $form->adicionarHidden("abaMenu",$abaMenu);
    $form->adicionarHidden("acao","editar");
    $form->adicionarCampo("lspidparametro","lspidparametro","ID do Par�metro:","Identificador da Coluna",$lspidparametro,true,"20","","Identificador do Par�metro. (N�o usar {}).");
    $form->adicionarCampo("lsptitulo","lsptitulo","T�tulo do Par�metro:","T�tulo do Par�metro",$lsptitulo,true,"20","","");
    
    $arrTipoColunas = array ( ''      => '---' ,
                              'text'      => 'Texto' ,
                              'int'       => 'N�mero' ,
                              'data'      => 'Data' 
           );
    $form->adicionarSelect("lsptipo","Tipo:","Tipo do Par�metro",$lsptipo,$arrTipoColunas,true);
    
    $form->adicionarCampo("lspvalor_padrao","lspvalor_padrao","Valor para Testes:","Valor para Testes",$lspvalor_padrao,false,"","","Valor usado nos testes para faciltar a consulta.");
    
    $arrCheckBlank[] = array('t', ' Par�metro Obrigat�rio.', $lspobrigatorio);
    $form->adicionarCheckBox("lspobrigatorio","",$arrCheckBlank,false);
    
      
    $form->adicionarQuadro("quadro2","Cadastro de Par�metros");
    $form->adicionarSubmit("quadro2","btn_cadastrar","Atualizar","atualizar_parametro");
    
    

    //LISTAGEM DE PAR�METROS
    $sql = "SELECT *, case when lsptipo = 'int' then 'N�mero' when lsptipo = 'text' then 'Texto' when lsptipo = 'data' then 'Data' end as novo_tipo FROM listagem.listagem_parametro WHERE lsplstoid=$lstoid order by lspoid";
    $listagem->carregar($sql);
    
    $listagem->adicionarIndicador("indobrigatorio","({lspobrigatorio} == 't')","I","v","Par�metro Obrigat�rio");
    
    $listagem->adicionarColuna("lspidparametro","Par�metro","{lspidparametro}","text","left","200px");
    $listagem->adicionarColuna("lsptitulo","T�tulo","{lsptitulo}","text","left","200px");
    $listagem->adicionarColuna("lsptipo","Tipo","{novo_tipo}","text","left","200px");
    $listagem->adicionarColuna("lspvalor_padrao","Valor de testes","{lspvalor_padrao}","text","left","200px");
    $listagem->adicionarColuna("lspobrigatorio","Obrigat�rio","{indobrigatorio}","text","center","50px");
    $listagem->adicionarColuna("lspoid","Excluir","[Excluir]","text","center","50px");
    $listagem->adicionarLink("lspoid",$BASE_URL . "&abaMenu=parametros&lstoid=$lstoid&lspoid={lspoid}&acao=excluir");
    $listagem->adicionarLink("lspidparametro",$BASE_URL . "&abaMenu=parametros&lstoid=$lstoid&lspoid={lspoid}&acao=editar");
    $listagem->setMensagemRegistrosEncontrados("par�metros(s) cadastrado(s)");
    $listagem->setMostrarLegendaTopo(false);
    $listagem->setMostrarLegendaRodape(true);
}

if ($msg != "") {
    echo "<script>ExibirMensagem('$msg')</script>";
}
$form->desenhar();
$listagem->desenhar();
echo "<div id='div_result_colunas'></div>";
?>