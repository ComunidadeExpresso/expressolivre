<?
/**
 * FormularioInput
 * 
 * Implementação de Formulários Padrões do Sistema.  
 * @author Jair Pereira <pereira.jair@gmail.com>
 */
 
class FormularioInput {
    
    protected $html;
    protected $tipo;
    protected $name;
    protected $value;
    protected $valueAux;
    protected $title;
    protected $form;
    protected $valuesSelect;
    protected $selectEscolha;
    protected $selectEscolhaValue;
    protected $valuesChecks;
    protected $valuesRadio;
    protected $width;
    protected $height;
    protected $multiple;
    protected $inforight;
    protected $obrigatorio;
    protected $selectCor;
    protected $selectValuesCor = array();
    public $arreventos = array();
    
    //$this->value
    function input(){
        $this->html = '';
        $addeventos = $this->getHtmlEventos();
        switch($this->tipo){
            case 'periodo' : 
                             $this->adicionarEvento("onkeyup","formatar(this,'@@/@@/@@@@'); validarPeriodo(document." . $this->form . "." . $this->name . "_inicio,document." .$this->form . "." . $this->name . "_fim);");
                             $this->adicionarEvento("onblur","revalidar(this,'@@/@@/@@@@','data'); validarPeriodo(document." . $this->form . "." . $this->name . "_inicio,document." .$this->form . "." . $this->name . "_fim);");
                             $addeventos = $this->getHtmlEventos();
                             echo '<input type="text" id="'.$this->obrigatorio.'id_'. $this->name.'_inicio" name="' . $this->name . '_inicio" value="' . $this->value . '" size="10" maxlength="10" ' .  $addeventos .' title="' . $this->title . '">';
                             echo '<img id="id_imgcal_' . $this->name . '_inicio" src="./images/calendar_cal.gif" align="absmiddle" border="0" alt="Calendário..." onClick="displayCalendar(document.' . $this->form . '.' . $this->name . '_inicio,\'dd/mm/yyyy\',this)">';
                             echo '&nbsp;a&nbsp;' ;
                             echo '<input type="text" id="'.$this->obrigatorio.'id_'. $this->name.'_fim" name="' . $this->name . '_fim" value="' . $this->valueAux . '" size="10" maxlength="10" ' .  $addeventos .' title="' . $this->title . '">' ;
                             echo '<img id="id_imgcal_' . $this->name . '_fim" src="./images/calendar_cal.gif" align="absmiddle" border="0" alt="Calendário..."   onclick="displayCalendar(document.' . $this->form . '.' . $this->name . '_fim,\'dd/mm/yyyy\',this)"><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>'; 
                 break;
            case 'data' : 
                          $this->adicionarEvento("onkeyup","formatar(this,'@@/@@/@@@@'); validarPeriodo(document." . $this->form . "." . $this->name . "_inicio,document." .$this->form . "." . $this->name . "_fim);");
                          $this->adicionarEvento("onblur","revalidar(this,'@@/@@/@@@@','data'); validarPeriodo(document." . $this->form . "." . $this->name . "_inicio,document." .$this->form . "." . $this->name . "_fim);");
                          $addeventos = $this->getHtmlEventos();
                          echo '<input type="text" id="'.$this->obrigatorio.'id_'. $this->name.'" name="' . $this->name . '_inicio" value="' . $this->value . '" size="10" maxlength="10" ' . $addeventos . ' title="' . $this->title . '">';
                          echo '<img id="id_imgcal_' . $this->name . '_inicio" src="./images/calendar_cal.gif" align="absmiddle" border="0" alt="Calendário..." onClick="displayCalendar(document.' . $this->form . '.' . $this->name . '_inicio,\'dd/mm/yyyy\',this)"><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>';
                 break;
                 
            case 'moeda' : 
            			  $this->adicionarEvento("onkeyup","milhar(this);");
                          $this->adicionarEvento("onblur","revalidarMilhar(this);");
                          $addeventos = $this->getHtmlEventos();
            			  echo '<input type="text" name="' . $this->name . '" id="'.$this->obrigatorio.'id_' . $this->name . '" value="' . $this->value . '" size="20" maxlength="20" ' . $addeventos . ' title="' . $this->title . '"><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>'; break;
            case 'cpf'   : 
            			  $this->adicionarEvento("onkeyup","formatar(this,'@@@.@@@.@@@-@@');");
                          $this->adicionarEvento("onblur","revalidar(this,'@@@.@@@.@@@-@@','cpf');");
                          $addeventos = $this->getHtmlEventos();
            			  echo '<input type="text" name="' . $this->name . '" id="'.$this->obrigatorio.'id_' . $this->name . '" value="' . $this->value . '" size="20" maxlength="20" ' . $addeventos . ' title="' . $this->title . '"><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>'; break;
            case 'cnpj'  : 
                          $this->adicionarEvento("onkeyup","formatar(this,'@@.@@@.@@@/@@@@-@@');");
                          $this->adicionarEvento("onblur","revalidar(this,'@@.@@@.@@@/@@@@-@@','cnpj');");
                          $addeventos = $this->getHtmlEventos();
            			  echo '<input type="text" name="' . $this->name . '" id="'.$this->obrigatorio.'id_' . $this->name . '" value="' . $this->value . '" size="20" maxlength="20" ' . $addeventos . ' title="' . $this->title . '"><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>'; break;
            
            case 'hidden':echo '<input type="hidden" name="' . $this->name . '" id="id_' . $this->name . '" value="' . $this->value . '">'; break;
            
            case 'int'   : 
                          $this->adicionarEvento("onkeyup","formatar(this,'@@@@@@@@@@@@@@@@@@');");
                          $this->adicionarEvento("onblur","revalidar(this,'@@@@@@@@@@@@@@@@@@');");
                          $addeventos = $this->getHtmlEventos();
                          echo '<input type="text" name="' . $this->name . '" id="'.$this->obrigatorio.'id_' . $this->name . '" value="' . $this->value . '"  title="' . $this->title . '" size="' . $this->width . '" maxlength="' . $this->height . '" ' . $addeventos . ' ><span id="id_info_' . $this->name . '" class=inforight style="color: rgb(100, 62, 65);">&nbsp;' . $this->inforight .'</span><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>'; break;
            
            default      : 
                          
                          echo '<input type="text" name="' . $this->name . '" id="'.$this->obrigatorio.'id_' . $this->name . '" value="' . $this->value . '"  title="' . $this->title . '" size="' . $this->width . '" maxlength="' . $this->height . '" ' . $addeventos . ' ><span id="id_info_' . $this->name . '" class=inforight style="color: rgb(100, 62, 65);">&nbsp;' . $this->inforight .'</span><span id="imgAjax_' . $this->name . '" style="display: none;"><img src="images/progress4.gif"></span>';
        }

    }
    
