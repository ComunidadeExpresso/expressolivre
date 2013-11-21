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

require_once 'smarty/Smarty.class.php';

/**
 * Classe para utilizar o Smarty na criação de interfaces do módulo.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class workflow_smarty extends Smarty
{
	/**
	 * Valor usado para indicar a exibição do cabeçalho
	 * @name SHOW_HEADER
	 */
	const SHOW_HEADER = 1;

	/**
	 * Valor usado para indicar a exibição da barra de navegação
	 * @name SHOW_NAVIGATION_BAR
	 */
	const SHOW_NAVIGATION_BAR = 2;

	/**
	 * Valor usado para indicar a exibição do rodapé
	 * @name SHOW_FOOTER
	 */
	const SHOW_FOOTER = 4;

	/**
	 * @var string $expressoHeader Cabeçalho do Expresso.
	 * @access public
	 */
	public $expressoHeader = '';

	/**
	 * @var string $expressoFooter Rodapé do Expresso.
	 * @access public
	 */
	public $expressoFooter = '';

	/**
	 * @var bool $showHeader Indicador de exibição do rodapé
	 * @access private
	 */
	private $showHeader = true;

	/**
	 * @var bool $showNavigationBar Indicador de exibição da barra de navegação (se utilizado, o cabeçalho também será exibido)
	 * @access private
	 */
	private $showNavigationBar = true;

	/**
	 * @var bool $showFooter Indicador de exibição do rodapé
	 * @access private
	 */
	private $showFooter = true;

   /**
	 * Construtor da classe workflow_smarty
	 * @param bool $createHeader Indica que o cabeçalho/rodapé devem ser "criados" na criação do objeto (se true, irá criar: cabeçalho, barra de navegação e rodapé)
	 * @return object
	 * @access public
	 */
	function workflow_smarty($createHeader = true)
	{
		$this->Smarty();

		/* define some directories */
		$workflowHomeDirectory = isset($_SESSION['phpgw_info']['workflow']['vfs_basedir']) ?
			$_SESSION['phpgw_info']['workflow']['vfs_basedir'] . '/workflow':
			$GLOBALS['phpgw_info']['server']['files_dir'] . '/workflow';
		$smartyDirectory = $workflowHomeDirectory . '/smarty';
		$templateSet = isset($_SESSION['phpgw_info']['workflow']['server']['template_set']) ?
			$_SESSION['phpgw_info']['workflow']['server']['template_set'] :
			$GLOBALS['phpgw_info']['server']['template_set'];
		$documentRoot = isset($_SESSION['phpgw_info']['workflow']['server_root']) ?
			$_SESSION['phpgw_info']['workflow']['server_root'] :
			$_SERVER['DOCUMENT_ROOT'] . $GLOBALS['phpgw_info']['server']['webserver_url'];

		/* list of directories used by the Smarty Template Engine */
		$directories = array(
			'home' => $workflowHomeDirectory,
			'main' => $smartyDirectory,
			'template' => array(
				"{$documentRoot}/workflow/templates/{$templateSet}",
				"{$documentRoot}/workflow/templates/default"
			),
			'compile' => $smartyDirectory . '/compile',
			'config' => $smartyDirectory . '/config',
			'cache' => $smartyDirectory . '/cache'
		);

		/* if necessary, create the directories */
		if (!is_dir($directories['main']))
			foreach ($directories as $key => $dir)
				if ($key != 'template')
					if (!is_dir($dir))
						@mkdir($dir,0770);

		/* setup the Smarty configuration */
		$this->template_dir = $directories['template'];
		$this->compile_dir = $directories['compile'];
		$this->config_dir = $directories['config'];
		$this->cache_dir = $directories['cache'];
		$this->plugins_dir[] = $documentRoot . '/workflow/inc/smarty/module_plugins';

		if ($createHeader)
			$this->setHeader(workflow_smarty::SHOW_HEADER | workflow_smarty::SHOW_NAVIGATION_BAR | workflow_smarty::SHOW_FOOTER);
	}

   /**
	 * Define os elementos do template padrão, do ExpressoLivre, que serão exibidos
	 * @param int $config Um inteiro que representa quais os elementos que serão exibidos
	 * @param string $applicationTitle O título da aplicação, opcional (valor padrão: Workflow)
	 * @return void
	 * @access public
	 */
	public function setHeader($config, $applicationTitle = 'Workflow')
	{
		$this->showHeader = (bool) ($config & workflow_smarty::SHOW_HEADER);
		$this->showNavigationBar = (bool) ($config & workflow_smarty::SHOW_NAVIGATION_BAR);
		$this->showFooter = (bool) ($config & workflow_smarty::SHOW_FOOTER);
		$this->showHeader = $this->showHeader || $this->showNavigationBar;

		$GLOBALS['phpgw_info']['flags']['app_header'] = $applicationTitle;
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => true,
			'nonavbar' => true,
			'currentapp' => 'workflow'
		);

		$this->createHeader();
	}

   /**
	 * Cria o cabeçalho de acordo com os elementos selecionados
	 * @return void
	 * @access private
	 */
	private function createHeader()
	{
		/* get the header code */
		if ($this->showHeader)
		{
			ob_start();
			$GLOBALS['phpgw']->common->phpgw_header();
			if ($this->showNavigationBar)
				parse_navbar();
			$this->expressoHeader = ob_get_contents();
			ob_end_clean();
		}

		/* get the footer code */
		if ($this->showFooter)
		{
			ob_start();
			$GLOBALS['phpgw']->common->phpgw_footer();
			$this->expressoFooter = ob_get_contents();
			ob_end_clean();
		}
	}
}
?>
