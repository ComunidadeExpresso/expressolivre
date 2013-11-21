{* ARQUIVOS CSS *}
<link rel="stylesheet" type="text/css" href="{$wf_default_resources_path}/default.css">
<link rel="stylesheet" type="text/css" href="{$wf_default_resources_path}/screen.css">
{* ARQUIVOS JS *}
{* componente que faz a ligação entre a camada view e a camada de controle *}
<script type="text/javascript" src="{$wf_js_path}/../scriptaculous/prototype.js"></script>
{literal}
<script type="text/javascript">
function actionDelete()
{
	return (confirm('Deseja realmente remover o registro?'));
}

function dispatch(acaoSolicitada, args)
{
	if (acaoSolicitada == 'Excluir')
		if (!actionDelete())
			return;

	document.forms["workflow_form"].elements["action"].value = acaoSolicitada;
	if (args) {
		document.forms["workflow_form"].elements["params"].value = args;
	}
	document.forms["workflow_form"].submit();
}
</script>
{/literal}
{* CABEÇALHO HTML *}
<span class="titulo">{$activity_title}</span>
<div id="barra_acao_top">
  <table width="100%">
    <tr>
      <td width="150">{wf_redir_menu}</td>
      <td><div id="menu">
          <ul>
          	{* variáveis $inbox e $processes são configuradas no controlador do processo *}
            <li><a href="{$wf_workflow_path}/index.php?start_tab=0" title="Tarefas Pendentes">Tarefas Pendentes</a></li>
            <li><a href="{$wf_workflow_path}/index.php?start_tab=1" title="Executar Processos">Processos</a></li>
          </ul>
        </div></td>
      <td>
      <td width="30"><a href="javascript:history.back()" title="Voltar para página anterior">voltar</a></td>
      <td width="16"><a href="javascript:history.back()"><img src="{$wf_default_resources_path}/icon_voltar.png" border="0"></a></td>
      {* NÃO IMPLEMENTADO!!! *}
	  {if $help ne ""}
	      <td width="30"><a href="#" title="Obter ajuda do sistema">&nbsp;ajuda</a></td>
    	  <td width="16"><a href="#"><img src="{$wf_default_resources_path}/icon_ajuda.png" border="0"></a></td>
	  {/if}
      </td>
    </tr>
  </table>
</div>
<div>

</div>
{* componente de navegação que informa ao controlador mvc qual a ação solicitada *}
<input  type="hidden" name="action" value="">
{* argumentos opcionais enviados para o controlador mvc repassar a camada model *}
<input  type="hidden" name="params" value="">