    function subtitulo() {
    	echo "<tr><td>" . $this->value . "</td></tr>";
    }
    
    function button() {
    	$addeventos = $this->getHtmlEventos();
        echo "<input type='button' name='" . $this->name . "' id='" . $this->form. "_btn_". $this->name . "' value='" . $this->value . "' style='width: 90px;' class='botao' " . $addeventos ." >";
    }
    
    function select(){
        $selected = "";
        $addeventos = $this->getHtmlEventos();
        echo "\n\t\t\t";
        if ($this->multiplo) { $addmultiplo = " MULTIPLE"; } 
        echo '<table><tr><td><select id="'.$this->obrigatorio.'id_'. $this->name.'" name="' . $this->name . '"  title="' . $this->title . '" style="width: ' . $this->width . 'px;" size="' . $this->height . '" ' . $addeventos . ' ' . $addmultiplo . '>';
        echo "\n";
        if($this->selectEscolha == true){
            echo "\t\t\t<option value=''>" . $this->selectEscolhaValue . "</option>";
        }
        if(count($this->valuesSelect) > 0 ){
            foreach($this->valuesSelect as $chave => $valor){
                $selected = ("$chave" == "$this->value") ? ' selected="selected" ' : '';
                echo "\t\t\t\t";
                if (in_array($chave,$this->selectValuesCor)) {
                	$addstyle = ' style="color: '.$this->selectCor . ';"';
                } else {
                	$addstyle = '';
                }
                echo '<option value="' . $chave . '"' . $selected .  $addstyle . '>' . $valor . '</option>';
                echo "\n";
            }
        }
        echo "\t\t\t</select></td><td><span id='imgAjax_" . $this->name . "' style='display: none;'><img src='images/progress4.gif'></span></td></tr></table>\n";
    }
    
    
    function checkbox(){
        $selected = "";
        $addeventos = $this->getHtmlEventos();
        echo '';
        if(count($this->valuesChecks) > 0 ){
        	
            echo "\n\t\t<table border='0'>";
            echo "\n\t\t<tr>\n\t\t\t<td>";
            $i = 0;
        	echo "\n\t\t\t<div id='".$this->obrigatorio .'div_ckbox_' .$this->name . "'>";
            foreach($this->valuesChecks as  $valores){
                //$class = ($class == 'tdc') ? 'tde' : 'tdc';
                 $selected = ($valores[2] == true) ? ' checked ' : '';
                 if (isset($_POST[$this->name])) {
	                 if (in_array($valores[0],$_POST[$this->name])) {
	                 	$selected = "checked";
	                 }
                 }
                 $i = $i + 1;
                 echo "\n\t\t\t\t";
                 echo '<input type="checkbox" class="checkbox" id="' . $this->name . '[' . $i . ']" name="' . $this->name . '[' . $i . ']" value="' . $valores[0] . '" ' . $addeventos . ' ' . $selected . ' >' . $valores[1] ;
                 echo "<br>";
                
            }
            echo "\n\t\t\t</div>";
		    echo "\n\t\t</td><td valign='top' id='imgAjax_" . $this->name . "' style='display: none;'><img src='images/progress4.gif'></td></tr>";
		    echo "\n\t\t</table>\n"; 
		    
        }
    }
    
