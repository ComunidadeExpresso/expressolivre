<?php
/**
 * Formulario
 *
 * Implementacao de Formularios Padroes do Sistema.
 */
/**
 * Formulario
 *
 * Implementacao de Formularios Padroes do Sistema.
 *
 *
 * @author Jair Pereira <pereira.jair@gmail.com>
 * @since 25/05/2009
 *
 * @package Formulario
 */

include 'FormularioHtml.class.php';
include 'FormularioUtil.class.php';
if (file_exists("../../xajax/xajax.inc.php")) { include_once "../../xajax/xajax.inc.php"; }
if (file_exists("../../../xajax/xajax.inc.php")) { include_once "../../../xajax/xajax.inc.php"; }

class Formulario {

    /**
     * FormularioUtil - Inst�ncia da Classe Formul�rio Util.
     */
    public $util = FormularioUtil;

    /**
     * Array de FormularioHtml do Formul�rio
     */
    private $_arrinputs = array();

    /**
     * Array de fun��es em Xajax.
     */
    private $_arrxajax = array();

    /**
     * Array de Querys em ajax.
     */
    private $_arrxajaxquery = array();

    /**
     * Array de arquivos que vieram por upload.
     */
    private $_arrfilesuploaded = array();

    /**
     * Array de Quadros pertencentes ao formul�rio.
     */
    private $_arrquadros = array();
    /**
     * Array de Campos Agrupados.
     */
    private $_agruparcampos = array();
    /**
     * Array de Campos que J� foram Desenhados na tela,
     * Utilizado para evitar que o mesmo campo seja desenhado duas vezes.
     */
    private $_desenhou = array();
    /**
     * Nome do Formul�rio.
     */
    private $_nomeform;
    /**
     * Action do Formul�rio.
     */
    private $_action;
    /**
     * M�todo do Formul�rio.
     */
    private $_method;
    /**
     * Valor do Onsubmit do formul�rio.
     */
    private $_onsubmit;
    /**
     * Armazena o C�digo HTML A ser impresso na tela.
     */
    //private $_htmlForm;
    
    private $html;

    /**
     * A fun��o desenhar() j� foi chamada?.
     */
    private $_desenhado = false;

    /**
     * � um formul�rio de Upload de arquivos?.
     */
    private $_isupload = false;

    /**
     * Depura��o de C�digo ativada?
     */
    private $_debug = true;
    
    private $_iscachedform = false;

    /**
     * Array com os erros do debug.
     */
    private $_errors = array();
    
    private $_microtime;

    private $inputDisplayOptions = array("open_tr" => true,
                                         "open_td" => true,
                                         "open_td_colspan" => 1,
                                         "makedivision" => true,
                                         "close_td" => true,
                                         "close_tr" => true);

    private $FirstGroupColsDisplayOptions = array("open_tr" => true,
                                         "open_td" => true,
                                         "open_td_colspan" => 1,
                                         "makedivision" => true,
                                         "close_td" => false,
                                         "close_tr" => false);
     
    private $FirstGroupDisplayOptions = array("open_tr" => true,
                                         "open_td" => true,
                                         "open_td_colspan" => 100,
                                         "makedivision" => true,
                                         "close_td" => false,
                                         "close_tr" => false);

    private $InputGroupDisplayOptions = array("open_tr" => false,
                                         "open_td" => false,
                                         "open_td_colspan" => 1,
                                         "makedivision" => false,
                                         "close_td" => false,
                                         "close_tr" => false);

    private $InputGroupColsDisplayOptions = array("open_tr" => false,
                                         "open_td" => true,
                                         "open_td_colspan" => 1,
                                         "makedivision" => true,
                                         "close_td" => false,
                                         "close_tr" => false);
     
    private $ButtonDisplayOptions = array("open_tr" => false,
                                         "open_td" => false,
                                         "makedivision" => false,
                                         "close_td" => false,
                                         "close_tr" => false);

    /**
     * Instancia uma nova Interface de Formul�rio
     *
     * @param string $name Nome
     * @param string $action Action
     * @param string $method M�todo
     * @param string $onsubmit Fun��o Javascript do OnSubmit
     * @return void
     */
    function Formulario($name = "frmpadrao",$action = "",$method = "post",$onsubmit = "") {
        $this->Interface = new FormularioHtml();
        $this->setForm($name);
        $this->setAction($action);
        $this->setMethod($method);
        $this->setOnSubmit($onsubmit);
        $this->util = new FormularioUtil();
       // $microtime = microtime(true);
        $microtime = rand(0,900);
        $this->microtime = $microtime;
        $this->adicionarHidden($name . "_pkey",$microtime);
     //   $this->adicionarHidden($name . "_lastpkey",$_POST[$name . "_pkey"]);
    }

    /**
     * Verifica se o ID passado j� foi adicionado como sendo um campo do formul�rio.
     *
     * @param string $idinput ID Input
     * @return void
     */
    protected function isInput($idinput) {
        if ($idinput != "") {
            $achou = false;
            foreach ($this->_arrinputs as $input) {
                if ($input->getName() == $idinput)
                $achou = true;
            }
        } else {
            $achou = true;
        }
        return $achou;
    }

    /**
     * Retorna o Input para o ID passado.
     *
     * @param string $idinput ID Input
     * @param boolean $retornaposicao Retorna a posi��o do input no _arrinputs.
     * @return void
     */
    protected function getInput($idinput,$retornaposicao = false) {
        if ($idinput != "") {
            $achou = "-1";
            $i = 0;
            foreach ($this->_arrinputs as $input) {
                 
                if ($input->getName() == $idinput) {
                    if ($retornaposicao) {
                        $achou = $i;
                    } else {
                        $achou = $input;
                    }
                }
                $i = i + 1;
            }
        } else {
            $achou = "-1";
       	}
       	return $achou;
    }

