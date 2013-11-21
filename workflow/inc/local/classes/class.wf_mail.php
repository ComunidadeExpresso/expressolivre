<?php
require_once(PHPGW_SERVER_ROOT . SEP . 'phpgwapi' . SEP . 'inc' . SEP . 'class.phpmailer.inc.php');

/**
* Permite ao desenvolvedor de processos Workflow enviar e-mails em tempo de execu��o.
* @author Sidnei Augusto Drovetto Junior
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
* @subpackage local
*/
class wf_mail extends PHPMailer
{
	/**
	* @var boolean $validConfig indica se as configura��es de e-mail carregadas s�o v�lidas.
	* @access private
	*/
	var $validConfig;

	/**
	* Construtor do wf_mail.
	* @return object
	* @access public
	*/
	function wf_mail()
	{
		//parent::PHPMailer();
		$this->_init();
	}

	/**
	* Inicializa a classe wf_mail (configura o PHPMailer com as configura��es do processo).
	* @return void
	* @access private
	*/
	private function _init()
	{
		/* carrega as configura��es de processo e do perfil de e-mail */
		$requiredConfiguration = array('mail_smtp_profile' => false);
		$configuration = $GLOBALS['workflow']['wf_runtime']->process->getConfigValues($requiredConfiguration);
		$bo_emailadmin = Factory::getInstance('bo');
		$profileData = $bo_emailadmin->getProfile($configuration['mail_smtp_profile']);

		if (!is_array($profileData))
		{
			$this->validConfig = false;
			return false;
		}
		else
			$this->validConfig = true;

		/* configura os par�metros para envio de e-mail */
		$userLang = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
		/* FIXME: hardcoded 'br' because phpmailer don't use pt-br */
		if ($userLang == 'pt-br')
		{
			$userLang = 'br';
		}
		$langFile = PHPGW_SERVER_ROOT."/phpgwapi/setup/phpmailer.lang-$userLang.php";
		if(file_exists($langFile))
			$this->SetLanguage($userLang, PHPGW_SERVER_ROOT."/phpgwapi/setup/");
		else
			$this->SetLanguage("en", PHPGW_SERVER_ROOT."/phpgwapi/setup/");

		$this->PluginDir = PHPGW_SERVER_ROOT."/phpgwapi/inc/";
		$this->IsSMTP();
		$this->Host = $profileData['smtpServer'];
		$this->Port = $profileData['smtpPort'];
		if ($profile['smtpAuth'])
		{
			$this->SMTPAuth = true;
			$this->Username = $GLOBALS['phpgw_info']['user']['userid'];
			$this->Password = $GLOBALS['phpgw_info']['user']['passwd'];
		}
		$this->Encoding = '8bit';
		$this->AddCustomHeader("X-Mailer: Egroupware Workflow");
		$this->WordWrap = 76;
		$this->IsHTML(true);
	}

	/**
	* Limpa os erros encontrados at� o momento.
	* @return void
	* @access private
	*/
	private function clearErrors()
	{
        $this->error_count = 0;;
        $this->ErrorInfo = null;
	}

	/**
	* Envia um e-mail de acordo com as propriedades da classe.
	* @return bool TRUE em caso de sucesso e FALSE caso contr�rio.
	* @access public
	*/
	function Send()
	{
		/* limpa poss�veis erros (para que outras chamadas ao m�todo n�o influenciem a chamada atual) */
		$this->clearErrors();

		/* checa se as configura��es s�o v�lidas */
		if (!$this->validConfig)
			return false;

		/* envia o e-mail */
		return parent::Send();
	}

	/**
	* Envia um e-mail de acordo com os par�metros passados.
	* @return bool TRUE em caso de sucesso e FALSE caso contr�rio.
	* @param string $from O e-mail de origem (remetente).
	* @param mixed $to Uma string contendo o e-mail do destino (destinat�rio) ou uma array contendo uma lista de destinat�rios.
	* @param string $subject O assunto do e-mail.
	* @param string $body O corpo da mensagem.
	* @access public
	*/
	function quickSend($from, $to, $subject, $body, $fromName = '')
	{
		/* limpa poss�veis erros (para que outras chamadas ao m�todo n�o influenciem a chamada atual) */
		$this->clearErrors();

		/* checa se as configura��es s�o v�lidas */
		if (!$this->validConfig)
			return false;

		if (empty($fromName))
			$fromName = $GLOBALS['workflow']['wf_runtime']->process->getName();

		/* preenche as informa��es para envio */
		$this->FromName = $fromName;
		$this->From = $from;
		$this->AddReplyTo($from);
		$this->Subject = $subject;
		$this->Body = str_replace("\n",'<br />',html_entity_decode($body));
		// se for necess�ria compatibilidade com clientes de email antigos (e.g. mutt) descomente a linha abaixo
		//$this->AltBody = $body;
		$this->ClearAllRecipients();
		if (!is_array($to))
			$to = array($to);
		foreach ($to as $recipient)
			$this->AddAddress($recipient);

		/* envia o e-mail */
		return parent::Send();
	}

	/**
	* Verifica se o recipiente (endere�o de e-mail) est� ok.
	* @param string $recipient O endere�o de e-mail do recipiente.
	* @return mixed true se o recipiente estiver ok, caso o recipiente n�o esteja ok. Ser� retornado null se houver algum problema ao se iniciar uma transa��o com o servidor SMTP.
	* @access public
	*/
	public function checkRecipient($recipient)
	{
        require_once $this->PluginDir . 'class.smtp.php';

		/* tenta se conectar com o servidor SMTP */
        if(!$this->SmtpConnect())
			return false;

		/* estabelece uma transa��o */
		if (!$this->smtp->Mail(''))
			return null;

		/* verifica se o e-mail � v�lido */
		$output = $this->smtp->Recipient($recipient);

		/* finaliza a transa��o */
		$this->smtp->Reset();

		/* retorna a sa�da */
		return $output;
	}
}
?>
