/* contém o e-mail padrão quando se envia somente um e-mail por usuário */
var workflowMonitorEmailUser = '<font color="#FF6666">Atenção: esta mensagem foi gerada automaticamente pelo sistema e não deve ser respondida.</font><br/><br/>\n\
\n\
Caro usuário,<br/>\n\
As seguintes atividades de workflow do processo <strong>%processo%</strong> estão aguardando execução em sua caixa de tarefas pendentes. Favor providenciar o andamento:<br/><br/>\n\
%inicio_loop%\n\
Instância %atual_instancia% de %quantidade_instancia%<br/>\n\
Atividade: %atividade%<br/>\n\
Aguardando por: %tempo_atividade% (desde %inicio_atividade%)<br/>\n\
Identificador: %identificador%<br/>\n\
Link para execução: %link%<br/>\n\
<br/><br/>\n\
%fim_loop%\n\
\n\
Atenciosamente,<br/>\n\
Workflow do Expresso';

/* contém o e-mail padrão quando se envia um e-mail por instância */
var workflowMonitorEmailInstance = '<font color="#FF6666">Atenção: esta mensagem foi gerada automaticamente pelo sistema e não deve ser respondida.</font><br/><br/>\n\
\n\
Caro usuário,<br/>\n\
Existe uma atividades de workflow do processo <strong>%processo%</strong> que está aguardando execução em sua caixa de tarefas pendentes. Favor providenciar o andamento:<br/><br/>\n\
\n\
Atividade: %atividade%<br/>\n\
Aguardando por: %tempo_atividade% (desde %inicio_atividade%)<br/>\n\
Identificador: %identificador%<br/>\n\
Link para execução: %link%<br/>\n\
<br/>\n\
\n\
Atenciosamente,<br/>\n\
Workflow do Expresso';

/**
 * Constrói a interface para envio de e-mails (dentro de um elemento LightBox)
 * @return void
 */
function sendMailConfig()
{
	var content = '';
	content += '<div id="emailCompose">';
	content += '<h2>Enviar E-mail</h2>';
	content += '<input type="radio" name="emailType" id="onePerUser" value="1" checked="true" onclick="$(\'emailBody\').value = workflowMonitorEmailUser;"/> <label for="onePerUser">Um e-mail por usuário</label>';
	content += '<label><input type="radio" name="emailType" id="onePerInstance" value="1" onclick="$(\'emailBody\').value = workflowMonitorEmailInstance;"/> <label for="onePerInstance">Um e-mail por instância</label>';
	content += '<br/><label>Assunto do E-mail <input type="text" id="emailSubject" value="Atividades de Workflow Pendentes" size="40"/></label>';
	content += '<br/>Texto do E-mail:<br/><textarea style="width: 100%; height: 170px;" id="emailBody">' + workflowMonitorEmailUser + '</textarea>';

	content += '<table style="width: 100%">';
	content += '<tr><td style="width: 60%"></td>';
	content += '<td>';

	content += '<button onclick="previewEmail(); return false;">Preview</button>';
	content += '&nbsp;&nbsp;&nbsp;&nbsp;';
	content += '<button onclick="sendMail(); return false;">Enviar</button>';
	content += '&nbsp;&nbsp;&nbsp;&nbsp;';
	content += '<button onclick="valid.deactivate(); return false;">Cancelar</button>';

	content += '</td>';
	content += '</tr>';
	content += '<tr>';
	content += '<td colspan="2">';

	content += '<table style="width: 100%">';
	content += '<tr style="color: #FFFFFF; background-color: #000000; font-size: 11px;"><th>Variável</th><th>Significado</th></tr>';
	content += '<tr><td>%atividade%</td><td>O nome da atividade atual</td></tr>'
	content += '<tr><td>%usuario%</td><td>O nome do usuário que está com a instância</td></tr>'
	content += '<tr><td>%processo%</td><td>O nome do processo</td></tr>'
	content += '<tr><td>%identificador%</td><td>O identificador da instância</td></tr>'
	content += '<tr><td>%tempo_atividade%</td><td>A duração (até o momento) da atividade atual</td></tr>'
	content += '<tr><td>%tempo_instancia%</td><td>A duração (até o momento) da instância</td></tr>'
	content += '<tr><td>%inicio_atividade%</td><td>O início da atividade atual</td></tr>'
	content += '<tr><td>%inicio_instancia%</td><td>O início da instância</td></tr>'
	content += '<tr><td>%quantidade_instancia%</td><td>A quantidade de instâncias com o usuário</td></tr>'
	content += '<tr><td>%atual_instancia%</td><td>A instância atual (número em relação à quantidade)</td></tr>'
	content += '<tr><td>%link%</td><td>Link para a execução da instância (abre em nova janela)</td></tr>'
	content += '<tr><td>%url%</td><td>Somente a URL para a execução da instância</td></tr>'
	content += '<tr><td>%prioridade%</td><td>A prioridade da instância</td></tr>'
	content += '</table>';

	content += '</td>';
	content += '</tr>';
	content += '</table>';
	content += '</div>';

	content += '<div id="emailPreview" style="display: none;"></div>';

	$('lbContent').innerHTML = content;
//	$('emailBody').innerHTML = workflowMonitorEmailUser;
}

