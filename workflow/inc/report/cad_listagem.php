<?php

$BASE_URL = "index.php?menuaction=workflow.reports.form";
$BASE_URL_VIEW = "index.php?menuaction=workflow.reports.view";
 
$conn = &Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID->_connectionID;
ini_set("soap.wsdl_cache_enabled", "0"); //Limpa o cache

$abaMenu = (isset($_POST['abaMenu'])) ? $_POST['abaMenu'] : $_GET['abaMenu'];
$abaMenu = ($abaMenu != '') ? $abaMenu : 'pesquisa';
$acao = (isset($_POST['acao'])) ? $_POST['acao'] : $_GET['acao'];

$lstoid = (isset($_POST['lstoid'])) ? $_POST['lstoid'] : $_GET['lstoid'];
$FrmCadListagem_acao = (isset($_POST['FrmCadListagem_acao'])) ? $_POST['FrmCadListagem_acao'] : $_GET['FrmCadListagem_acao'];

if ($FrmCadListagem_acao == "editar") { $abaMenu = "cadastro"; }

$habilitasincronizacao = $_SESSION['funcao']['webservice_listagem_sincronizacao'];

?>
<html>
<head>
  <link type="text/css" rel="stylesheet" href="./workflow/inc/report/includes/css/base_form.css">
  <link type="text/css" rel="stylesheet" href="./workflow/inc/report/includes/css/calendar.css">
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/jquery-1.3.2.js"></script>
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/calendar.js"></script>
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/mascaras.js"></script>
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/auxiliares.js"></script>  
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/validacoes.js"></script>
  <script language="Javascript" type="text/javascript" src="./workflow/inc/report/includes/js/FormularioUtil.js"></script>
  <script>
  function mudaAba(aba){
    $id('hidden_id_abaMenu').value = aba;
    document.frm.submit();
  }
  </script>
  <script>
  function ExibirMensagem(msg) {
  	$id('div_msg').innerHTML = msg;
  }
function adiciona_option(idcampo,valor,nome){
    posicao = document.getElementById(idcampo).length;
    document.getElementById(idcampo).options[posicao] = new Option(nome, valor);
}
function remover_options(idcampo){
 tamanho = document.getElementById(idcampo).length;
 for(i=0 ; i<tamanho ; i++){
     document.getElementById(idcampo).options[0] = null;
 }

}
function selecionaTipoIndicador(tipo,imagem) {
	remover_options('id_lsiimagem');
    
    if ((tipo == "Q") || (tipo == "R") || (tipo == "T")) {
    	adiciona_option("id_lsiimagem",'','---');
        adiciona_option("id_lsiimagem",'3' ,'Amarelo - 1');
        adiciona_option("id_lsiimagem",'17','Amarelo - 2');
        adiciona_option("id_lsiimagem",'20','Azul - 1');
        adiciona_option("id_lsiimagem",'10','Azul - 2');
        adiciona_option("id_lsiimagem",'2' ,'Azul - 3');
        adiciona_option("id_lsiimagem",'19','Azul - 4');
        adiciona_option("id_lsiimagem",'22','Cinza - 1');
        adiciona_option("id_lsiimagem",'5' ,'Cinza - 2');
        adiciona_option("id_lsiimagem",'14','Cinza - 3');
        adiciona_option("id_lsiimagem",'18','Laranja - 1');
        adiciona_option("id_lsiimagem",'16','Laranja - 2');
        adiciona_option("id_lsiimagem",'13','Preto');
        adiciona_option("id_lsiimagem",'12','Rosa');
        adiciona_option("id_lsiimagem",'11','Roxo');
        adiciona_option("id_lsiimagem",'21','Verde - 1');
        adiciona_option("id_lsiimagem",'15','Verde - 2');
        adiciona_option("id_lsiimagem",'1' ,'Verde - 3');
        adiciona_option("id_lsiimagem",'4' ,'Vermelho');
    }
    if (tipo == "I") {
        adiciona_option("id_lsiimagem",'','---');
    	adiciona_option("id_lsiimagem",'filePdf','Arquivo de PDF');
        adiciona_option("id_lsiimagem",'fileTxt','Arquivo de Texto');
        adiciona_option("id_lsiimagem",'folhaBranca','Arquivo em Branco');
        adiciona_option("id_lsiimagem",'i_attach','Arquivo Anexo');
        adiciona_option("id_lsiimagem",'lupaMais','Prï¿½-Visualizar (Lupa)');
        adiciona_option("id_lsiimagem",'mais','Sinal Mais');
        adiciona_option("id_lsiimagem",'menos','Sinal Menos');
        adiciona_option("id_lsiimagem",'v','V - Existe');
        adiciona_option("id_lsiimagem",'x','X - Excluido');
        adiciona_option("id_lsiimagem",'x1','X 1 - Excluï¿½do');
        adiciona_option("id_lsiimagem",'pasta1','Pasta 1');
        adiciona_option("id_lsiimagem",'pasta2','Pasta 2');
        adiciona_option("id_lsiimagem",'pasta3','Pasta 3');
    }
    document.getElementById("id_lsiimagem").value = imagem;
}

