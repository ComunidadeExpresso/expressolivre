<?php
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'fpdf'.SEP.'fpdf.php');

/**
 * Class for generating PDF reports
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local  
 */
class wf_fpdf extends FPDF
{
	/**
	 * @var string $headerFunction Armazena a função que gera o Cabeçalho do documento
	 * @access public 
	 */
	var $headerFunction;
	/**
	 * @var string $footerFunction Armazena a função que gera o Rodapé do documento
	 * @access public
	 */
	var $footerFunction;

	/**
	 * Construtor da classe wf_fpdf inicializa a classe
	 * @return object
	 * @access public
	 */
	function wf_fpdf()
	{
		$this->headerFunction = "";
		$this->footerFunction = "";

		parent::FPDF();
	}
	/**
	 * Utilizado para chamar o Metodo Output da classe base FPDF que retorna o documento PDF como uma string 
	 * @return string
	 * @access public
	 */
	function Output()
	{
		return parent::Output('', 'S');
	}
	 
	/**
	 * Utilizado para chamar (se existir) uma função para gerar o cabeçalho
	 * @return mixed
	 * @access public 
	 */
	function Header()
	{
		$funcao = $this->headerFunction;
		if (!empty($funcao))
			if ((function_exists($funcao)) && (is_callable($funcao)))
				return call_user_func($funcao, &$this);
	}
    /**
	 * Utilizado para chamar (se existir) uma função para gerar o rodapé
	 * @return mixed
	 * @access public 
	 */
	function Footer()
	{
		$funcao = $this->footerFunction;
		if (!empty($funcao))
			if ((function_exists($funcao)) && (is_callable($funcao)))
				return call_user_func($funcao, &$this);
	}
    /**
	 * Utilizado para atribuir uma função para gerar o Cabeçalho
	 * @return void
	 * @access public 
	 */
	function setHeaderFunction($funcao)
	{
		$this->headerFunction = $funcao;
	}
	/**
	 * Utilizado para atribuir uma função para gerar o Rodapé
	 * @return void
	 * @access public 
	 */
	function setFooterFunction($funcao)
	{
		$this->footerFunction = $funcao;
	}
}
?>