/**
 * Gera uma string contento os parâmetros para as chamadas Ajax do envio de e-mails
 * @return string A string de parâmetros
 */
function commonParams()
{
	var output = 'pid=' + workflowMonitorInstancesParams['pid'];

	output += '&p_page=0';

	if (workflowMonitorInstancesParams['filters'])
		output += '&filters=' + workflowMonitorInstancesParams['filters'];

	return output;
}

/**
 * Envia os e-mails para as instâncias selecionadas
 * @return void
 */
function sendMail()
{
	function resultSendMail(data)
	{
		if (data['error'])
		{
			alert(data['error']);
			return;
		}

		var content = '';
		content += '<h2>Resultado de Envio</h2>';
		content += '<br/><font color="#FF0000">Foram enviados ' + data + ' e-mails alertando sobre as instâncias.</font>'
		content += '<br/><br/><button onclick="valid.deactivate(); return false;">Fechar</button>';
		$('lbContent').innerHTML = content;

	}

	if (!confirm('Este e-mail será enviado para todos os usuários listados na tela anterior.\nDeseja continuar?'))
		return;
	var params = commonParams();
	params += '&emailType=' + (($F('onePerUser') == 1) ? 'user' : 'instance');
	params += '&emailBody=' + escape($F('emailBody'));
	params += '&emailSubject=' + escape($F('emailSubject'));
	cExecute('$this.bo_monitors.sendMail', resultSendMail, params);
}

/**
 * Gera um preview dos e-mails que serão enviados
 * @return void
 */
function previewEmail()
{
	function resultPreviewEmail(data)
	{
		if (data['error'])
		{
			alert(data['error']);
			return;
		}

		var content = '<h2>Preview</h2>';
		content += '<p><strong>Número de e-mails que serão enviados:</strong> ' + data['emailCount'] + '</p>';
		content += '<div style="background-color: #FFFFFF; border: 1px solid black; padding: 10px;">' + data['emailBody'] + '</div>';
		content += '<br/><button onclick="$(\'emailPreview\').hide();$(\'emailCompose\').show()"; return false;>Fechar Preview</button>';
		$('emailPreview').innerHTML = content;
		$('emailCompose').hide();
		$('emailPreview').show();
	}
	var params = commonParams();
	params += '&emailType=' + (($F('onePerUser') == 1) ? 'user' : 'instance');
	params += '&emailBody=' + escape($F('emailBody'));
	params += '&emailSubject=' + escape($F('emailSubject'));
	cExecute('$this.bo_monitors.previewEmail', resultPreviewEmail, params);
}

/**
 * Remove as instâncias finalizadas (completadas ou abortadas) que foram selecionadas
 * @return void
 */
function removeCompletedInstances()
{
	function resultRemoveCompletedInstances(data)
	{
		if (data['error'])
		{
			alert(data['error']);
			return;
		}

		/* define a mensagem que será exibida */
		var message = '';
		if (data < 1)
			message = 'Nenhuma instância foi removida';
		else
			if (data == 1)
				message = 'Uma instância foi removida';
			else
				message = 'Foram removidas ' + data + ' instâncias';
		write_msg(message);

		/* recarrega a lista de instâncias */
		filterInstances(true);
	}

	if (!confirm('Todas as instâncias listadas nesta tela (inclusive as paginadas) serão removidas.\nDeseja continuar?'))
		return;
	var params = commonParams();
	cExecute('$this.bo_monitors.removeCompletedInstances', resultRemoveCompletedInstances, params);
}
