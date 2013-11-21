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
 * Classe que provê as fotos dos usuários do LDAP
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class UserPictureProvider
{
	/**
	 * @var int $userID O ID do usuário cuja foto será exibida
	 * @access private
	 */
	private $userID = false;

	/**
	* @var int $cacheDays O número de dias antes da imagem ser renovada
	* @access private
	*/
	private $cacheDays = 5;

	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	public function UserPictureProvider()
	{
		if (isset($_GET['userID']))
			if (is_numeric($_GET['userID']))
				$this->userID = (int) $_GET['userID'];
	}

	/**
	 * Redimensiona uma imagem de acordo com o tamanho padrão (hardcoded 60x80)
	 * @param resource $image Um resource do tipo image (da imagem de entrada)
	 * @return resource Um resource do tipo image (da imagem redimensionada)
	 * @access private
	 */
	private function resizeImage($image)
	{
		/* thumbnail dimensions */
		$thumbWidth = 60;
		$thumbHeight = 80;
		$thumbRatio = $thumbWidth/$thumbHeight;

		/* get the image dimensions */
		$width = imagesx($image);
		$height = imagesy($image);
		$ratio = $width/$height;

		/* resize the image according to the ratio */
		if ($ratio > $thumbRatio)
		{
			$newWidth = $thumbWidth;
			$newHeight = $height * ($newWidth/$width);
		}
		else
		{
			$newHeight = $thumbHeight;
			$newWidth = $width * ($newHeight/$height);
		}

		/* create the new image */
		$output = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($output, $image, 0, 0, 0, 0,$newWidth, $newHeight, $width, $height);

		return $output;
	}

	/**
	 * Serve a imagem do usuário
	 * @return void
	 * @access public
	 */
	public function serve()
	{
		if ($this->userID === false)
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
					/* prepara o ambiente para a carga de informações sobre o banco de dados */
					$result = Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID->query('SELECT config_value FROM phpgw_config WHERE config_app = ? AND config_name = ?', array('phpgwapi', 'files_dir'));
					if (empty($result))
						return;
					$fields = $result->fetchRow();
					$baseDirectory = $fields['config_value'];
				}
			}
			$_SESSION['workflow']['ResourcesRedirector']['baseDirectory'] = $baseDirectory;
		}

		$baseDirectory = str_replace('//', '/', $baseDirectory . '/workflow/workflow/ldapPictures');
		$filename =  $baseDirectory . '/' . $this->userID;

		$createPictureFile = true;
		if (file_exists($filename))
		{
			if ((filemtime($filename) + ($this->cacheDays * 24 * 60 * 60)) > mktime())
				$createPictureFile = false;
			else
				unlink($filename);
		}

		if ($createPictureFile)
		{
			if (!is_dir($baseDirectory))
				mkdir($baseDirectory, 0770, true);

			$contents = Factory::getInstance('WorkflowLDAP')->getUserPicture($this->userID);
			$success = true;
			if ($contents !== false)
			{
				$image = imagecreatefromstring($contents);
				$image = $this->resizeImage($image);
				$success = @imagepng($image, $filename);
			}

			if (($contents === false) || ($success === false))
			{
				$filename = $baseDirectory . '/default.png';
				if (!file_exists($filename))
				{
					$image = imagecreatefrompng(dirname(__FILE__) . '/../../expressoMail1_2/templates/default/images/photo.png');
					$image = $this->resizeImage($image);
					$success = @imagepng($image, $filename);
				}

				if ($success === false)
					return false;
			}
		}

		Factory::getInstance('ResourcesRedirector')->show($filename);
	}
}
?>
