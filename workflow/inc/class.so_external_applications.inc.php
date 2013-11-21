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

/**
 * Camada Model das Aplica��es Externas
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_external_applications
{
	/**
	 * @var bool True se o usu�rio for administrador do expresso
	 * @access private
	 */
	private $isAdmin;

	/**
	 * @var object Link para a ACL do Workflow
	 * @access private
	 */
	private $acl;

	/**
	 * @var object Link para o Banco de Dados do Expresso
	 * @access private
	 */
	private $db;

	/**
	 * @var object Link para o Banco de Dados do Expresso
	 * @access private
	 */
	private $EXTERNAL_APPLICATION_PATH;

	/**
	 * Checa se o usu�rio possui acesso � administra��o das Aplica��es Externas
	 * @return void
	 * @access private
	 */
	private function _checkAccess()
	{
		/* the user is an administrator */
		if ($this->isAdmin)
			return true;
		else
			die(serialize("Voc� n�o tem permiss�o para executar este procedimento!"));
	}

	/**
	 * Verifica se houve erro em alguma query do Banco de Dados
	 * @param object $result O resultado de alguma query
	 * @return void
	 * @access private
	 */
	private function _checkError($result)
	{
		if ($result === false)
			die(serialize("Ocorreu um erro ao se tentar executar a opera��o solicitada."));
	}

	/**
	 * Grava a imagem no diret�rio de aplica��es externas
	 * @param string $filename O nome da imagem
	 * @param string $contents O conte�do (bin�rio) da imagem
	 * @return bool Informa se a imagem foi gravada com sucesso ou n�o
	 * @access private
	 */
	private function _saveImage($filename, $contents)
	{
		/* avoid writes outside the external_applications directory */
		if (strpos($filename, '/') !== false)
			return false;

		/* create the directories if they don't exist */
		@mkdir($this->EXTERNAL_APPLICATION_PATH, 0770, true);

		/* perform the write operations */
		$handler = fopen($this->EXTERNAL_APPLICATION_PATH . '/' . $filename, 'w');
		if ($handler)
		{
			fwrite($handler, $contents);
			fclose($handler);
			return true;
		}
		else
			return false;
	}

	/**
	 * Verifica a presen�a de valores inv�lidos em alguns campos da aplica��o externa
	 * @param string $name O nome da aplica��o externa
	 * @param string $address O endere�o da aplica��o externa
	 * @return array Uma array contendo os erros encontrados
	 * @access private
	 */
	private function checkExternalApplicationData($name, $address)
	{
		$output = array();
		$name = trim($name);
		$address = trim($address);

		if (empty($name))
			$output[] = 'O nome da aplica��o externa n�o pode ser vazio.';

		if (empty($address))
			$output[] = 'O endere�o da aplica��o externa n�o pode ser vazio.';

		if (preg_match('/^[a-z]+:\/\//i', $address) == 0)
			$output[] = 'Aparentemente a URL informada n�o est� formatada corretamente.';

		return $output;
	}

	/**
	 * Construtor da classe so_external_applications
	 * @return object
	 */
	function so_external_applications()
	{
		$this->isAdmin = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$this->acl = &$GLOBALS['ajax']->acl;
		$this->db =& Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$this->EXTERNAL_APPLICATION_PATH = $_SESSION['phpgw_info']['workflow']['server']['files_dir'] . '/workflow//workflow/external_applications';
	}

	/**
	 * Lista todas as aplica��es externas
	 * @return array Lista de aplica��es externas
	 * @access public
	 */
	function getExternalApplications()
	{
		$this->_checkAccess();
		$query = "SELECT external_application_id, name FROM egw_wf_external_application ORDER BY name";

		$result = $this->db->query($query);
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Retornar informa��es sobre uma aplica��o externa
	 * @param int $externalApplicationID O ID da aplica��o externa
	 * @return array Array contento informa��es sobre a aplica��o externa
	 * @access public
	 */
	function getExternalApplication($externalApplicationID)
	{
		$this->_checkAccess();
		$query = "SELECT external_application_id, name, description, image, address, authentication, post, intranet_only FROM egw_wf_external_application WHERE (external_application_id = ?)";

		$result = $this->db->query($query, array($externalApplicationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return isset($output[0]) ? $output[0] : false;
	}

	/**
	 * Adiciona uma aplica��o externa
	 * @param string $name O nome da aplica��o externa
	 * @param string $description A descri��o da aplica��o externa
	 * @param string $address O endere�o da aplica��o externa
	 * @param string $image O nome da imagem da aplica��o externa
	 * @param int $authentication Indica se a aplica��o externa autentica (1) ou n�o (0)
	 * @param string $post Os dados que s�o postados para a aplica��o externa (caso necessite de autentica��o)
	 * @param int $intranetOnly Indica se a aplica��o externa s� ser� vis�vel na Intranet (1 somente Intranet e 2 para cliente de qualquer origem)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function addExternalApplication($name, $description, $address, $image, $authentication, $post, $intranetOnly)
	{
		$this->_checkAccess();

		if (count($errors = $this->checkExternalApplicationData($name, $address)) > 0)
			return array('error' => implode("\n", $errors));

		/* decode the supplied image */
		if ($image != '')
		{
			$image = str_replace(' ', '+', $image);
			$imageData = base64_decode($image);
			if ($imageData !== false)
				$imageData = unserialize($imageData);
			if ($imageData !== false)
			{
				$image = strtolower(end(explode('.', $imageData['name'])));
				if (($image != 'png') && ($image != 'jpg') && ($image != 'gif'))
					return array('error' => 'A imagem enviada n�o � do tipo JPG, PNG ou GIF');
			}
			else
				$image = '';
		}

		if (strlen($image) > 0)
			$query = "INSERT INTO egw_wf_external_application(name, description, address, image, authentication, post, intranet_only) VALUES(?, ?, ?, currVAL('seq_egw_wf_external_application') || '.$image', ?, ?, ?)";
		else
			$query = "INSERT INTO egw_wf_external_application(name, description, address, authentication, post, intranet_only) VALUES(?, ?, ?, ?, ?, ?)";

		$this->db->StartTrans();
		$result = $this->db->query($query, array($name, $description, $address, $authentication, $post, $intranetOnly));

		if ((strlen($image) > 0) && ($result !== false))
		{
			$currentID = $this->db->getOne("SELECT currVAL('seq_egw_wf_external_application')");
			$this->_saveImage($currentID . '.' . $image, $imageData['contents']);
		}

		if ($result === false)
			$this->db->FailTrans();
		else
			$this->db->CompleteTrans();

		$this->_checkError($result);
		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza uma aplica��o externa
	 * @param int $externalApplicationID O ID da aplica��o externa
	 * @param string $name O nome da aplica��o externa
	 * @param string $description A descri��o da aplica��o externa
	 * @param string $address O endere�o da aplica��o externa
	 * @param string $image O nome da imagem da aplica��o externa
	 * @param int $authentication Indica se a aplica��o externa autentica (1) ou n�o (0)
	 * @param string $post Os dados que s�o postados para a aplica��o externa (caso necessite de autentica��o)
	 * @param int $removeCurrentImage Indica se a imagem atual da aplica��o externa ser� removida (1 para remover e 0 para n�o remover)
	 * @param int $intranetOnly Indica se a aplica��o externa s� ser� vis�vel na Intranet (1 somente Intranet e 2 para cliente de qualquer origem)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function updateExternalApplication($externalApplicationID, $name, $description, $address, $image, $authentication, $post, $removeCurrentImage, $intranetOnly)
	{
		$this->_checkAccess();

		if (count($errors = $this->checkExternalApplicationData($name, $address)) > 0)
			return array('error' => implode("\n", $errors));

		/* if a new image is supplied, decode the data */
		if ($image != '')
		{
			$image = str_replace(' ', '+', $image);
			$imageData = base64_decode($image);
			if ($imageData !== false)
				$imageData = unserialize($imageData);
			if ($imageData !== false)
			{
				$image = strtolower(end(explode('.', $imageData['name'])));
				if (($image != 'png') && ($image != 'jpg') && ($image != 'gif'))
					return array('error' => 'A imagem enviada n�o � do tipo JPG, PNG ou GIF');
				$image = $externalApplicationID . '.' . $image;
			}
			else
				$image = null;
		}
		else
			$image = null;

		/* get the current image */
		$currentImage = $this->db->getOne('SELECT image FROM egw_wf_external_application WHERE (external_application_id = ?)', array($externalApplicationID));

		/* if necessary, remove the current image */
		if ((($removeCurrentImage == '1') || (!is_null($image))) && ($currentImage))
			if (file_exists($this->EXTERNAL_APPLICATION_PATH . '/' . $currentImage))
				unlink($this->EXTERNAL_APPLICATION_PATH . '/' . $currentImage);

		/* if supplied, save the new image */
		if (!is_null($image))
			$this->_saveImage($image, $imageData['contents']);
		else
			if ($removeCurrentImage == '0')
				$image = $currentImage;

		/* update the external application */
		$query = "UPDATE egw_wf_external_application SET name = ?, description = ?, address = ?, image = ?, authentication = ?, post = ?, intranet_only = ? WHERE (external_application_id = ?)";
		$result = $this->db->query($query, array($name, $description, $address, $image, $authentication, $post, $intranetOnly, $externalApplicationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove uma aplica��o externa
	 * @param int $externalApplicationID O ID da aplica��o externa.
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeExternalApplication($externalApplicationID)
	{
		$this->_checkAccess();

		/* remove the current image */
		$currentImage = $this->db->getOne('SELECT image FROM egw_wf_external_application WHERE (external_application_id = ?)', array($externalApplicationID));
		if ($currentImage)
			if (file_exists($this->EXTERNAL_APPLICATION_PATH . '/' . $currentImage))
				unlink($this->EXTERNAL_APPLICATION_PATH . '/' . $currentImage);

		/* remove the external application */
		$result = $this->db->query('DELETE FROM egw_wf_external_application WHERE (external_application_id = ?)', array($externalApplicationID));
		$this->_checkError($result);

		$this->acl->removeAdminsFromResource('APX', $externalApplicationID);

		return (($result === false) ? false : true);
	}
}
?>
