<?php
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'fpdf'.SEP.'mem_image.php');

/**
 * Classe que permite a inclusão de imagens em relatórios gerados pelo FPDF sem a necessidade da utilização de arquivos temporários.
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Allan Bomfim
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com (revisão)
 * @package Workflow
 * @subpackage local
 */
class wf_mem_image extends MEM_IMAGE
{
	/**
	 * Construtor da classe wf_mem_image
	 * @param string $orientation Orientação da página: 'P' (ou 'portrait') para retrato ou 'L' (ou 'landscape') para paisagem.
	 * @param string $unit Unidade de medida: 'pt' para ponto, 'mm' para milímetro, 'cm' para centímetros ou 'in' para polegadas.
	 * @param string $format Formato da página: 'a3', 'a4', 'a5', 'letter' ou 'legal'.
	 * @return object
	 * @access public
	 */
	function wf_mem_image($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::MEM_IMAGE($orientation, $unit, $format);
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
}
?>
