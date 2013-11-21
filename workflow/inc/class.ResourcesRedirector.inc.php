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
 * Classe que redireciona requisições de arquivos da área resources dos processos
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class ResourcesRedirector
{
	/**
	 * @var mixed $processInfo Informações sobre o processo (ou false se o processo não foi encontrado)
	 * @access private
	 */
	private $processInfo;

	/**
	 * @var string $file O nome do arquivo requisitado
	 * @access private
	 */
	private $file;

	/**
	 * @var int $pid O número do processo onde estão os arquivos
	 * @access private
	 */
	private $pid;

	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	public function ResourcesRedirector()
	{
		if (!isset($_SESSION['workflow']['ResourcesRedirector']))
			$_SESSION['workflow']['ResourcesRedirector'] = array(0 => 'workflow');

		$this->pid = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;
		$this->file = $_GET['file'];
		$this->processInfo = false;
		if (!isset($_SESSION['workflow']['ResourcesRedirector'][$this->pid]))
		{
			$result = Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID->query('SELECT wf_normalized_name FROM egw_wf_processes WHERE wf_p_id = ?', array($this->pid));
			if ($result->numRows() != 1)
				return;
			$fields = $result->fetchRow();
			$_SESSION['workflow']['ResourcesRedirector'][$this->pid] = $fields['wf_normalized_name'];
		}
		$this->processInfo = $_SESSION['workflow']['ResourcesRedirector'][$this->pid];
	}

	/**
	 * Redireciona a requisição do arquivo contido no resources do processo para o devido lugar
	 * @return void
	 * @access public
	 */
	public function redirect()
	{
		if ($this->processInfo == false)
			return;

		/* encontra o diretório onde os arquivos estão armazenados */
		$baseDirectory = '';
		if (isset($_SESSION['workflow']['ResourcesRedirector']['baseDirectory']))
		{
			$baseDirectory = $_SESSION['workflow']['ResourcesRedirector']['baseDirectory'];
		}
		else
		{
			if (isset($_SESSION['phpgw_info']['workflow']['vfs_basedir']))
			{
				$baseDirectory = $_SESSION['phpgw_info']['workflow']['vfs_basedir'];
			}
			else
			{
				if (isset($_SESSION['phpgw_info']['expressomail']['server']['files_dir']))
				{
					$baseDirectory = $_SESSION['phpgw_info']['expressomail']['server']['files_dir'];
				}
				else
				{
					/* em último caso, tenta buscar a informação em banco de dados */
					$result = Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID->query('SELECT config_value FROM phpgw_config WHERE config_app = ? AND config_name = ?', array('phpgwapi', 'files_dir'));
					if (empty($result))
						return;
					$fields = $result->fetchRow();
					$baseDirectory = $fields['config_value'];
				}
			}
			$_SESSION['workflow']['ResourcesRedirector']['baseDirectory'] = $baseDirectory;
		}

		if ($this->pid != 0)
			$filename = str_replace('//', '/', $baseDirectory . '/workflow/' . $this->processInfo . '/resources/' . $this->file);
		else
			$filename = str_replace('//', '/', $baseDirectory . '/workflow/' . $this->processInfo . '/' . $this->file);

		if (strpos($filename, '..') !== false)
			return;

		$this->show($filename);
	}

	/**
	 * Serve o arquivo indicado (para o navegador)
	 * @param string $filename Caminho completo do arquivo
	 * @param string $mimeType O tipo mime do arquivo (se não for fornecido, tenta encontrar o tipo de mime a partir do arquivo)
	 * @return void
	 * @access public
	 */
	public function show($filename, $mimeType = null)
	{
		if (!file_exists($filename))
			return;

		/* pega o cabeçalho enviado pelo cliente */
		$headers = apache_request_headers();

		/* verifica se o cliente está validando seu cache e se ele está atualizado */
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($filename)))
		{
			/* o cache do cliente está atualizado. Apenas envia um 304 (Não modificado) */
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true, 304);
		}
		else
		{
			/* verifica se foi fornecido um tipo mime */
			if (is_null($mimeType))
				$mimeType = mime_content_type($filename);

			/* arquivo não está em cache ou o cache já expirou */
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true, 200);
			//Todo: the next line causes delay when downloading files in certains apache configurations
			//header('Content-Length: ' . filesize($filename));
			header('Content-Type: ' . $mimeType);
			header('Content-Disposition: filename="' . basename($filename) . '"');
			echo file_get_contents($filename);
		}
	}
}
?>
