<?php
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'phplot'.SEP.'phplot.php');
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'phplot'.SEP.'phplot_data.php');
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'phplot'.SEP.'rgb.inc.php');

/**
 * Classe para gera��o de gr�ficos din�micos.
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Allan Bomfim
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com (revis�o)
 * @package Workflow
 * @subpackage local
 */
class wf_phplot extends PHPlot
{
	/**
	 * Construtor da classe wf_phplot
	 * @param int $width Largura da imagem.
	 * @param int $height Altura da imagem.
	 * @return object
	 * @access public
	 */
	function wf_phplot($width = 800, $height = 600)
	{
		parent::PHPlot($width, $height);
	}
}
?>