    function radio(){
        $selected = "";
        $this->html = '';
        $addeventos = $this->getHtmlEventos();
        if(count($this->valuesRadio) > 0 ){
        	echo "\n\t\t\t<div id='".$this->obrigatorio . $this->form. '_div_radio_' .$this->name . "'>";
        	echo "\n\t\t<table border='0'>";
            echo "\n\t\t<tr>\n\t\t\t<td>";
            
            foreach($this->valuesRadio as  $valores){		               
                $selected = ($valores[2] == true) ? ' checked ' : '';
                echo "\n\t\t\t\t";
                echo '<input type="radio" id="'. $this->name .'" class="radio" name="' . $this->name . '" value="' . $valores[0] . '" ' . $addeventos . " " . $selected . " >" . $valores[1] ;
                echo "<br>\n";
            }
            
		    echo "\n\t\t</td><td valign='top' id='imgAjax_" . $this->name . "' style='display: none;'><img src='images/progress4.gif'></td></tr>";
		    echo "\n\t\t</table>\n";
            echo "\n\t\t\t</div>";
        }
        
    }
    
    function arquivo() {
    	$this->html = '';
    	$this->html = '<input type="file" id="'. $this->name .'" name="'. $this->name .'">';
    }
    
    function textArea(){
        $this->html = '';
        $addeventos = " " . $this->getHtmlEventos();
        echo "<textarea name=\"" . $this->name . "\" id=\"" . $this->obrigatorio . "id_". $this->name . "\" cols=\"" . $this->width .  "\" rows=\"" . $this->height .  "\"$addeventos>" . $this->value . "</textarea>";
       
    }
    
    
    