    /**
     * Agrupar Campos em um Formul�rio
     *
     * Posiciona Campos um lado do outro em um formul�rio, em uma mesma linha sem criar divis�es nas tabelas.
     *
     * Exemplo:
     * <code>
     * $form->adicionarCampo("nome",(.....));
     * $form->adicionarCampo("cpf",(.....));
     * $form->adicionarCampo("rg",(.....));
     * $form->agruparCampos("nome,cpf,rg");
     * </code>
     *
     * @param string $campos Nome dos Campos a serem Agrupados, Separados por v�rgula.
     */
    function agruparCampos($camposagrupados,$colunas = true) {
        $arrcampos1 = explode(";",$camposagrupados);
        foreach ($arrcampos1 as $campos) {
            $arrcampos = explode(",",$campos);
            $agcampos = array($arrcampos[0] => $arrcampos);

            //VERIFICA SE O CAMPO J� FOI ADICIONADO E A ORDEM QUE FOI ADICIONADO AO FORMUL�RIO.
            if ($this->_debug) {
                //$achouprimeiro = false;
                foreach ($arrcampos as $idcampo) {
                     
                    /*	if ($achouprimeiro) {
                     if (in_array($idcampo,$this->desenhou)) {
                     $this->adicionarErro("agruparCampos()<br>ID: " . $idcampo . " achou.",false);
                     }
                     }
                     if ($idcampo == $arrcampos[0]) {
                     $achouprimeiro = true;
                     } */
                    if ((!$this->isInput($idcampo)) && ($idcampo != "")) {
                        $this->adicionarErro("agruparCampos()<br>ID: " . $idcampo . " n�o � um campo no formul�rio.",false);
                    }
                }
            }

            $agcampos["colunas"] = $colunas;
            array_push($this->_agruparcampos,$agcampos);
        }
    }

    /**
     * Adiciona um Quadro ao Formul�rio
     *
     * $campos � opcional, SOMENTE quando s� existe um quadro para o Formul�rio, a classe automaticamente entende que � todos os campos que j� foram adicionados.
     *
     * @param string $idquadro Id do Quadro
     * @param string $titulo T�tulo do Quadro
     * @param string $campos Campos que Pertencer�o ao Quadro (separados por v�rgula)
     */
    function adicionarQuadro($idquadro,$titulo = "",$campos = "") {
        $arrcampos = explode(",",$campos);
        if ($this->_debug) {
            if (is_array($this->_arrquadros[$idquadro])) {
                $this->adicionarErro("adicionarQuadro()<br>ID: $idquadro j� foi adicionado no formul�rio.");
            }
            foreach ($arrcampos as $campo) {
                if ($this->isInputInQuadro($campo)) {
                    $this->adicionarErro("adicionarQuadro()<br>ID: $campo j� foi adicionado a outro quadro neste formul�rio.");
                }
                if (!$this->isInput($campo)) {
                    $this->adicionarErro("adicionarQuadro()<br>ID: $campo N�O est� adicionado no formul�rio.");
                }
            }
        }

        if ($campos == "") {
            $arrcampos = $this->montaArrayQuadroCampos($idquadro);
        }

        $quadro = array("idquadro" => $idquadro,"titulo" => $titulo, "campos" => $arrcampos,"botoes" => array());
        $this->_arrquadros[$idquadro] = $quadro;
    }

    /**
     * Fun��o interna para verificar se um Input j� foi adicionado em algum quadro.
     *
     * @param string $idinput Id do Input
     * @return boolean
     */
    protected function isInputInQuadro($idinput) {
        $ret = false;
        foreach ($this->_arrquadros as $quadro) {
            if (in_array($idinput,$quadro['campos'])) {
                $ret = true;
            }
        }
        return $ret;
    }
    
    public function setCachedForm($value) {
    	$this->_iscachedform = $value;
    }

    /**
     * Adiciona um Bot�o ao Rodap� de um Quadro do Formul�rio
     *
     * @param string $idquadro Id do Quadro
     * @param string $idbutton Id do Bot�o
     */
    function adicionarQuadroButton($idquadro,$idbutton) {
        if (!is_array($this->_arrquadros[$idquadro])) {
            $erro = true;
            if ($this->_debug) {
                $this->adicionarErro("adicionarQuadroButton()<br>ID: $idquadro N�O est� adicionado no formul�rio.");
            }
        }
        if (!$this->isInput($idbutton)) {
            $erro = true;
            if ($this->_debug) {
                $this->adicionarErro("adicionarQuadroButton()<br>ID: $idbutton N�O � um campo no formul�rio.");
            }
        }
        if (!$erro) {
            foreach ($this->_arrinputs as $input) {
                if ($input->getName() == $idbutton) {
                    array_push($this->_arrquadros[$idquadro]["botoes"],$input);
                }
            }
        }
    }