function liberar_campo(idcampo) {
    document.getElementById(idcampo).style.backgroundColor = '#FFFFFF';
    document.getElementById(idcampo).disabled = "";
}
function bloquear_campo(idcampo) {
	document.getElementById(idcampo).style.backgroundColor = '#E0E0E0';
    document.getElementById(idcampo).disabled = "true";
}
function limpar_campo(idcampo) {
	document.getElementById(idcampo).value = "";
}
function nowrapCheck(checked) {
	  if (checked) { 
        limpar_campo('not_id_lslcwidth'); 
        bloquear_campo('not_id_lslcwidth'); 
      } else { 
        liberar_campo('not_id_lslcwidth'); 
      }
}

  </script>
</head>
<body>
 
<div align="center">
<br />

<table width="98%" class="tableMoldura">
<br>
<tr class="tableTitulo">
    <td><h1>Cadastro de Relatórios</h1></td>
</tr>
<tr>
        <td><br><span id="div_msg" class="msg"><?=$msg?></span></td>
</tr>
<tr>
    <td align="center">
    
    <form action="<?=$_PHP_SELF?>" method="post" name="frm" id="frm">

    <input type="hidden" name="abaMenu" id="hidden_id_abaMenu" value="<?=$abaMenu?>"/>
    <input type="hidden" name="acao" id="hidden_id_abaMenu" value="<?=$acao?>"/>
    <input type="hidden" name="lstoid" id="hidden_id_lstoid" value="<?=$lstoid?>"/>
    
    <table width="98%">
            <tr>
                <td align="left" id="navPrincipal">
        
        <table>
            <tr>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="window.location.href='<?=$BASE_URL?>';" <?php if($abaMenu == 'pesquisa'){ echo 'class="active"'; }?>>Pesquisa</a>
                </td>
                <?php if (($lstoid != "") || ($lstoid == "" && $FrmCadListagem_acao == "editar") ||  ($FrmCadListagem_acao == "atualizar_listagem")) { ?>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('cadastro');" <?php if($abaMenu == 'cadastro'){ echo 'class="active"'; }?>>Cadastro</a>
                </td>
                <?php } ?>
                <?php if (($lstoid != "") ||  ($FrmCadListagem_acao == "atualizar_listagem")) { ?>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('colunas');" <?php if($abaMenu == 'colunas'){ echo 'class="active"'; }?>>Colunas</a>
                </td>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('indicadores');" <?php if($abaMenu == 'indicadores'){ echo 'class="active"'; }?>>Indicadores</a>
                </td>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('parametros');" <?php if($abaMenu == 'parametros'){ echo 'class="active"'; }?>>Parametros</a>
                </td>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('preview');" <?php if($abaMenu == 'preview'){ echo 'class="active"'; }?>>Pre-Visualizacao</a>
                </td>
                <?php if ($habilitasincronizacao) { ?>
                <td align="center" id="tabnav">
                    <a href="javascript:void(null);" onclick="javascript:mudaAba('sincronizar');" <?php if($abaMenu == 'sincronizar'){ echo 'class="active"'; }?>>Sincronizacao</a>
                </td> 
                <?php } ?>
                <?php } ?>
            </tr>
        </table>
        
                </td>
            </tr>
        </table>
    
      </form>
    </td>
</tr>

<tr>
    <td align="center">
    <?
    //echo $abaMenu;
    switch($abaMenu){
        case 'pesquisa' : include 'includes/php/listagem/lst_pesquisa.php'; break;
        case 'cadastro' : include 'includes/php/listagem/lst_cadastro.php'; break;
        case 'colunas' : include 'includes/php/listagem/lst_colunas.php';   break;
        case 'parametros' : include 'includes/php/listagem/lst_parametros.php';   break;
        case 'indicadores' : include 'includes/php/listagem/lst_indicadores.php';   break;
        case 'preview' : include 'includes/php/listagem/lst_preview.php';   break;
        case 'sincronizar' : include 'includes/php/listagem/lst_sincronizacao.php';   break;
    }
    ?>
    </td>
</tr>
</table>
    
</div>

</body>
</html>