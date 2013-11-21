<?
/**
 * FormularioHtml
 * 
 * Implementação de Formulários Padrões do Sistema.  
 * 
 * @author Jair Pereira <pereira.jair@gmail.com>
 */
 
include 'FormularioInput.class.php';

class FormularioHtml extends FormularioInput {
    protected $htmlForm;
    protected $label;
    protected $widthlabel;
    protected $widthinput;
    
    function desenhaHtmlForm($options,$showTableDivision = true, $showTableRow = true){
    	switch($this->tipo){
            case 'periodo' : $this->formInput($options);  break;
            case 'data' : $this->formInput($options);  break;
            case 'moeda' : $this->formInput($options);  break;
            case 'cpf' : $this->formInput($options);  break;
            case 'cnpj' : $this->formInput($options);  break;
            case 'input' : $this->formInput($options);  break;
            case 'select' : $this->formSelect($options);  break;
            case 'checkbox' : $this->formCheckBox($options);  break;
            case 'radio' : $this->formRadio($options);  break;
            case 'textarea' : $this->formTextArea($options);  break;
            case 'button' : $this->formButton($options);  break;
            case 'hidden' : $this->formHidden(false,false);  break;
            case 'subtitulo' : $this->formSubTitulo();  break;
            case 'div' : $this->formDiv();  break;
            case 'file' : $this->formArquivo($options); break;
            default: $this->formInput($options);
        }
    	
        return $this->htmlForm;
    }
    function makeDisplayHTML($options,$tipo = "input") {
    	$opentr = $options["open_tr"];
        $opentd = $options["open_td"];
        $makedivision = $options["makedivision"];
        $makedivision_colspan = $options["makedivision_colspan"];
        $open_td_colspan = $options["open_td_colspan"];
        $closetd = $options["close_td"];
        $closetr = $options["close_tr"];

        if ($opentr)       echo "\n\t<tr>";  
	    if ($makedivision) { 
	    	if ($makedivision_colspan) $addcolspan = " colspan=" . $makedivision_colspan;
	      if ($this->widthlabel) { $addwidthlabel = " width='" . $this->widthlabel . "'"; }
	      echo  "\n\t\t<td" . $addwidthlabel . $addcolspan ." nowrap>"; 
	    }
        echo "<label id='id_lbl_" . $this->name . "' for='" . $this->obrigatorio . "id_" . $this->name . "'>". $this->label . "</label>";
        if ($makedivision) echo "</td>";
        if ($opentd) { 
          if ($open_td_colspan) $addcolspan2 = " colspan=" . $open_td_colspan;
          if ($this->widthinput) { $addwidthinput = " width='" . $this->widthinput . "'"; }
          echo "\n\t\t<td" . $addwidthinput . $addcolspan2 . ">";
        }
        
        echo "\n\t\t\t<span id='span_id_" . $this->name . "'>";
        if ($tipo == "input") { $this->input(); }
        if ($tipo == "select") { $this->select(); }
        if ($tipo == "checkbox") { $this->checkbox(); }
        if ($tipo == "radio") { $this->radio(); }
        if ($tipo == "textarea") { $this->textarea(); }
        if ($tipo == "arquivo") { $this->arquivo(); }
        if ($tipo == "button") { $this->button(); }
        echo "</span>";
        if ($closetd)      echo	"\n\t\t</td>";
        if ($closetr)      echo	"\n\t</tr>\n";
        
        return $this->htmlForm;
    }
    
    function formInput($options = array()){
    	//$this->input();
    	return $this->makeDisplayHTML($options);
    }
    
    function formSelect($options){
        //$this->select();
    	return $this->makeDisplayHTML($options,"select");
    } 
    function formCheckbox($options){
        //$this->checkbox();
    	return $this->makeDisplayHTML($options,"checkbox");
    }
    function formRadio($options){
    	//$this->radio();
    	return $this->makeDisplayHTML($options,"radio");
    }
    function formHidden() {
        echo "\n\t\t\t" . $this->input();
    }
    function formTextArea($options){
       // $this->textArea();
        return $this->makeDisplayHTML($options,"textarea");
    }
    function formButton($options){
		//$this->button();
    	return $this->makeDisplayHTML($options,"button");
    }
    function formSubTitulo() {
    	echo "\n\t\t</td>\n\t</tr>\n\t</table>\n\t<table width='100%'>\n\t<tr>\n\t\t<td>\n\t\t\t&nbsp;<br><br>\n\t\t</td>\n\t</tr>\n\t<tr class='tableSubTitulo'>\n\t\t<td colspan='1000'><h2>" . $this->value . "</h2></td>\n\t</tr>\n\t<tr><td>&nbsp;</td></tr>";
    }
    function formDiv() {
        echo "\n\t\t</td>\n\t</tr>\n\t</table>\n\t<table width='100%'>\n\t\n\t<div id='" . $this->value . "'></div>\n\t";
    }
    function formArquivo($options) {
    	//$this->arquivo();
    	return $this->makeDisplayHTML($options,"arquivo");
    }
    function setLabel($label){
       $this->label = $label;
    }

}



?>