    function desenhaForm(){
       return $this->html;        
    }
    function setTipo($tipo){
        $this->tipo = $tipo;
    }
    function setName($name){
        $this->name = $name;
    }
    function setValue($value){
        $this->value = "$value";
        //echo $this->name . ": " . $this->value . "<br>";
    }
    function setValueAux($valueAux){
        $this->valueAux = $valueAux;
    }
    function setTitle($title){
        $this->title = $title;
    }
    function setInfoRight($info) {
    	$this->inforight = $info;
    }
    function setObrigatorio($obrigatorio){
    	
    		if(strlen($obrigatorio)>1){
    				$this->obrigatorio = $obrigatorio;
    				return true;
    		}
    		
    		switch($obrigatorio){
    			case true :
    				$this->obrigatorio = "";
    				break;
    			case false :
    				$this->obrigatorio = "not_";
    				break;
    			default :
    				$this->obrigatorio = "";
    		}
    }
    
    
    function setValuesSelect($arrayValores){
        $this->valuesSelect = $arrayValores;
    }
    function getName() {
    	return $this->name;
    }
    function getValue() {
    	return $this->value;
    }
    function getWidth() {
    	return $this->width;
    }
    function getTipo() {
    	return $this->tipo;
    }
    function getObrigatorio() {
    	return $this->obrigatorio;
    	/*if ($this->obrigatorio == "not_") {
    		$ret = false;
    	} else {
    		$ret = true;
    	}
    	return $ret; */
    }
    function getHeight() {
    	return $this->height;
    }
    function getMultiplo() {
    	return $this->multiplo;
    }
    function getLabel() {
    	return $this->label;
    }
    
    function setValuesSelectCor($cor,$values) {
    	$this->selectValuesCor = $values;
    	$this->selectCor = $cor;
    }
    
    function getTitle() {
    	return $this->title;
    }
    function setForm($form){
        $this->form = $form;
    }
    function getHtml() {
        return $this->html;
    }
    function setSelectEscolha($escolha, $valor){
        $this->selectEscolha = $escolha;
        $this->selectEscolhaValue = $valor;
    }
    function getSelectEscolha() {
      	return $this->selectEscolha;
    }
    function getSelectEscolhaValue() {
      	return $this->selectEscolhaValue;
    }
    
    function setValuesChecks($arrValores){
       $this->valuesChecks = $arrValores;
    }
    function setValuesRadio($arrValores){
       $this->valuesRadio = $arrValores;
    }
    
    function setSize($tamanho1,$tamanho2 = "") {
    	$this->width = $tamanho1;
    	$this->height = $tamanho2;
    }
    function setMultiplo($multiplo) {
    	$this->multiplo = $multiplo;
    }
    
    function adicionarEvento($action,$javascript) {
       $arrevt = array("action" => $action, "evento" =>$javascript);
       array_push($this->arreventos,$arrevt);
    }
    function getHtmlEventos() {
    	$streventos = "";
    	foreach($this->arreventos as $evento) {
    	    if ($evento["action"] == "onclick") { 
    	    	$addonclick .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onchange") { 
    	    	$addonchange .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onblur") { 
    	    	$addonblur .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onmouseout") { 
    	    	$addonmouseout .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onmousemove") { 
    	    	$addonmousemove .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onkeydown") { 
    	    	$addonkeydown .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onkeypress") { 
    	    	$addonkeypress .= $evento["evento"];
    	    }
    	    if ($evento["action"] == "onkeyup") { 
    	    	$addonkeyup .= $evento["evento"];
    	    }
    	}
    	if ($addonclick != "") { 
    		$streventos .= " OnClick=\" $addonclick \" ";
    	}
    	if ($addonchange != "") { 
    		$streventos .= " OnChange=\" $addonchange \" ";
    	}
    	if ($addonmouseout != "") { 
    		$streventos .= " OnMouseOut=\" $addonmouseout \" ";
    	}
    	if ($addonmousemove != "") { 
    		$streventos .= " OnMouseMove=\" $addonmousemove \" ";
    	}
    	if ($addonkeypress != "") { 
    		$streventos .= " OnKeyPress=\" $addonkeypress \" ";
    	}
    	if ($addonkeyup != "") { 
    		$streventos .= " OnKeyUp=\" $addonkeyup \" ";
    	}
    	if ($addonkeydown != "") { 
    		$streventos .= " OnKeyDown=\" $addonkeydown \" ";
    	}
    	if ($addonblur != "") { 
    		$streventos .= " OnBlur=\" $addonblur \" ";
    	}
    	return $streventos;
    }

}
?>
