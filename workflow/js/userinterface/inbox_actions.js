/**
 * Faz uma chamada Ajax para executar uma ação qualquer
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @param string action A ação que será executada
 * @param string message A mensagem que será exibida após a execução da ação solicitada
 * @return void
 */
function inboxGeneralAction(instanceID, activityID, action, message)
{
	function actionHandler(data)
	{
		if (_checkError(data))
			return false;

		if (data == true)
		{
			write_msg(message);
			draw_inbox_folder(workflowInboxParams['p_page'], workflowInboxParams['sort'], workflowInboxParams['pid'], workflowInboxParams['search_term']);
		}
	};

	cExecute ('$this.bo_userinterface.' + action, actionHandler, 'instanceID=' + instanceID + "&activityID=" + activityID);
}

/**
 * Solicita o envio da instância para a próxima atividade
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionSend(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionSend', 'A instância foi enviada para a próxima atividade.');
}

/**
 * Solicita a liberação de uma instância
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionRelease(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionRelease', 'O acesso à instância foi liberado.');
}

/**
 * Solicita a captura de um instância
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionGrab(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionGrab', 'A instância foi atribuída para você.');
}

/**
 * Solicita a transformação de uma instância em exceção
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionException(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionException', 'A instância foi transformada em exceção.');
}

/**
 * Solicita a retirada da instância de exceção
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionResume(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionResume', 'A instância foi retirada de exceção.');
}

/**
 * Solicita que uma instância seja abortada
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionAbort(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionAbort', 'A instância foi abortada.');
}

/**
 * Solicita a visualização de informações sobre a instância
 * @param int instanceID O ID da instância
 * @param int activityID O ID da atividade (desnecessário)
 * @return void
 */
function workflowInboxActionView(instanceID, activityID)
{
	function viewHandler(data)
	{
		if (_checkError(data))
			return false;
		drawViewInstance(data);
	};
	cExecute ("$this.bo_userinterface.inboxActionView", viewHandler, 'instanceID=' + instanceID);
}