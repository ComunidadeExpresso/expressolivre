<?php

$form           = new Formulario("FrmCadListagem");

$acao = $_POST['FrmCadListagem_acao'];
if ($acao == "") { $acao = $_GET['acao']; }
$lstoid = $_POST['lstoid'];
if ($lstoid == "") { $lstoid = $_GET['lstoid']; }

$lstnome = $_POST['lstnome'];

//FORMUL�RIO DE PESQUISA
$form->setAction($BASE_URL);
$form->adicionarCampo("lstnome","lstnome","Procurar por:","Procurar Por",$lstnome,false);
$form->adicionarQuadro("quadro1","Pesquisa");
$form->adicionarSubmit("quadro1","btn_pesquisar","Pesquisar","pesquisar");
$form->adicionarSubmit("quadro1","btn_novo","Novo","editar");


if ($form->isSubmit("pesquisar")) {
//LISTAGEM
$sql = "select 
            lstoid, 
            lstidlistagem, 
            lstnome, 
            lstdescricao, 
            lstexclusao
        from 
            listagem.listagem 
        where 
            lstnome ilike '%$lstnome%' or  
            lstdescricao ilike '%$lstnome%' or 
            lstidlistagem ilike '%$lstnome%' 
        order by 
            lstidlistagem";

$res = pg_query($conn,$sql);

if ($habilitasincronizacao) {
   $addcol = "<td align='center' colspan=2><h3>Sincronizado</h3></td>";
   $addcol .= "<td align='center'><h3>Status</h3></td>";    
}

$tr .= "<table class='tableMoldura'>
                <tr class='tableSubTitulo'><td colspan=6><h2>Listagens</h2></td></tr>
                <tr class='tableTituloColunas'>
                    <td><h3>ID Listagem</h3></td>
                    <td><h3>Nome</h3></td>
                    <td><h3>Descricao</h3></td>
                    <td><h3>Visualizar</h3></td>
                    $addcol
                </tr>";
                
        $qtd = pg_num_rows($res);
        if ($qtd == 0) { $msgres = "Nenhum Resultado Encontrado."; } else { $msgres = "$qtd resultado(s) encontrado(s)."; }
        while ($listagen = pg_fetch_object($res)) {
            
            if ($habilitasincronizacao) {
            
                $xmlversao = verificaVersao(array( "$listagen->lstidlistagem" ));
                $versao = new SimpleXMLElement($xmlversao);
                
                $sincronizado = $versao->listagem[0]->sincronizado;
                
                if ($sincronizado == "1") {
                	$iconsincronizado = "<img src='./images/indicadores/redondos/apf/ap01.jpg'>";
                    $linksincronizado = "";
                } else {
                	$iconsincronizado = "<img src='./images/indicadores/redondos/apf/ap03.jpg'>";
                    $linksincronizado = "<a href='" . $BASE_URL . "&acao=editar&abaMenu=sincronizar&lstoid=" . $listagen->lstoid . "'>[ Sincronizar ]</a>";
                } 
                $exclusao = $listagen->lstexclusao;
                
                if ($exclusao) {
                	$iconstatus = "<span style='color:#990000;'>Inativo</span>";
                } else {
                	$iconstatus = "Ativo";
                }
                
                
                $addcoluna = "<td align='center'>" . $iconsincronizado . "</td><td align='center' nowrap>$linksincronizado</td>";
                
                $addcoluna .= "<td align='center'>" . $iconstatus . "</td>";  
            
            }
            
            $class = ( $class == "tdc" ) ? "tde" : "tdc";
            $tr .= "<tr class='$class'>";
            $tr .= "<td><a href='" . $BASE_URL . "&acao=editar&abaMenu=cadastro&lstoid=" . $listagen->lstoid . "'>" . $listagen->lstidlistagem . "</a></td>";
            $tr .= "<td>" . $listagen->lstnome . "</td>"; 
            $tr .= "<td>" . $listagen->lstdescricao . "</td><td align='center'><a href='$BASE_URL_VIEW&idlistagem=" . $listagen->lstidlistagem . "'>[Visualizar]</a></td>$addcoluna";
            
            $tr .= "</tr>";
        }
        
        $tr .= "<tr class='tableRodapeModelo1'><td colspan=6 align='center'><h3>$msgres</h3></td></tr>";
        $tr .= "</table>";


}
$form->desenhar();


echo $tr;


?>