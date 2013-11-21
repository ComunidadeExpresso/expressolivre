<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once 'common.inc.php';

/**
 * Classe que redireciona requisições de arquivos de acordo com o template utilizado
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class TemplateServer
{
	/**
	 * @var string $file O nome do arquivo requisitado
	 * @access private
	 */
	private $file;

	/**
	 * @var string $currentTemplate O nome do template em uso
	 * @access private
	 */
	private $currentTemplate;

	/**
	 * @var string $DEFAULT_TEMPLATE O nome do template padrão
	 * @access private
	 */
	private $DEFAULT_TEMPLATE = 'default';

	/**
	 * @var string $FILE_SYSTEM_DIR O caminho (no sistema de arquivos) para o diretório de templates
	 * @access private
	 */
	private $FILE_SYSTEM_DIR;

	/**
	 * @var string $WEB_WORKFLOW_BASE O caminho (na Web) para o diretório do Workflow
	 * @access private
	 */
	private $WEB_WORKFLOW_BASE;

	/**
	 * @var string $WEB_PATH O caminho (na Web) para o diretório de templates
	 * @access private
	 */
	private $WEB_PATH;

	/**
	 * @var array $cache O cache de templates (arquivo => template)
	 * @access private
	 */
	private $cache;

	/**
	 * @var int $CACHE_SIZE O tamanho do cache
	 * @access private
	 */
	private $CACHE_SIZE = 100;

	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	public function TemplateServer()
	{
		if (!isset($_SESSION['workflow']['TemplateServer']['cache']))
			$_SESSION['workflow']['TemplateServer']['cache'] = array();
		$this->cache = &$_SESSION['workflow']['TemplateServer']['cache'];

		/* encontra o template atualmente em uso */
		if (isset($_SESSION['workflow']['TemplateServer']['templateSet']))
		{
			$this->currentTemplate = $_SESSION['workflow']['TemplateServer']['templateSet'];
		}
		else
		{
			if (isset($_SESSION['phpgw_info']['expresso']['server']['template_set']))
			{
				$this->currentTemplate = $_SESSION['phpgw_info']['expresso']['server']['template_set'];
			}
			else
			{
				if (isset($GLOBALS['phpgw_info']['login_template_set']))
				{
					$this->currentTemplate = $GLOBALS['phpgw_info']['login_template_set'];
				}
				else
				{
					Factory::getInstance('WorkflowMacro')->prepareEnvironment();
					if (!isset($GLOBALS['phpgw_info']['login_template_set']))
						return false;
					$this->currentTemplate = $GLOBALS['phpgw_info']['login_template_set'];
				}
			}
			$_SESSION['workflow']['TemplateServer']['templateSet'] = $this->currentTemplate;
		}

		$this->file = $_GET['file'];
		$this->FILE_SYSTEM_DIR = dirname(__FILE__) . '/../templates';

		/* tenta carregar o endereço Web do Workflow */
		if (isset($_SESSION['workflow']['TemplateServer']['workflowBase']))
		{
			$this->WEB_WORKFLOW_BASE = $_SESSION['workflow']['TemplateServer']['workflowBase'];
		}
		else
		{
			if (isset($GLOBALS['phpgw_info']['server']) && is_array($GLOBALS['phpgw_info']['server']) && array_key_exists('webserver_url', $GLOBALS['phpgw_info']['server']))
			{
				$this->WEB_WORKFLOW_BASE = ((string) $GLOBALS['phpgw_info']['server']['webserver_url']) . '/workflow';
			}
			else
			{
				if (isset($_SESSION['phpgw_info']['workflow']['server']) && is_array($_SESSION['phpgw_info']['workflow']['server']) && array_key_exists('webserver_url', $_SESSION['phpgw_info']['workflow']['server']))
				{
					$this->WEB_WORKFLOW_BASE = ((string) $_SESSION['phpgw_info']['workflow']['server']['webserver_url']) . '/workflow';
				}
				else
				{
					/* se não for encontrado em nenhuma variável de ambiente, tenta carregar do banco de dados */
					$webServerURL = (string) Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID->GetOne('SELECT config_value FROM phpgw_config WHERE config_app = ? AND config_name = ?', array('phpgwapi', 'webserver_url'));
					$this->WEB_WORKFLOW_BASE = str_replace('//', '/', "{$webServerURL}/workflow");
				}
			}
			$_SESSION['workflow']['TemplateServer']['workflowBase'] = $this->WEB_WORKFLOW_BASE;
		}
		$this->WEB_PATH = $this->WEB_WORKFLOW_BASE . '/templates';
	}

	/**
	 * Redireciona a requisição para o arquivo do template adequado
	 * @return void
	 * @access public
	 */
	public function redirect()
	{
		if (strpos($this->file, '..') !== false)
			return false;

		if (($selectedTemplate = $this->getTemplateForFile($this->file)) === false)
			return false;

		$filename = $this->getSystemFile($this->file, $selectedTemplate);
		$webFile = $this->getWebFile($this->file, $selectedTemplate);

		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($filename)))
		{
			/* o cache do cliente está atualizado. Apenas envia um 304 (Não modificado) */
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true, 304);
		}
		else
		{
			header("Location: {$webFile}");
			exit;
		}

	}

	/**
	 * Encontra o template para o arquivo solicitado
	 * @param string $file O nome do arquivo
	 * @return string O nome do template que contém o arquivo (será 'default' ou o nome do template em uso)
	 * @access public
	 */
	public function getTemplateForFile($file)
	{
		if (!is_null(($output = $this->getCache($file))))
			return $output;

		$output = false;
		if (file_exists($filename = "{$this->FILE_SYSTEM_DIR}/{$this->currentTemplate}/{$file}"))
			$output = $this->currentTemplate;
		else
			if (file_exists($filename = "{$this->FILE_SYSTEM_DIR}/{$this->DEFAULT_TEMPLATE}/{$file}"))
				$output = $this->DEFAULT_TEMPLATE;

		$this->setCache($file, $output);
		return $output;
	}

	/**
	 * Encontra o endereço (Web) do arquivo
	 * @param string $file O nome do arquivo
	 * @param string $template O template do arquivo (se não for passado, a classe tentará encontrar o template adeqüado)
	 * @return string O endereço (Web) do arquivo
	 * @access public
	 */
	public function getWebFile($file, $template = null)
	{
		if (is_null($template))
			if (($template = $this->getTemplateForFile($file)) === false)
				return false;

		return str_replace('//', '/', "{$this->WEB_PATH}/{$template}/{$file}");
	}

	/**
	 * Encontra o endereço (no sistema de arquivos) do arquivo
	 * @param string $file O nome do arquivo
	 * @param string $template O template do arquivo (se não for passado, a classe tentará encontrar o template adeqüado)
	 * @return string O endereço (no sistema de arquivos) do arquivo
	 * @access public
	 */
	public function getSystemFile($file, $template = null)
	{
		if (is_null($template))
			if (($template = $this->getTemplateForFile($file)) === false)
				return false;

		return "{$this->FILE_SYSTEM_DIR}/{$template}/{$file}";
	}

	/**
	 * Gera um link Web, através do servidor de templates (esta classe), para o arquivo informado
	 * @param string $file O nome do arquivo
	 * @return string O endereço do arquivo
	 * @access public
	 */
	public function generateLink($file)
	{
		return "{$this->WEB_WORKFLOW_BASE}/templateFile.php?file={$file}";
	}

	/**
	 * Gera um link Web, através do servidor de templates (esta classe), para a imagem informada
	 * @param string $file O nome da imagem
	 * @return string O endereço do arquivo
	 * @access public
	 */
	public function generateImageLink($file)
	{
		return $this->generateLink("images/{$file}");
	}

	/**
	 * Define um elemento no cache
	 * @param string $key O nome da chave do cache
	 * @param mixed $value O valor que será armazenado no cache
	 * @return void
	 * @access public
	 */
	private function setCache($key, $value)
	{
		if (isset($this->cache[$key]))
			unset($this->cache[$key]);

		$this->cache[$key] = $value;

		if (count($this->cache) > $this->CACHE_SIZE)
			array_shift($this->cache);
	}

	/**
	 * Busca um elemento do cache
	 * @param string $key O nome da chave do cache
	 * @return mixed O valor que está armazenado no cache. Caso não seja encontrado, será retornado null
	 * @return void
	 * @access public
	 */
	private function getCache($key)
	{
		if (!isset($this->cache[$key]))
			return null;

		/* assegura que o elemento buscado fique em último lugar da array e, assim, garantindo que ficará mais tempo em cache (princípio da temporalidade) */
		$output = $this->cache[$key];
		unset($this->cache[$key]);
		$this->cache[$key] = $output;

		return $output;
	}
}
?>
