<?php
/**
 * ListagemColuna
 *
 * Implementação de Colunas das Listagems Padrões do Sistema.
 */
/**
 * ListagemColuna
 *
 * Implementação de Colunas das Listagems Padrões do Sistema.
 *
 *
 * @author Jair Pereira <pereira.jair@gmail.com>
 * @since 01/06/2009
 *
 */

class ListagemColuna {
    private $idcoluna;
    private $idlistagem;
    private $nome;
    private $align;
    private $html;
    private $width;
    private $tipo;
    private $nowrap;
    private $valign;
    private $link;
    private $popup = false;
    private $target;
    private $condicao;
    private $linkcondicao;
    private $cor;
    private $corcondicao;
    private $visivel = true;

    
    public function getValorHTML($linha) {
        $newhtml = "";
        $newhtml .= $this->html;
        $newhtml = $this->replaceValorLinha($newhtml,$linha);
        
        if ($this->tipo == "php") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
        }
        
        if ($this->tipo == "php-moeda") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
        }
        
        return $newhtml;
    }
    
    public function getTextoArquivo($linha,$todososindicadores) {
    	$newhtml = $this->html;
    	foreach ($todososindicadores as $idindicador => $indicadores) {
            foreach ($indicadores as $indicador) {
                $nomeindicador = $indicador->getIdIndicador();
                $condicao = $indicador->getCondicao();
                $ret = $this->validaCondicao($condicao,$linha);
                if ($ret) {
                    $newhtml = str_replace("{" . $nomeindicador . "}",$indicador->getLegendaArquivo() ,$newhtml);
                }
            }
        }
        foreach ($todososindicadores as $idindicador => $indicadores) {
            foreach ($indicadores as $indicador) {
                $nomeindicador = $indicador->getIdIndicador();
                $newhtml = str_replace("{" . $nomeindicador . "}","" ,$newhtml);
            }
        }
        
        $newhtml = $this->replaceValorLinha($newhtml,$linha);
        if ($this->tipo == "data") {
            if ($newhtml != "") {
              $newhtml = date("d/m/Y",strtotime($newhtml));
            }
        }

        if ($this->tipo == "moeda") {
            if ($newhtml == "") { $newhtml = 0; }
            $newhtml = number_format($newhtml,2,",",".");
        }
        
        if ($this->tipo == "php") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
        }
        
        if ($this->tipo == "php-moeda") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
            $newhtml = number_format($newhtml,2,",",".");
        }
        
        return $newhtml;
    }
    
    public function validaCondicao($condicao,$linha,$debug = false) {
        foreach (array_keys($linha) as $col) {
            $condicao = str_replace("{" .  $col . "}","'" . $linha[$col] . "'",$condicao);
        }
        $condicao = html_entity_decode($condicao, ENT_QUOTES);
        $fcondicao = ' if (' . $condicao . ') { $ret = true; } else { $ret = false; }';
        if ($condicao != "") {
          if ($debug) { echo "<br>" . $fcondicao ; } 
          eval($fcondicao);
        } else {
            $ret = false;
        }
        return $ret;
    }
  
    public function getHtml($linha,$todososindicadores,$classe,$i) {
        $newhtml = "";
        $newhtml .= $this->html;
        if ($classe == "tdc") {
            $fundo = false;
        } else {
            $fundo = true;
        }
        
        foreach ($todososindicadores as $idindicador => $indicadores) {
            foreach ($indicadores as $indicador) {
                $nomeindicador = $indicador->getIdIndicador();
                $condicao = $indicador->getCondicao();
                
                $ret = $this->validaCondicao($condicao,$linha);
                if ($ret) {
                    $newhtml = str_replace("{" . $nomeindicador . "}",$indicador->getHtml($fundo) . "{" . $nomeindicador . "}",$newhtml);
                }
            }

        }
        
        $newhtml = str_replace("{this_num_linha}",$i,$newhtml);

        foreach ($todososindicadores as $idindicador => $indicadores) {
            foreach ($indicadores as $indicador) {
                $nomeindicador = $indicador->getIdIndicador();
                $newhtml = str_replace("{" . $nomeindicador . "}","",$newhtml);
            }
        }
         
        $newhtml = $this->replaceValorLinha($newhtml,$linha);

        if ($this->tipo == "check") {
            $condicao = $this->getCondicao();
            $ret = $this->validaCondicao($condicao,$linha);
            if ($ret) {
                $checked = "";
                if (isset($_POST[$this->idcoluna])) {
                    if (is_array($newhtml)) {
                        if (in_array($newhtml,$_POST[$this->idcoluna])) {
                            $checked = " checked";
                        }
                    }
                }
                $newhtml = "<input id='" . $this->idcoluna ."_" . $this->idlistagem . "_" .$i . "' type='checkbox' name='" . $this->idcoluna . "[]' value='" . $newhtml . "' class='obrigatorio'" . $checked .">";
            } else {
                $newhtml = "";
            }
             
        }

        if ($this->tipo == "data") {
            if ($newhtml != "") {
                $newhtml = date("d/m/Y",strtotime($newhtml));
            }
        }
        
        if ($this->tipo == "moeda") {
            if ($newhtml == "") { $newhtml = 0; }
            $newhtml = number_format($newhtml,2,",",".");
        }
        
        if ($this->tipo == "php") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
        }

        if ($this->tipo == "php-moeda") {
        	ob_start();
        	echo eval($newhtml);
        	$novohtml = ob_get_contents();
        	ob_end_clean();
            $newhtml = $novohtml;
            $newhtml = number_format($newhtml,2,",",".");
        }


        if ($this->link != "") {
            $condicao = $this->linkcondicao;
            //echo $this->linkcondicao;
            $ret = $this->validaCondicao($condicao,$linha);
            if ($ret) {
                $novolink = $this->replaceValorLinha($this->link,$linha);
                $novolink = str_replace("{this_num_linha}",$i,$novolink);
                $addtarget = "";
                if ($this->target) {
                    $addtarget = " target='_blank'";
                }
                if ($this->popup) {
                    $addonclick = "  onClick=\"window.open('$novolink','Popup','width=600,top=50,left=50, scrollbars=yes');\" ";
                    $novolink = "#";
                }
                $newhtml = "<a href=\"" . $novolink . "\"" . $addtarget . $addonclick . ">" . $newhtml . "</a>";
            }
        }
        
        if ($this->cor != "") {
            $condicao = $this->corcondicao;
            $ret = $this->validaCondicao($condicao,$linha);
            if ($ret) {
                $newhtml = "<span style='color: #" . $this->cor . ";'>" . $newhtml . "</span>"; 
            }
        }

        return $newhtml;
    }

    public function replaceValorLinha($html,$linha) {
        $newhtml = $html;
        foreach (array_keys($linha) as $col) {
            $newhtml = str_replace("{" .  $col . "}",$linha[$col],$newhtml);
        }
        return $newhtml;
    }

    function setCondicao($condicao) {
        $this->condicao = $condicao;
    }
    
    function setIdListagem($idlistagem) {
        $this->idlistagem = $idlistagem;
    }
    
    function setLinkCondicao($condicao) {
        $this->linkcondicao = $condicao;
    }
    
    function getCondicao() {
        return $this->condicao;
    }
    
    function getLinkCondicao() {
        return $this->condicao;
    }

    function getVAlign($formatado = false) {
        if ($formatado) {
            if ($this->valign != "") {
                $addvalign = " valign='" . $this->valign . "' ";
            } else {
                $addvalign = "";
            }
            return $addvalign;
        } else {
            return $this->valign;
        }
    }

    public function __construct($idcoluna,$tipo,$nome,$html,$width = "",$nowrap = false,$align = "") {
        $this->idcoluna = $idcoluna;
        $this->nome = $nome;
        $this->html = $html;
        $this->width = $width;
        $this->setTipo($tipo);
        if ($align != "") { 
        	$this->align = $align;
        }
        $this->nowrap = $nowrap;
        $this->valign= "center";
    }

    public function setTipo($tipo) {
        $align = "";
        if ($tipo == "imagem") { $align = "center"; }
        if ($tipo == "int") { $align = "right"; }
        if ($tipo == "check") { $align = "center"; }
        if ($tipo == "moeda") { $align = "right"; }
        if ($tipo == "php-moeda") { $align = "right"; }
        if ($tipo == "text") { $align = "left"; }
        if ($tipo == "data") { $align = "center"; }
        if ($tipo == "hora") { $align = "center"; }
  /*      if ($tipo == "center") { $align = "center"; }
        if ($tipo == "left") { $align = "left"; }
        if ($tipo == "right") { $align = "right"; } */ 
        $this->align = $align;
        $this->tipo = $tipo;
    }

    public function setLink($link,$condicao,$target = "",$popup = false) {
        $this->link = $link;
        $this->target = $target;
        $this->linkcondicao = $condicao;
        $this->popup = $popup;
    }
    
    public function setCor($cor,$condicao) {
        $this->cor = $cor;
        $this->corcondicao = $condicao;
    }

    public function getWidth($formatado = false) {
        if ($formatado) {
            if ($this->width != "") {
                $addwidth = " width='" . $this->width . "' ";
            } else {
                $addwidth = "";
            }
            return $addwidth;
        } else {
            return $this->width;
        }
    }

    public function getIdColuna() {
        return $this->idcoluna;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getNowrap() {
        return $this->nowrap;
    }

    public function getAlign() {
        return $this->align;
    }
    
    public function setVisibilidade($value) {
    	$this->visivel = $value;
    }
    public function getVisibilidade() {
    	return $this->visivel;
    }
    
}
?>