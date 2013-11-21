/* cont�m o e-mail padr�o quando se envia somente um e-mail por usu�rio */
var workflowMonitorEmailUser = '<font color="#FF6666">Aten��o: esta mensagem foi gerada automaticamente pelo sistema e n�o deve ser respondida.</font><br/><br/>\n\
\n\
Caro usu�rio,<br/>\n\
As seguintes atividades de workflow do processo <strong>%processo%</strong> est�o aguardando execu��o em sua caixa de tarefas pendentes. Favor providenciar o andamento:<br/><br/>\n\
%inicio_loop%\n\
Inst�ncia %atual_instancia% de %quantidade_instancia%<br/>\n\
Atividade: %atividade%<br/>\n\
Aguardando por: %tempo_atividade% (desde %inicio_atividade%)<br/>\n\
Identificador: %identificador%<br/>\n\
Link para execu��o: %link%<br/>\n\
<br/><br/>\n\
%fim_loop%\n\
\n\
Atenciosamente,<br/>\n\
Workflow do Expresso';

/* cont�m o e-mail padr�o quando se envia um e-mail por inst�ncia */
var workflowMonitorEmailInstance = '<font color="#FF6666">Aten��o: esta mensagem foi gerada automaticamente pelo sistema e n�o deve ser respondida.</font><br/><br/>\n\
\n\
Caro usu�rio,<br/>\n\
Existe uma atividades de workflow do processo <strong>%processo%</strong> que est� aguardando execu��o em sua caixa de tarefas pendentes. Favor providenciar o andamento:<br/><br/>\n\
\n\
Atividade: %atividade%<br/>\n\
Aguardando por: %tempo_atividade% (desde %inicio_atividade%)<br/>\n\
Identificador: %identificador%<br/>\n\
Link para execu��o: %link%<br/>\n\
<br/>\n\
\n\
Atenciosamente,<br/>\n\
Workflow do Expresso';

/**
 * Constr�i a interface para envio de e-mails (dentro de um elemento LightBox)
 * @return void
 */
function sendMailConfig()
{
	var content = '';
	content += '<div id="emailCompose">';
	content += '<h2>Enviar E-mail</h2>';
	content += '<input type="radio" name="emailType" id="onePerUser" value="1" checked="true" onclick="$(\'emailBody\').value = workflowMonitorEmailUser;"/> <label for="onePerUser">Um e-mail por usu�rio</label>';
	content += '<label><input type="radio" name="emailType" id="onePerInstance" value="1" onclick="$(\'emailBody\').value = workflowMonitorEmailInstance;"/> <label for="onePerInstance">Um e-mail por inst�ncia</label>';
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
	content += '<tr style="color: #FFFFFF; background-color: #000000; font-size: 11px;"><th>Vari�vel</th><th>Significado</th></tr>';
	content += '<tr><td>%atividade%</td><td>O nome da atividade atual</td></tr>'
	content += '<tr><td>%usuario%</td><td>O nome do usu�rio que est� com a inst�ncia</td></tr>'
	content += '<tr><td>%processo%</td><td>O nome do processo</td></tr>'
	content += '<tr><td>%identificador%</td><td>O identificador da inst�ncia</td></tr>'
	content += '<tr><td>%tempo_atividade%</td><td>A dura��o (at� o momento) da atividade atual</td></tr>'
	content += '<tr><td>%tempo_instancia%</td><td>A dura��o (at� o momento) da inst�ncia</td></tr>'
	content += '<tr><td>%inicio_atividade%</td><td>O in�cio da atividade atual</td></tr>'
	content += '<tr><td>%inicio_instancia%</td><td>O in�cio da inst�ncia</td></tr>'
	content += '<tr><td>%quantidade_instancia%</td><td>A quantidade de inst�ncias com o usu�rio</td></tr>'
	content += '<tr><td>%atual_instancia%</td><td>A inst�ncia atual (n�mero em rela��o � quantidade)</td></tr>'
	content += '<tr><td>%link%</td><td>Link para a execu��o da inst�ncia (abre em nova janela)</td></tr>'
	content += '<tr><td>%url%</td><td>Somente a URL para a execu��o da inst�ncia</td></tr>'
	content += '<tr><td>%prioridade%</td><td>A prioridade da inst�ncia</td></tr>'
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
 * Gera uma string contento os par�metros para as chamadas Ajax do envio de e-mails
 * @return string A string de par�metros
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
 * Envia os e-mails para as inst�ncias selecionadas
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
		content += '<br/><font color="#FF0000">Foram enviados ' + data + ' e-mails alertando sobre as inst�ncias.</font>'
		content += '<br/><br/><button onclick="valid.deactivate(); return false;">Fechar</button>';
		$('lbContent').innerHTML = content;

	}

	if (!confirm('Este e-mail ser� enviado para todos os usu�rios listados na tela anterior.\nDeseja continuar?'))
		return;
	var params = commonParams();
	params += '&emailType=' + (($F('onePerUser') == 1) ? 'user' : 'instance');
	params += '&emailBody=' + escape($F('emailBody'));
	params += '&emailSubject=' + escape($F('emailSubject'));
	cExecute('$this.bo_monitors.sendMail', resultSendMail, params);
}

/**
 * Gera um preview dos e-mails que ser�o enviados
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
		content += '<p><strong>N�mero de e-mails que ser�o enviados:</strong> ' + data['emailCount'] + '</p>';
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
 * Remove as inst�ncias finalizadas (completadas ou abortadas) que foram selecionadas
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

		/* define a mensagem que ser� exibida */
		var message = '';
		if (data < 1)
			message = 'Nenhuma inst�ncia foi removida';
		else
			if (data == 1)
				message = 'Uma inst�ncia foi removida';
			else
				message = 'Foram removidas ' + data + ' inst�ncias';
		write_msg(message);

		/* recarrega a lista de inst�ncias */
		filterInstances(true);
	}

	if (!confirm('Todas as inst�ncias listadas nesta tela (inclusive as paginadas) ser�o removidas.\nDeseja continuar?'))
		return;
	var params = commonParams();
	cExecute('$this.bo_monitors.removeCompletedInstances', resultRemoveCompletedInstances, params);
}