    /**
     * Adiciona Campo
     *
     * Fun��o para adicionar campos no formul�rio, permite v�rios formatos de tipo.
     *
     * tipo: text,data,periodo,moeda,cpf,cnpj,hidden,button
     *
     * @param  string $name
     * @param  string $tipo
     * @param  string $label
     * @param  string $title
     * @param  string $value
     * @param  string $obrigatorio
     * @param  string $width
     * @param  string $maxlenght
     * @param  string $inforight
     * @return void
     */
    function adicionarCampo($name,$tipo,$label,$title,$value = "",$obrigatorio = false,$width = "30",$maxlenght = "100",$inforight = "") {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setTipo($tipo);
        $iface->setName($name);
        $valores = explode("|",$value);
        $iface->setValue(str_replace("\'","'",$valores[0]));
        $iface->setValueAux(str_replace("\'","'",$valores[1]));
        $iface->setTitle($title);
        $iface->setLabel($label);
        $iface->setObrigatorio($obrigatorio);
        $iface->setSize($width,$maxlenght);
        $iface->setInfoRight($inforight);
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarCampo()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }

    /**
     * Adiciona Campo Hidden
     *
     * Fun��o para adicionar campos hidden no formul�rio.
     *
     * @param  string $name Nome do Campo
     * @param  string $value Valor do Campo
     * @return void
     */
    function adicionarHidden($name,$value) {
        $this->adicionarCampo($name,"hidden",$name,"",$value);
    }

    /**
     * Adiciona Arquivo
     *
     * Fun��o para adicionar campos file no formul�rio.
     *
     * @param  string $name Nome do Campo
     * @param  string $value Valor do Campo
     * @return void
     */
    function adicionarUploadArquivo($name,$label,$pasta,$prefixo,$obrigatorio = false,$force = false) {
        $this->_isupload = true;
        $this->adicionarCampo($name,"file",$label,$pasta,$prefixo,$obrigatorio,$force);
    }

    /**
     * Adiciona Campo Select
     *
     * Fun��o para adicionar campos select no formul�rio.
     *
     * @param string $name Nome do Campo
     * @param string $label Label do Campo
     * @param string $title T�tulo do Campo
     * @param string $value Valor Pr�-Selecionado
     * @param array $data Array com Conte�do do Select. Veja: $IUtils->MontaArraySelect();
     * @param boolean $obrigatorio Campo Obrigat�rio
     * @param string $textoescolha Adiciona um Option com value = '' com o Texto passado na vari�vel.
     * @param string $width Tamanho em pixels do Select
     * @param string $height Altura em Linhas do Select (Usado para M�ltiplos Campos)
     * @param boolean $multiplo Indica se pode haver mais de um item selecionado.
     * @return void
     */
    function adicionarSelect($name,$label,$title,$value,$data,$obrigatorio = false,$textoescolha = "",$width = "100",$height = "1",$multiplo = false,$selectCor = "",$arrCores = "") {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setName($name);
        $iface->setValue($value);
        $iface->setTitle($title);
        $iface->setValuesSelect($data);
        $iface->setSize($width,$height);
        $mostraescolha = true;
        if ($textoescolha == "") {
            $mostraescolha = false;
        }
        $iface->setMultiplo($multiplo);
        $iface->setSelectEscolha($mostraescolha, $textoescolha);
        $iface->setLabel($label);
        $iface->setObrigatorio($obrigatorio);
        $iface->setTipo("select");
        
        if ($selectCor != "") {
        	$iface->setValuesSelectCor($selectCor,$arrCores);
        }
        
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarSelect()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }

    /**
     * Adiciona Campo CheckBox
     *
     * Fun��o para adicionar campos chekcbox no formul�rio.
     *
     * @param string $name Nome do Campo
     * @param string $label Label do Campo
     * @param string $value Valor Pr�-Selecionado
     * @param array $data Array com Conte�do do CheckBox.
     * @param boolean $obrigatorio Campo Obrigat�rio
     * @return void
     */
    function adicionarCheckBox($name,$label,$data,$obrigatorio = false) {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setName($name);
        $iface->setValuesChecks($data);
        $iface->setLabel($label);
        $iface->setObrigatorio($obrigatorio);
        $iface->setTipo("checkbox");
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarCheckBox()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }

    /**
     * Adiciona Campo Radios
     *
     * Fun��o para adicionar campos radios no formul�rio.
     *
     * @param string $name Nome do Campo
     * @param string $label Label do Campo
     * @param string $value Valor Pr�-Selecionado
     * @param array $data Array com Conte�do do CheckBox.
     * @param boolean $obrigatorio Campo Obrigat�rio
     * @return void
     */
    function adicionarRadio($name,$label,$data,$obrigatorio = false) {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setName($name);
        $iface->setValuesRadio($data);
        $iface->setLabel($label);
        $iface->setObrigatorio($obrigatorio);
        $iface->setTipo("radio");
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarRadio()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }

    /**
     * Adiciona Campo TextArea
     *
     * Fun��o para adicionar campos TextArea no formul�rio.
     *
     * @param string $name Nome do Campo
     * @param string $label Label do Campo
     * @param string $value Valor Pr�-Selecionado
     * @param boolean $obrigatorio Campo Obrigat�rio
     * @param string $cols Colunas
     * @param string $rows Linhas
     * @return void
     */
    function adicionarTextarea($name,$label,$value,$obrigatorio = false,$cols = "80",$rows = "6") {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setName($name);
        $iface->setLabel($label);
        $iface->setObrigatorio($obrigatorio);
        $iface->setValue(str_replace("\'","'",$value));
        $iface->setSize($cols,$rows);
        $iface->setTipo("textarea");
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarTextarea()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }

    /**
     * Adiciona Bot�o
     *
     * Fun��o para adicionar campos Button no formul�rio.
     *
     * @param string $name Nome do Campo
     * @param string $value Valor
     * @return void
     */
    function adicionarButton($name,$value) {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $iface->setName($name);
        //$iface->setLabel($label);
        $iface->setValue(str_replace("\'","'",$value));
        $iface->setTipo("button");
        if ($this->isInput($name)) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarButton()<br>ID: $name foi adicionado duas vezes ao formul�rio. Remova um campo ou altere seu ID.");
            }
        } else {
            array_push($this->_arrinputs,$iface);
        }
    }


    /**
     * Adiciona Bot�o SUBMIT
     *
     * Fun��o para adicionar SUBMIT no formul�rio.
     *
     * @param string $idquadro ID do Quadro
     * @param string $name Nome do Campo
     * @param string $value Valor
     * @param string $acao A��o de Submit
     * @return void
     */
    function adicionarSubmit($idquadro,$name,$value,$acao) {
        $erro = false;
        if (!is_array($this->_arrquadros[$idquadro])) {
            if ($this->_debug) {
                $this->adicionarErro("adicionarSubmit()<br>Quadro: $idquadro n�o est� adicionado no formul�rio.");
            }
            $erro = true;
        }
        if (!$erro) {
            $this->adicionarButton($name,$value);
            $this->adicionarCampoAcao($name,"onclick","valida(document." . $this->_nomeform . ",'" . $acao . "',true);");
            $this->adicionarQuadroButton($idquadro,$name);
        }
    }

    /**
     * Adiciona Subtitulo
     *
     * Fun��o para adicionar Subtitulos no Formul�rio
     *
     * @param string $value Subt�tulo
     * @return void
     */
    function adicionarSubTitulo($value) {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $id = count($this->_arrinputs);
        $iface->setName("iFace_subtitulo_" . $id);
        $iface->setValue($value);
        $iface->setTipo("subtitulo");
        array_push($this->_arrinputs,$iface);
    }
    
    /**
     * Adiciona um Div
     *
     * Fun��o para adicionar um Div no Formul�rio
     *
     * @param string $value Subt�tulo
     * @return void
     */
    function adicionarDiv($value) {
        $iface = new FormularioHtml();
        $iface->setForm($this->_nomeform);
        $id = count($this->_arrinputs);
        $iface->setName("iFace_div_" . $id);
        $iface->setValue($value);
        $iface->setTipo("div");
        array_push($this->_arrinputs,$iface);
    }

    /**
     * Altera o Nome do Formul�rio
     *
     * @param string $name Nome do Formul�rio
     * @return void
     */
    protected function setForm($nome) {
        $this->_nomeform = $nome;
    }

    /**
     * Altera o Action do Formul�rio
     *
     * @param string $action Action do Formul�rio
     * @return void
     */
    public function setAction($action) {
        if ($action == "") { $action = $_SERVER['PHP_SELF']; }
        $this->_action = $action;
    }

    /**
     * Altera o M�todo do Formul�rio
     *
     * @param string $method M�todo do Formul�rio
     * @return void
     */
    protected function setMethod($method) {
        $this->_method = $method;
    }

    /**
     * Altera o OnSubmit do Formul�rio
     *
     * @param string $onsubmit OnSubmit do Formul�rio
     * @return void
     */
    protected function setOnSubmit($onsubmit) {
        $this->_onsubmit = $onsubmit;
    }

    /**
     * Escreve o C�digo de Abertura do Formul�rio.
     *
     * @return void
     */
    public function abreForm() {
       	echo "\n\n";
       	if ($this->_isupload) { $adicionarenctype = ' enctype="multipart/form-data"'; }
       	echo '<form name="' .  $this->_nomeform  . '" id="' .  $this->_nomeform  . '" method="'  . $this->_method .'" action="' .  $this->_action  . '"' . $adicionarenctype .'>';
       	echo "\n<input type='hidden' name='" .  $this->_nomeform  . "_acao' id='" .  $this->_nomeform  . "_acao'>";
       	//echo "\n<input type='hidden' name='" .  $this->_nomeform  . "_ordem' id='" .  $this->_nomeform  . "_ordem' value='" . $_POST[$this->_nomeform . "_ordem"] . "'>";
        //echo "\n<input type='hidden' name='" .  $this->_nomeform  . "_ordemtipo' id='" .  $this->_nomeform  . "_ordemtipo' value='" . $_POST[$this->_nomeform . "_ordemtipo"] . "'>";
    }

    /**
     * Escreve o C�digo de Fechamento do Formul�rio.
     *
     * @return void
     */
    public function fechaForm() {
        //SALVA NA SESS�O A VARI�VEL QUE VERIF�CA SE O FORMUL�RIO FOI SUBMETIDO OU SE FOI PRESSIONADO F5.
//        session_start();
        $_SESSION[$this->_nomeform . "_pkey"] = $this->microtime;
        echo "\n</form>";
    }

    /**
     * Escreve o C�digo de Abertura de um Quadro
     *
     * @param string $idquadro ID do quadro a ser Aberto
     * @return void
     */
    public function escreveAbreQuadro($idquadro) {
        if ($this->_arrquadros[$idquadro]["titulo"] != "") {
            echo "\n<div align='center'>\n<table class='tableMoldura' width='98%'  id='quadro_$idquadro'>";
            echo "\n\t<tr class='tableSubTitulo'>\n\t\t<td colspan='4'><h2>" . $this->_arrquadros[$idquadro]["titulo"] . "</h2></td>\n\t</tr>\n\t<tr>\n\t<td>\n\t<table width='100%' border='0'><br>";
        } else {
            echo "\n<div align='center'>\n<table width='100%'  id='quadro_$idquadro' cellpadicionaring=0 cellspacing=0><tr><td>\n\t<table width='100%' border='0' cellpadicionaring=0 cellspacing=0>";
        }
    }

    /**
     * Escreve o C�digo de Fechamento de um Quadro
     *
     * @param string $idquadro ID do quadro a ser Fechado
     * @return void
     */
    public function escreveFechaQuadro($idquadro) {
        echo "</table>\n\t" .
    			"\n\t</td></tr>\n";

        if (count($this->_arrquadros[$idquadro]["botoes"])) {
            echo "\t<tr class='tableRodapeModelo1''>\n
			\t\t<td colspan='2' align='center'>\n"; 
            foreach ($this->_arrquadros[$idquadro]["botoes"] as $botao) {
                echo $botao->desenhaHtmlForm($this->ButtonDisplayOptions) . "";
                array_push($this->_desenhou,$botao->getName());
            }

            echo "</td>\n\t</tr>\n";
        }


        echo "</table>\n
		</div>\n";
    }


    /**
     * Adiciona uma A��o Javascript para qualquer campo do formul�rio
     *
     * Exemplo:
     * <code>
     * $form->adicionarCampo("nome",(...));
     * $form->adicionarCampoAcao("nome","onclick","alert('onclick');");
     * $form->adicionarCampoAcao("nome","onblur","alert('onblur');");
     * </code>
     *
     * Poss�veis valores para tipo:
     * onclick, onchange, onblur, onmouseout, onmousemove, onkeydown, onkeypress, onkeyup
     *
     *
     * @param string $name Nome do Campo
     * @param string $tipo Tipo de A��o
     * @param string $javascript C�digo em JavaScript a ser Executado.
     * @return void
     */
    public function adicionarCampoAcao($name,$tipo,$javascript) {
        $i = 0;
        if (!$this->isInput($name)) {
            $this->adicionarErro("adicionarCampoAcao()<br>ID: " . $name . " n�o � um campo no formul�rio.",false);
        }
        foreach ($this->_arrinputs as $input) {
            $i = $i + 1;
            if (is_object($this->_arrinputs[$i])) {
                if ($this->_arrinputs[$i]->getName() == $name) {
                    $campo = $this->_arrinputs[$i];
                    $campo->adicionarEvento(strtolower($tipo),$javascript);
                    $this->_arrinputs[$i] = $campo;
                }
            }
        }
    }


    /**
     * Fun��o utilizada internamente para verificar se um bot�o faz parte de um quadro.
     *
     * @param string $name Nome do Bot�o
     * @return boolean
     */
    protected function isInRodapeQuadro($name) {
        $ret = false;
        foreach ($this->_arrquadros as $quadro) {
            foreach ($quadro["botoes"] as $botoes) {
                if ($botoes->getName() == $name) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * Fun��o utilizada para Desenhar um campo do Formul�rio.
     *
     * @param string $name Nome do Bot�o
     * @param boolean true $showtableRow Escreve a Divis�o TR
     * @param boolean treu $showtableDivision Escreve a Divis�o TD
     * @return boolean
     */
    public function desenhaCampo($name,$options = "") {
        if ($options == "") { $options = $this->inputDisplayOptions; }
        if (!is_object($name)) {
            if (!in_array($name,$this->_desenhou)) {
                foreach ($this->_arrinputs as $input) {
                    if ($input->getName() == $name) {
                        if (!$this->isInRodapeQuadro($input->getName())) {
                            echo $input->desenhaHtmlForm($options);
                            array_push($this->_desenhou,$input->getName());
                        }
                    }
                }
            } else {
                $this->adicionarErro("desenhaCampo()<br>N�o � poss�vel desenhar o ID: $name j� foi desenhado no formul�rio.");
            }
        } else {
            $input = $name;
            if (!in_array($input->getName(),$this->_desenhou)) {
                if (!$this->isInRodapeQuadro($input->getName())) {
                    echo $input->desenhaHtmlForm($options);
                    array_push($this->_desenhou,$input->getName());
                }
            } else {
                //$this->adicionarErro("desenhaCampo()<br>N�o � poss�vel desenhar o ID: " . $input->getName() . " j� foi desenhado no formul�rio.");
            }

        }
    }

    /**
     * Fun��o utilizada internamente para Desenhar campos Agrupados no Formul�rio.
     *
     * @param string $name Nome do Bot�o
     * @param boolean true $colunas Escreve a Divis�o TR
     * @return void
     */
    protected function desenhaAgrupados($campo) {
        foreach ($this->_agruparcampos as $campoagru) {
            $colunas = $campoagru["colunas"];
            if ($colunas) {
                $firstDisplayGroups = $this->FirstGroupColsDisplayOptions;
                $displaygroups = 	$this->InputGroupColsDisplayOptions;
            } else {
                $firstDisplayGroups = $this->FirstGroupDisplayOptions;
                $displaygroups = 	$this->InputGroupDisplayOptions;
            }
            if (count($campoagru[$campo])) {
                foreach ($this->_arrinputs as $input) {
                    if (in_array($input->getName(),$campoagru[$campo])) {
                        if ($input->getName() == $campo) {
                            echo $this->desenhaCampo($input,$firstDisplayGroups);

                        } else {

                            if ($this->_debug) {
                                if (in_array($input->getName(),$this->_desenhou)) {
                                    $this->adicionarErro("Fun��o Interna desenhaAgrupados()<br>N�o � poss�vel desenhar o ID: " . $input->getName() . " na posi��o correta.<br> O ID foi adicionado ao formul�rio antes da chave do agrupamento. Verifique a ordem de cria��o dos campos agrupados, ou se o ID n�o foi adicionado a outro quadro.",true);
                                }
                            }
                            echo $this->desenhaCampo($input,$displaygroups);
                        }
                    }
                }
            }
       	}
    }

    /**
     * Fun��o interna utilizada para montar o array de campos para um quadro quando n�o � informado nenhum campo no par�metro campos na fun��o adicionarQuadro, asumindo assim que todos os campos restantes ser�o adicionados nesse �ltimo quadro.
     *
     * @param string $idquadro ID do quadro a ser Desenhado.
     * @return void
     */
    protected function montaArrayQuadroCampos($idquadro) {
        $arrcampos = array();
        foreach ($this->_arrinputs as $input) {
            if (!$this->isInputInQuadro($input->getName())) {
                //echo $input->getName() . "<br>";
                array_push($arrcampos,$input->getName());
            }
        }
        return $arrcampos;
        //print_r($this->_arrquadros);
    }

    /**
     * Fun��o utilizada para desenhar um quadro espec�fico.
     *
     * @param string $idquadro ID do quadro a ser Desenhado.
     * @return void
     */
    public function desenhaQuadro($idquadro) {



        $quadro = $this->_arrquadros[$idquadro];
        $this->escreveAbreQuadro($idquadro);
         
         
        foreach ($this->_arrinputs as $input) {
            	
            //VERIFICA SE O CAMPO A SER DESENHADO PERTENCE AO QUADRO.
            if (in_array($input->getName(),$quadro["campos"])) {
                $desenhou = false;
                //VERIFICA SE EXISTEM CAMPOS AGRUPADOS
                if (count($this->_agruparcampos)) {
                    foreach ($this->_agruparcampos as $campoagru) {
                        if (count($campoagru[$input->getName()])) {
                            //CHAMA A FUN��O PARA DESENHAR OS CAMPOS AGRUPADOS.
                            $this->desenhaAgrupados($input->getName());
                            $desenhou = true;
                        }
                    }
                }
                 
                if (!$desenhou) {
                    //CASO N�O SEJAM CAMPOS AGRUPADOS DESENHA O CAMPO NORMAL.
                    echo $this->desenhaCampo($input,$this->inputDisplayOptions);
                }
            }
        }
        $strcampos = "";
        foreach ($quadro["campos"] as $campo) {
            $strcampos .= $campo  . ",";
        }
        echo "<div id='" . $idquadro . "_campos' style='display:none;'>$strcampos</div>";
        $this->escreveFechaQuadro($idquadro);
    }

    /**
     * Desenha todos os quadros e todos os campos do formul�rio.
     *
     * @return void
     */
    public function desenhaCampos() {
        $this->_desenhado = true;
        foreach ($this->_arrquadros as $quadro) {
            $this->desenhaQuadro($quadro["idquadro"]);
        }
    }

    /**
     * Desenha o Formul�rio.
     *
     * @return void
     */
    function desenhar() {
        if (!$this->_desenhado) {
            $this->_desenhado = true;
            ob_start();
            $this->abreForm();
            $this->desenhaCampos();
            $this->fechaForm();
            $html = ob_get_contents();
            ob_end_clean();
            $this->displayErros();
            $this->html = $html;
            echo $html;
        } else {
            $this->adicionarErro("desenhar()<br>Formul�rio j� foi Desenhado anteriormente.",true);
        }
    }

    /**
     * Altera o Valor do DEBUG para fazer Depura��o de Erros.
     *
     * @return void
     */
    function setDebug($value) {
        $this->_debug = $value;
    }


    /**
     * Verifica se o Formul�rio foi Enviado para a a��o passada como par�metro.
     *
     * Exemplo:
     * <code>
     * if ($form->isSubmit("enviar")) {
     *   //executa a a��o do bot�o enviar.
     * }
     * </code>
     *
     * @param string $acao A��o do Formul�rio.
     * @return void
     */
    public function isSubmit($acao,$permitirf5 = false) {
        if (!$this->_iscachedform) { 
	        if ($this->_method == "post") {
	            $varacao = $_POST[$this->_nomeform . '_acao'];
	        } else {
	            $varacao = $_GET[$this->_nomeform . '_acao'];
	        }
	        
	        $ret = true;
	        
	        if ($this->_desenhado) {
	            $this->adicionarErro("isSubmit()<br>Ordem da fun��o isSubmit deve estar antes do formul�rio ser desenhado.",true);
	        }
	        
	        
	        //VERIFICA SE O F5 FOI PRESSIONADO
	        //session_start();
	        if (!$permitirf5) {
		        $sesspkey = $_SESSION[$this->_nomeform . '_pkey'];
		        $postpkey = $_POST[$this->_nomeform . '_pkey'];
		        if ($postpkey != "") {
		            if ($postpkey != $sesspkey) {
		                 //$this->adicionarErro("isSubmit()<br>Imposs�vel fazer a��o, F5 pressionado.",false);
		                 //$ret = false;
		            }
		        }
	        }
	        
	        if ($ret) {
	            if ($varacao == $acao) {
	                if ($this->_isupload) {
	                    include('UploadFile.class.php');
	                    foreach ($this->_arrinputs as $input) {
	                        if ($input->getTipo() == "file") {
	                            $fname = $input->getName();
	                            $pasta = $input->getTitle();
	                            $prefixo = $input->getValue();
	                            $force = $input->getWidth();
	                            //echo $force;
	                            $upload = new UploadFile($_FILES[$fname],$pasta, $prefixo, $force);
	                            if($upload->doUpload() === true) {
	                                $filename = $upload->getFileNomeFinal();
	                                $this->_arrfilesuploaded[$fname] = array( "field" => $fname, "folder" => $pasta, "prefix" => $prefixo, "filename" => $filename, "completefilename" => $pasta . $filename);
	                            } else {
	                                echo $upload->getSysError();
	                                echo $upload->getSysDebug();
	                            }
	                        }
	                    }
	                }
	                $ret = true;
	            } else {
	                $ret = false;
	            }
	        }
        }
        return $ret;
    }
    /**
     * Fun��o que retorna os arquivos que foram enviados e salvos por upload.
     */
    public function getFiles() {
        return $this->_arrfilesuploaded;
    }


    /**
     * Fun��o usada internamente para adicionar um erro ao formul�rio.
     *
     * @param string $msgerro Mensagem de Erro
     * @param boolean $fatal Se � um erro Fatal = true ()
     * */
    protected function adicionarErro($msgerro,$fatal = false) {
        if ($fatal) { $this->_errors = array(); }
        $erro = array("msgerro" => $msgerro,"fatal" => $fatal);
        array_push($this->_errors,$erro);
         
        if ($fatal) {
            $this->displayErros();
            exit;
       	}
       	 
    }


    /**
     * Fun��o usada para exibir os erros.
     *
     * */
    public function displayErros() {
        if ($this->_debug) {
            if (count($this->_errors)) {
                echo "<style type='text/css'>#erros { background: #BAD0E5; color: #000000; font-size: 12px; Verdana,Arial,Helvetica,sans-serif; font-weight: bold; margin: 15px; padicionaring: 0px; padicionaring-top: 3px; padicionaring-bottom: 3px;  text-align: left; }" .
		        		".erro { background: #E6EAEE; color: #000000; font-size: 12px; Verdana,Arial,Helvetica,sans-serif; font-weight: bold; margin: 2px; padicionaring: 5px; text-align: left; }" .
		        		"</style>";
                echo "<div id='erros'>";
                foreach ($this->_errors as $erro) {
                    echo "<div class=erro>";

                    if ($erro["fatal"]) {
                        $adicionarmsg = "<span style='color: #990000;'><b>ERRO FATAL:</b></span> ";
                    } else {
                        $adicionarmsg = "ERRO: ";
                    }
                    echo $adicionarmsg . $erro["msgerro"];
                    echo "</div>";
                }
                echo "</div>";
            }
        }
    }


    /**
     * Fun��o utilizada para Desenhar o XAJAX do formul�rio e as fun��es registradas.
     */
    public function desenhaXAjax() {
         
         
         
        $ajax = new xajax("",$this->_nomeform . "_xajax_",'ISO-8859-1');
        $ajax->decodeUTF8InputOn();
         
         
         
         
        foreach ($this->_arrxajax as $xajax) {
             
            $findInput = $this->getInput($xajax['idorigem']);

            $sql = $this->getSQLXajaxQuery($xajax['idquery']);
            $fparametros = '$' . $this->_arrxajaxquery[$xajax['idquery']]["parametros"];
            $fparametros = str_replace(",",',$',$fparametros);
             
            $camposquery = $this->_arrxajaxquery[$xajax['idquery']]["campos"];
            $campos = explode(",",$camposquery);
            //echo $camposquery;

            if ($xajax['funcao'] == "redesenhar") {
                 
                $funcao = "";
                 
                if ($findInput->getTipo() == "select") {
                    $funcao =
	                'function ' . $xajax['idfuncao'] . '(' . $fparametros . '){
					    $objResponse = new xajaxResponse("ISO-8859-1");
					    global $conn;
					    //$objResponse->AddAssign("img_' . $xajax['idcampo'] . '","style.display",""); 
						$sql = "' . $sql . '"; 
						$IUtils = new FormularioUtil();
	                	$data = $IUtils->MontaArraySelect($conn,$sql,"' . $campos[0] .'","' . $campos[1] .'");
						$iface = new FormularioHtml();
			        	$iface->setForm("' . $this->_nomeform . '");
						$iface->setName("' .  $xajax['idorigem'] . '") ;
						$iface->setValue("' . $findInput->getValue() . '");
						$iface->setTitle("' . $findInput->getTitle() . '");
						$iface->setValuesSelect($data);
						$iface->setSize(' . $findInput->getWidth() .  ',' . $findInput->getHeight()  . ');
						$mostraescolha = true;
				    	$textoescolha = "' . $findInput->getSelectEscolhaValue() . '";
						if ($textoescolha == "") {
							$mostraescolha = false;
						}
						$iface->setMultiplo("' . $findInput->getMultiplo() . '");
						$iface->setSelectEscolha($mostraescolha, $textoescolha);
						$iface->setLabel("' . $findInput->getLabel() . '");
						$iface->setObrigatorio("' . $findInput->getObrigatorio() .'");
						$iface->setTipo("select");
						$iface->select();
				   		$div = $iface->getHtml();
						$objResponse->AddAssign(\'span_id_' . $xajax['idorigem'] . '\',\'innerHTML\',$div);
					    //$objResponse->AddScript(\'sleep(5000);\');
						//$objResponse->AddAssign("img_' . $xajax['idcampo'] . '","style.display","");
					    return $objResponse->getXML();
					}';
                }
                if ($findInput->getTipo() == "checkbox") {
                    $funcao =
	                'function ' . $xajax['idfuncao'] . '(' . $fparametros . '){
					    $objResponse = new xajaxResponse("ISO-8859-1");
					    global $conn;
						$sql = "' . $sql . '"; 
						$IUtils = new FormularioUtil();
	                	$data = $IUtils->MontaArrayCheckBox($conn,$sql,"' . $campos[0] .'","' . $campos[1] .'");
						$iface = new FormularioHtml();
			        	$iface->setForm("' . $this->_nomeform . '");
						$iface->setName("' .  $xajax['idorigem'] . '") ;
						$iface->setValuesChecks($data);
						$iface->setLabel("' . $findInput->getLabel() . '");
						$iface->setObrigatorio("' . $findInput->getObrigatorio() .'");
						$iface->setTipo("checkbox");
						$iface->checkbox();
				   		$div = $iface->getHtml();
						$objResponse->AddAssign(\'span_id_' . $xajax['idorigem'] . '\',\'innerHTML\',$div);
					    return $objResponse->getXML();
					}';
                }
                 
                if ($findInput->getTipo() == "radio") {
                    $funcao =
	                'function ' . $xajax['idfuncao'] . '(' . $fparametros . '){
					    $objResponse = new xajaxResponse("ISO-8859-1");
					    global $conn;
						$sql = "' . $sql . '"; 
						$IUtils = new FormularioUtil();
	                	$data = $IUtils->MontaArrayCheckBox($conn,$sql,"' . $campos[0] .'","' . $campos[1] .'");
						$iface = new FormularioHtml();
			        	$iface->setForm("' . $this->_nomeform . '");
						$iface->setName("' .  $xajax['idorigem'] . '") ;
						$iface->setValuesRadio($data);
						$iface->setLabel("' . $findInput->getLabel() . '");
						$iface->setObrigatorio("' . $findInput->getObrigatorio() .'");
						$iface->setTipo("radio");
						$iface->radio();
				   		$div = $iface->getHtml();
						$objResponse->AddAssign(\'span_id_' . $xajax['idorigem'] . '\',\'innerHTML\',$div);
					    return $objResponse->getXML();
					}';
                }

                //echo $funcao;

                if ($funcao != "") {
                    eval($funcao);
                }
                 
            }



        }
        foreach ($this->_arrxajax as $xajax) {
            $ajax->registerFunction($xajax['idfuncao']);  //38

            if ($xajax['idquery'] != "") {
                $parametros = $this->_arrxajaxquery[$xajax['idquery']]["valores"];
            } else {
                $parametros = $xajax["valores"];
            }

            $this->adicionarCampoAcao($xajax['idcampo'],$xajax['acao']," formUtilExibeImgAjax('" . $xajax['idorigem'] . "'); " . $this->_nomeform . "_xajax_" . $xajax['idfuncao'] . "(" . $parametros .");  setTimeout('formUtilOcultaImgAjax(\'" . $xajax['idorigem'] . "\')',3000); ");
        }
        $ajax->processRequests();
        $ajax->printJavascript();
    }


    /**
     * Fun��o Utilizada para adicionar � um campo espec�fico uma fun��o em XAJAX declarada fora do formul�rio.
     * Tamb�m faz o registro a fun��o em XAJAX no formul�rio.
     */
    public function adicionarCampoXajax($idcampo,$acao,$idfuncao,$valores) {
        $ajax = array( "idfuncao" => $idfuncao, "funcao" => "funcao", "idcampo" => $idcampo, "valores" => $valores, "acao" => $acao);
        array_push($this->_arrxajax,$ajax);
    }

    /**
     * Fun��o utilizada para Redesenhar um campo utilizando uma query em XAJAX.
     */
    public function adicionarXajaxRedesenhar($idorigem,$idquery,$idcampo,$acao) {
        $rand = rand(0,999);
        $nomefuncao = "redesenhar_" . $idorigem . "_" . $idcampo . "_" . $acao;
        $ajax = array( "idfuncao" => $nomefuncao, "funcao" => "redesenhar", "idquery" => $idquery,"idorigem" => $idorigem,"idcampo" => $idcampo, "acao" => $acao);
        array_push($this->_arrxajax,$ajax);
    }

    /**
     * Fun��o utilizada Internamente para retornar o SQL formatado de uma query em XJAX.
     */
    protected function getSQLXajaxQuery($idquery) {
        foreach ($this->_arrxajaxquery as $query) {
            if ($query['idquery'] == $idquery) {
                $fparametros = '$' . $query["parametros"];
                $fparametros = str_replace(",",',$',$fparametros);
                $arrparametros = explode(",",$query["parametros"]);
                $sql = $query['sql'];
                foreach ($arrparametros as $parametro) {
                    $sql = str_replace('{' . $parametro. '}','$' . $parametro,$sql);
                }
            }
        }
        return $sql;
    }

    /**
     * Fun��o utilizada para adicionar uma Query em XAJAX ao formul�rio.
     */
    public function adicionarXajaxQuery($idquery,$sql,$campos,$parametros,$valores) {
        $query = array( "idquery" => $idquery, "sql" => $sql, "campos" => $campos, "parametros" => $parametros,"valores" => $valores);
        $this->_arrxajaxquery[$idquery] = $query;
    }

    public function getNome() {
        return $this->_nomeform;
    }

    public function setInFormFieldsAsHidden($form) {
        $form->adicionarHidden($this->_nomeform . "_acao",$_POST[$this->_nomeform . "_acao"]);
       // $form->adicionarHidden($this->_nomeform . "_ordem",$_POST[$this->_nomeform . "_ordem"]);
       // $form->adicionarHidden($this->_nomeform . "_ordemtipo",$_POST[$this->_nomeform . "_ordemtipo"]);
        foreach ($this->_arrinputs as $input) {
            $valor = "";
            if ($_POST[$input->getName()]) {
                $valor = $_POST[$input->getName()];
            } else {
                $valor = $_GET[$input->getName()];
            }
            $form->adicionarHidden($input->getName(),$valor);
        }
        return $form;
    }

}


?>