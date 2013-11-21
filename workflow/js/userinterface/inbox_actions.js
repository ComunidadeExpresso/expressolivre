/**
 * Faz uma chamada Ajax para executar uma a��o qualquer
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @param string action A a��o que ser� executada
 * @param string message A mensagem que ser� exibida ap�s a execu��o da a��o solicitada
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
 * Solicita o envio da inst�ncia para a pr�xima atividade
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionSend(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionSend', 'A inst�ncia foi enviada para a pr�xima atividade.');
}

/**
 * Solicita a libera��o de uma inst�ncia
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionRelease(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionRelease', 'O acesso � inst�ncia foi liberado.');
}

/**
 * Solicita a captura de um inst�ncia
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionGrab(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionGrab', 'A inst�ncia foi atribu�da para voc�.');
}

/**
 * Solicita a transforma��o de uma inst�ncia em exce��o
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionException(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionException', 'A inst�ncia foi transformada em exce��o.');
}

/**
 * Solicita a retirada da inst�ncia de exce��o
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionResume(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionResume', 'A inst�ncia foi retirada de exce��o.');
}

/**
 * Solicita que uma inst�ncia seja abortada
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade
 * @return void
 */
function workflowInboxActionAbort(instanceID, activityID)
{
	inboxGeneralAction(instanceID, activityID, 'inboxActionAbort', 'A inst�ncia foi abortada.');
}

/**
 * Solicita a visualiza��o de informa��es sobre a inst�ncia
 * @param int instanceID O ID da inst�ncia
 * @param int activityID O ID da atividade (desnecess�rio)
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