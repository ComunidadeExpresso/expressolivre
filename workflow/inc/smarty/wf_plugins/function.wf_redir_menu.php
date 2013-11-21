<?php
/**
 * Este plugin Smarty irá gerar uma lista com as atividades start e standalone,
 * do processo em execução, em formato de caixa de seleção. Útil para colocar no
 * formulário de uma atividade interativa, para que os usuários tenham opção de
 * mover para outras atividades, sem ter que retornar para a interface de usuário, aba "processos".
 * @package Smarty
 * @subpackage wf_plugins
 * @author Mauricio Luiz Viani
 * @param array $params Array de parametros
 * - label : (opcional) uma string que será usada como cabeçalho de campo.
 * @param object &$smarty Instância do objeto smarty em uso
 * @return string codigo que insere o selectbox
 * @access public
 */
function smarty_function_wf_redir_menu($params, &$smarty)
{
 	$defaultValues = array(
		'label' => '');
	$extractParams = array(
		'label');

	/* verifica se todos os parâmetros obrigatórios foram passados */
	foreach ($defaultValues as $key => $value)
		if (!isset($params[$key]))
			$params[$key] = $value;

	/* atribui valores default para os parâmetros não passados */
	foreach ($extractParams as $extract)
		$$extract = $params[$extract];

    $base_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
	$user_activities = $GLOBALS['workflow']['wf_user_activities']['data'];
	$pid = $GLOBALS['workflow']['wf_process_id'];

	$select_tag = "<select name=\"redir_menu\" id=\"redir_menu\" onchange=\"redir_link();\" \"print_result=false;\">";
	$select_tag .= "<option value=\"0\">------- Atividades -------</option>";
	$user_activities = $GLOBALS['workflow']['wf_user_activities']['data'];
	$pid = $GLOBALS['workflow']['wf_process_id'];

	foreach($user_activities as $key => $line) {
		if ($line['wf_p_id'] === $pid) {
			if ($line['wf_menu_path'] != '!')
				$select_tag .= "<option value=\"" . $line['wf_activity_id'] . "\">". $line['wf_name'] . "</option>";
		}
	}

	$select_tag .= "<option value=\"0\">------- Interfaces -------</option>";
	$select_tag .= "<option value=\"ce\">Tarefas Pendentes</option>";
	$select_tag .= "<option value=\"pr\">Processos</option>";
	$select_tag .= "<option value=\"ac\">Acompanhamento</option>";
	$select_tag .= "<option value=\"ap\">Aplicações Externas</option>";
	$select_tag .= "<option value=\"og\">Organograma</option>";

	$user_is_admin = Factory::getInstance('workflow_acl')->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']);
	if ($user_is_admin || ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow'))) {
		$select_tag .= "<option value=\"ad\">Administração</option>";
	}
	if ($user_is_admin ||  ($GLOBALS['phpgw']->acl->check('monitor_workflow',1,'workflow'))) {
		$select_tag .= "<option value=\"mo\">Monitoramento</option>";
	}

	$select_tag .= "</select>";

	$output = '<table border="0" align="right"><tr><td>' . $label . '</td><td>';
	$output .= $select_tag . "</td></tr></table>";
	$output .= <<<EOF
	<script language="javascript1.2">
		function redir_link() {
			elem = document.getElementById("redir_menu");
			activity = elem.options[elem.options.selectedIndex].value;
			switch (activity){
   				case '0' :
      				break;
   				case 'ce' :
			   		location.href = "$base_url/workflow/index.php?start_tab=0";
      				break;
   				case 'pr' :
      				location.href = "$base_url/workflow/index.php?start_tab=1";
      				break;
   				case 'ac' :
      				location.href = "$base_url/workflow/index.php?start_tab=2";
      				break;
   				case 'ap' :
      				location.href = "$base_url/workflow/index.php?start_tab=3";
      				break;
   				case 'og' :
      				location.href = "$base_url/workflow/index.php?start_tab=4";
      				break;
      			case 'ad' :
      				location.href = "$base_url/index.php?menuaction=workflow.ui_adminprocesses.form";
      				break;
				case 'mo' :
      				location.href = "$base_url/index.php?menuaction=workflow.ui_monitors.form";
      				break;
			    default :
			   		location.href = "$base_url/index.php?menuaction=workflow.run_activity.go&activity_id=" + activity;
			}
		}
	</script>
EOF;
	return $output;
}
?>
