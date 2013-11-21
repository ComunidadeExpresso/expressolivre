<?php
/**
 * ListagemIndicador
 * 
 * @author Jair Pereira <pereira.jair@gmail.com>
 *
 */
class ListagemIndicador {
	
	private $idindicador;
	private $tipo;
	private $condicao;
	private $codigoimagem;
	private $legenda;
	private $tamanho;
	private $legendaarquivo;
	private $_url_base_path;
	
	public function ListagemIndicador($idindicador,$condicao = "",$tipo = "R",$codigoimagem = "1",$legenda = "",$legendaarquivo = "",$tamanho = "1") {
		$this->idindicador = $idindicador;
		$this->condicao = $condicao;
		$this->tipo = $tipo;
		$this->codigoimagem = $codigoimagem;
		$this->legenda = $legenda;
		$this->legendaarquivo = $legendaarquivo;
		$this->tamanho = $tamanho;
		$this->_url_base_path = "";
	}
	
	public function getLegenda() {
		return $this->legenda;
	}
	
	public function setUrlBasePath($base_path) {
		$this->_url_base_path = $base_path;
	}
	
	public function getIdIndicador() {
		return $this->idindicador;
	}
	
	public function getTipo() {
		return $this->tipo;
	}
	
	public function getCodigoImagem() {
	    return $this->codigoimagem;
	}
	
	protected function getFileName($fundo = false) {
		if (strtoupper($this->tipo) == "R") { $pasta = $this->_url_base_path . "/images/indicadores/redondos/ap"; }
		if (strtoupper($this->tipo) == "Q") { $pasta = $this->_url_base_path . "/images/indicadores/quadrados/ap"; }
		if (strtoupper($this->tipo) == "T") { $pasta = $this->_url_base_path . "/images/indicadores/triangulares/ap"; }
	    if (strtoupper($this->tipo) == "I") { $pasta = $this->_url_base_path . "/images/icones/t"; }
		if ($fundo) { $pasta = $pasta . "f"; }
		if (strtoupper($this->tipo) != "I") {
    		if ($this->codigoimagem < 10) { 
    			$nomeimagem = "ap0" . $this->codigoimagem . ".jpg"; 
    		} else {
    			$nomeimagem = "ap" . $this->codigoimagem . ".jpg";
    		}
    		$filename = $pasta ."/" .  $nomeimagem;
		} else {
		    $filename = $pasta . $this->tamanho . "/" . $this->codigoimagem . ".jpg";   
		}
		if (!file_exists($filename)) { 
		    $filename = str_replace("../../../","",$filename);
		}		
		return $filename;
	}
	
	public function getLegendaArquivo() {
		return $this->legendaarquivo;
	}
	
	public function getHtml($fundo = false) {
	    $file = $this->getFileName($fundo);
		$html = "<img src='" . $file . "' align='absmiddle' alt='" . $this->legenda . "' title='" . $this->legenda .  "'>";
		return $html;
	}
	public function getCondicao() {
		return $this->condicao;
	}
}
?>