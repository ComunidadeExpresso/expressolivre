<?php
/**
 * Listagem
 *
 * Implementaçao de Listagens padroes do sistema.
 */
/**
 * Listagem
 *
 * Implementaçao de Listagens padroes do sistema.
 *
 *
 * @author Jair Pereira <pereira.jair@gmail.com>
 * @since 01/06/2009
 *
 * @package Listagem
 */

include_once("ListagemColuna.class.php");
include_once("ListagemIndicador.class.php");
include_once("SqlParser.class.php");

class Listagem {

    /**
     * Identificador da Listagem.
     */
    private $idlistagem;

    /**
     * Tï¿½tulo da Listagem
     */
    private $titulo;

    /**
     * Array de Colunas.
     */
    private $colunas = array();

    /**
     * Array de Linhas de Detalhamento.
     */
    private $linhasdetalhamento = array();

    /**
     * Array de Linhas Agrupadas
     */
    private $linhasagrupamento = array();

    /**
     * Array de Dados.
     */
    private $dados = array();

    /**
     * Exibe Header das Tables. (Tï¿½tulos das Colunas).
     */
    private $showthead = true;

    /**
     * Array de Totalizadores.
     */
    private $totalizadores = array();

    /**
     * Alterado para Protegido nï¿½o serï¿½ instanciado na classe.
     */
    //protected  $form = Formulario;

    /**
     * Formulï¿½rio de Pesquisa.
     */
    //protected $formpesquisa = Formulario;

    /**
     * Agrupar Resultados Alfabeticamente (True ou False)
     */
    private $agruparAlfabeticamente = false;

	/**
	 * Array de Indicadores
	 */
    private $arrindicadores = array();

    /**
     * Array de Parï¿½metros
     */
    private $arrparametros = array();

    /**
     * Array de Agrupamentos
     */
    private $arragrupamentos = array();

    /**
     * SubListagem.
     */
    public $subListagem;

    /**
     * Indica se este objeto ï¿½ uma SubListagem.
     */
    private $issublist = false;

    /**
     * Conexao com o Banco de Dados.
     */
    private $conexao;

    /**
     * SQL da Listagem.
     */
    private $sql;

    /**
     * Exibir SubTotalizadores
     */
    private $exibesubtotais = false;

    private $exibedivcolunas = true;

    /**
     * Exibir Totalizadores
     */
    private $_exibetotalizadores = true;

    /**
     * Exibir Quantidade de Registros Encontrados
     */
    private $_exibeqtdregistros = true;

    /**
     * Modo DEBUG
     */
    private $_debug = true;

    /**
     * Array de Erros
     */
    private $_errors = array();

    /**
     * Indica se a Listagem Possui algum checkbox
     */
    private $temcheck = false;

    /**
     * Repetir Tï¿½tulo do Agrupamento.
     */
    private $_repetirtituloagrupamento = true;

    /**
     * Quantidade de Registros da Listagem Principal (Usada somente quando a listagem ï¿½ uma sublistagem.)
     */
    private $_qtdregistroslistagemprincipal = 0;

    /**
     * Gerar Arquivo CSV.
     */
    private $_temarquivocsv = false;


    /**
     * Exibir Resultados na tela. (Usado quando somente serï¿½ exibido os resultados em CSV).
     */
    private $_exiberesultados = true;

    /**
     * Gerar Automaticamente Arquivo CSV.
     */
    private $_exibearquivocsv = false;

    /**
     * Colunas Arquivo CSV.
     */
    private $_colunasarquivo = array();

    /**
     * Texto do Arquivo CSV.
     */
    private $_txtarquivocsv = "";


    /**
     * Array de Totalizadores.
     */
    private $arrtotais = array();

    /**
     * Array de SubTotalizadores
     */
    private $arrsubtotais = array();


    /**
     * Mesagem: Nenhum Resultado Encontrado.
     */
    public $msgnenhumresultado = "Nenhum resultado encontrado.";

    /**
     * Mensagem: registro(s) encontrado(s)
     */
    public $msgregistros = "registro(s) encontrado(s).";

    /**
     * Mensagem: TOTAL:
     */
    public $msgtotalizador = "TOTAL:";

    /**
     * Mensagem: SUB-TOTAL;
     */
    public $msgsubtotalizador = "SUB-TOTAL:";

    private $_mostraLegendaTopo = true;

    private $_mostraLegendaRodape = false;

    private $_exibelinhasvazias = true;

    private $_colunascsv;

    private $_isarray = false;

    private $_totalizadorprecisao = 2;

    /*
     * ID da Listagem. que foi carregado.
     */
    private $_lstoid = "";

    private $_url_base_path;

    private $_tmp_file_path = "/tmp/";


    /**
     * Construï¿½ï¿½o da Classe.
     *
     * @idlistagem Identificador da Listagem.
     * @titulo Tï¿½tulo da Listagem.
     * @conexao Conexï¿½o com o Banco de Dados.
     * @subListagem Indica se a Listagem ï¿½ uma SubListagem. (default: false)
     */
    public function __construct($idlistagem,$titulo,$conexao,$subListagem = false) {
        $this->setTitulo($titulo);
        $this->setIdListagem($idlistagem);
        if (!$subListagem) {
            $novoidlistagem = $idlistagem . "_sublist";
            $this->subListagem = new Listagem($novoidlistagem,"",$conexao,true);
            $this->subListagem->setMostrarQuantidadeRegistros(false);
            $this->subListagem->setIdListagem($novoidlistagem);
        }
        $this->setSubListagem($subListagem);
        $this->setConexao($conexao);
    }

    /**
     * Indica se a Listagem ï¿½ uma SubListagem.
     *
     * @param $value (true,false)
     */
    protected function setSubListagem($value) {
        $this->issublist = $value;
    }

    public function setTotalizadorPrecisao($value) {
        $this->_totalizadorprecisao = $value;
    }

    /**
     * Identificador da Listagem.
     *
     * @param $value (true,false)
     */
    public function setIdListagem($value) {
        $this->idlistagem = $value;
    }

    public function setExibirOpcoesDeColunas($value) {
        $this->exibedivcolunas = $value;
    }

    /**
     * Retorna o Identificador da Listagem.
     */
    public function getIdListagem() {
        return $this->idlistagem;
    }

    public function setUrlBasePath($base_path) {
    	$this->_url_base_path = $base_path;
    }

    /**
     * Funï¿½ï¿½o Interna para Alterar a Varï¿½avel de Conexï¿½o com o Banco de Dados.
     *
     * @param $conexao
     * @return unknown_type
     */
    protected function setConexao($conexao) {  $this->conexao = $conexao;  }

    /**
     * Funï¿½ï¿½o Interna para Alterar O SQL que serï¿½ executado na listagem.
     *
     * @param $sql
     * @return unknown_type
     */
    protected function setSQL($sql) {  $this->sql = $sql;  }

    /**
     * Funï¿½ï¿½o Interna para Recuperar o SQL que serï¿½ executado na listagem.
     *
     * @return unknown_type
     */
    public function getSQL() {  return $this->sql;  }

    /**
     * Funï¿½ï¿½o para Alterar o Tï¿½tulo da Listagem.
     *
     * @param $titulo
     * @return unknown_type
     */
    public function setTitulo($titulo) {  $this->titulo = $titulo;  }

    /**
     * Funï¿½ï¿½o para Alterar a forma de Exibiï¿½ï¿½o da Listagem
     *
     * @param $exiberesultados
     * @return unknown_type
     */
    public function setExibirResultadosTela($exiberesultados) {  $this->_exiberesultados = $exiberesultados;  }

    /**
     * Funï¿½ï¿½o para Alterar a exibiï¿½ï¿½o de linhas que nï¿½o exibem nenhum resultado, (linhas vazias)
     *
     * @param $exiberesultados
     * @return unknown_type
     */
    public function setExibirLinhasVazias($valor) {  $this->_exibelinhasvazias = $valor;  }

    /**
     * Funï¿½ï¿½o para Alterar o Resultado da SQL executada.
     *
     * @param $dados
     * @return unknown_type
     */
    public function setDados($dados) {
        $this->dados = &$dados;
        if (is_resource($this->dados)) {
            $this->_isarray = false;
        } else {
            $this->_isarray = true;
        }
    }


    /**
     * Funï¿½ï¿½o utilizada para Executar o SQL e Carregar os dados a serem listados.
     * @param $sql
     * @return unknown_type
     */
    public function carregar($sql) {
        $parser = new SqlParser($sql);
        $ret_parser = $parser->verificaSql();

        if ($ret_parser === false) {
            $msg = $parser->getErro();
            $this->adicionarErro($msg,true);
        }

        $ret_parser = $parser->verificaCondicoes();
        if ($ret_parser === false) {
            $msg = $parser->getErro();
            $this->adicionarErro($msg,true);
        }
        $sql = $parser->getSql();

        try {
                $this->setSQL($sql);
                $res  = pg_query($this->conexao,"begin;");
                $resu = pg_query($this->conexao,$this->sql);
                $res  = pg_query($this->conexao,"rollback;");
                if (!$resu) { throw new Exception($this->sql); }
                $this->setDados($resu);

                return $resu;

        } catch (exception $e) {
             $msg = $e->getMessage();
             $this->adicionarErro("Erro ao Executar a consulta: $msg",true);
        }

    }

    /**
     * Funï¿½ï¿½o utilizada para Executar o SQL e Carregar os dados a serem listados.
     * @param $sql
     * @return unknown_type
     */
    public function carregarIDListagem($idlistagem,$previsualizacao = false) {

        $sql = "select
                    lstoid,
                    lstversao,
                    lstidlistagem,
                    lstdescricao,
                    lstnome,
                    lsttitulo,
                    lstsql,
                    lstexibe_header,
                    lstexibe_totalizadores,
                    lstexibe_subtotais,
                    lstexibe_qtdregistros,
                    lstexibe_checkbox,
                    lstexibe_csv,
                    lstexibe_legendatopo,
                    lstexibe_legendarodape,
                    lstexibe_titagrupamento,
                    lstexibe_agrupamento_alfabetico,
                    lstagrupamento_campo,
                    lstagrupamento_titulo,
                    lstmsg_totalizador,
                    lstmsg_subtotalizador,
                    lstmsg_registrosencontrados,
                    lstmsg_nenhumresultado,
                    lstexclusao,
                    lstexibe_resultados
                from
                    listagem.listagem
                where lstidlistagem = '$idlistagem'";
        $resu = pg_query($this->conexao,$sql);

        $this->setIdListagem($idlistagem);

        $arquivo = $_SERVER['PHP_SELF'];

        if (stristr($arquivo,"cad_listagem.php")) {
           $arquivo = "null";
        }

        if (pg_num_rows($resu)) {
            $dados = pg_fetch_object($resu);

            $lstexclusao = $dados->lstexclusao;

            if (($lstexclusao) && ($arquivo != "null")) {
            	$this->_debug = true;
                $this->adicionarErro("Esta Consulta estï¿½ inativada, contate o suporte tï¿½cnico de sistemas.",true);
            }

            $lstoid = $dados->lstoid;
            $this->_lstoid = $lstoid;


        /*    if ($arquivo != "null") {
                $sql = "select lsdoid from listagem_dependencia where lsdlstoid = '$lstoid' and lsdarquivo ilike '%$arquivo%'";
                $res_dependencia = pg_query($this->conexao,$sql);
                $qtd_dependencia = pg_num_rows($res_dependencia);


                if ($qtd_dependencia == 0) {
                	$sql = "insert into listagem_dependencia (lsdlstoid,lsdarquivo,lsdacesso) values ($lstoid,'$arquivo',now())";
                    $atualiza_dependencia = pg_query($this->conexao,$sql);
                } else {
                    $listagem_dependencia = pg_fetch_object($res_dependencia);
                    $lsdoid = $listagem_dependencia->lsdoid;
                	$sql = "update listagem_dependencia set lsdacesso = now() where lsdoid = $lsdoid";
                    $atualiza_dependencia = pg_query($this->conexao,$sql);
                }
            }
            */


            $sql = html_entity_decode( $dados->lstsql, ENT_QUOTES);

            $this->sql = $sql;

           // if ($previsualizacao) { $addsql = " limit 20"; }

            $sql_par = "select
                            lspoid,
                            lsplstoid,
                            lspidparametro,
                            lsptipo,
                            lspvalor_padrao,
                            lspobrigatorio
                        from
                            listagem.listagem_parametro
                        where
                            lsplstoid = $lstoid ";
            $resu_par = pg_query($this->conexao,$sql_par);

            while ($parametro = pg_fetch_object($resu_par)) {
                $idparametro = $parametro->lspidparametro;
                $tipo = $parametro->lsptipo;
                //$valor = $parametro->lspvalor;
                $valor = "";
                $obrigatorio = $parametro->lspobrigatorio;
                $valor_padrao = $parametro->lspvalor_padrao;

                $this->adicionarParametro($idparametro,$tipo,$obrigatorio,$valor,$valor_padrao);

                if ($previsualizacao) {
                	$this->setParametro($idparametro,$valor_padrao);
                }
            }

            $sql_par = "select
                            lsioid,
                            lsilstoid,
                            lsiidindicador,
                            lsitipo,
                            lsiimagem,
                            lsilegenda,
                            lsilegenda_csv,
                            lsicondicao
                        from
                            listagem.listagem_indicador
                        where lsilstoid = $lstoid ";
            $resu_par = pg_query($this->conexao,$sql_par);

            while ($indicador = pg_fetch_object($resu_par)) {
                $idindicador = $indicador->lsiidindicador;
                $tipo        = $indicador->lsitipo;
                $imagem      = $indicador->lsiimagem;
                $condicao    = html_entity_decode($indicador->lsicondicao, ENT_QUOTES);
                $legenda_csv = html_entity_decode($indicador->lsilegenda_csv, ENT_QUOTES);
                $legenda     = html_entity_decode($indicador->lsilegenda, ENT_QUOTES);


                if ($legenda_csv == "") {
                	$legenda_csv = $legenda;
                }
                $this->adicionarIndicador($idindicador,$condicao,$tipo,$imagem,$legenda,$legenda_csv);
            }


            $this->setTitulo($dados->lsttitulo);
            $this->setMensagemTotalizador($dados->lstmsg_totalizador);
            $this->setMensagemRegistrosEncontrados($dados->lstmsg_registrosencontrados);
            $this->setMensagemSubTotalizador($dados->lstmsg_subtotalizador);
            $this->setMensagemNenhumResultado($dados->lstmsg_nenhumresultado);

            if ($dados->lstagrupamento_campo != "") {
                if ($dados->lstagrupamento_titulo == "") {
                	$lstagrupamento_titulo = "{" . $dados->lstagrupamento_campo . "}";
                } else {
                	$lstagrupamento_titulo = $dados->lstagrupamento_titulo;
                }
                if ($dados->lstexibe_titagrupamento == "t") {
                    $lstexibe_titagrupamento = true;
                } else {
                    $lstexibe_titagrupamento = false;
                }


                if ($dados->lstexibe_agrupamento_alfabetico == "t") {
                	$lstexibe_agrupamento_alfabetico = true;
                } else {
                	$lstexibe_agrupamento_alfabetico = false;
                }
            	$this->setAgrupamento("{" . $dados->lstagrupamento_campo . "}",$lstagrupamento_titulo,$lstexibe_titagrupamento);
                $this->setAgruparAlfabeticamente($lstexibe_agrupamento_alfabetico);
            }

            if ($dados->lstexibe_csv == "f") {
                $lstexibe_csv = false;
            } else {
                $lstexibe_csv = true;
            }
            if ($dados->lstexibe_resultados == "f") {
                $lstexibe_resultados = false;
            } else {
                $lstexibe_resultados = true;
            }
            if ($dados->lstexibe_totalizadores == "f") {
            	$lstexibe_totalizadores = false;
            } else {
            	$lstexibe_totalizadores = true;
            }
            if ($dados->lstexibe_subtotais == "f") {
                $lstexibe_subtotais = false;
            } else {
                $lstexibe_subtotais = true;
            }
            if ($dados->lstexibe_header == "f") {
            	$showthead = false;
            } else {
            	$showthead = true;
            }
            if ($dados->lstexibe_qtdregistros == "f") {
            	$lstexibe_qtdregistros = false;
            } else {
            	$lstexibe_qtdregistros = true;
            }
            if ($dados->lstexibe_legendatopo == "f") {
                $lstexibe_legendatopo = false;
            } else {
                $lstexibe_legendatopo = true;
            }
            if ($dados->lstexibe_checkbox == "f") {
                $lstexibe_checkbox = false;
            } else {
                $lstexibe_checkbox = true;
            }
            if ($dados->lstexibe_legendarodape == "f") {
                $lstexibe_legendarodape = false;
            } else {
                $lstexibe_legendarodape = true;
            }
            $this->_exibearquivocsv = $lstexibe_csv;
            $this->_mostraLegendaTopo = $lstexibe_legendatopo;
            $this->_mostraLegendaRodape = $lstexibe_legendarodape;
            $this->exibesubtotais = $lstexibe_subtotais;
            $this->_exibeqtdregistros = $lstexibe_qtdregistros;
            $this->showthead = $showthead;
            $this->_exibetotalizadores = $lstexibe_totalizadores;
            $this->_exiberesultados = $lstexibe_resultados;



            $sql_colunas = "select
                                lslcoid,
                                lslclstoid,
                                lslcidcoluna,
                                lslcordem,
                                lslctipo,
                                lslctitulo,
                                lslchtml,
                                lslcalign,
                                lslcwidth,
                                lslcnowrap,
                                lslcvisivel,
                                lslcexibe_csv,
                                lslccalculada,
                                lslccheckbox,
                                lslclink,
                                lslclink_condicao,
                                lslclink_blank,
                                lslctotalizador_condicao,
                                lslcsubtotalizador_condicao,
                                lslccheckbox_condicao
                            from
                                listagem.listagem_coluna
                            where
                                lslclstoid = $lstoid
                            order by lslcordem";
            $resu_colunas = pg_query($this->conexao,$sql_colunas);

            while ($coluna = pg_fetch_object($resu_colunas)) {
               // print_r($coluna);
                $idcoluna = $coluna->lslcidcoluna;
                $tipo = $coluna->lslctipo;
                $titulo = $coluna->lslctitulo;
                $html = $coluna->lslchtml;
                $align = $coluna->lslcalign;
                $width = $coluna->lslcwidth;
                $condicao_totalizador = $coluna->lslctotalizador_condicao;
                $condicao_subtotalizador = $coluna->lslcsubtotalizador_condicao;
                $link = $coluna->lslclink;
                $link_condicao = $coluna->lslclink_condicao;

                $idcoluna       =  html_entity_decode($idcoluna, ENT_QUOTES);
                $titulo         =  html_entity_decode($titulo, ENT_QUOTES);
                $html           =  html_entity_decode($html, ENT_QUOTES);
                $link           =  html_entity_decode($link, ENT_QUOTES);
                $link_condicao  =  html_entity_decode($link_condicao, ENT_QUOTES);

                if ($coluna->lslccalculada == "t") {
                    $lslccalculada = true;
                } else {
                    $lslccalculada = false;
                }
                if ($coluna->lslccheckbox == "t") {
                    $lslccheckbox = true;
                } else {
                    $lslccheckbox = false;
                }
                if ($coluna->lslcnowrap == "t") {
                    $lslcnowrap = true;
                } else {
                    $lslcnowrap = false;
                }
                if ($coluna->lslcvisivel == "t") {
                    $lslcvisivel = true;
                } else {
                    $lslcvisivel = false;
                }
                if ($coluna->lslclink_blank == "t") {
                    $lslclink_blank = true;
                } else {
                    $lslclink_blank = false;
                }

                if ($coluna->lslcexibe_csv == "t") {
                    $lslcexibe_csv = true;
                } else {
                    $lslcexibe_csv = false;
                }

                if ($lslcexibe_csv) {
                	$this->_colunascsv .= "$idcoluna,";
                }


                if (($tipo == "text") || ($tipo == "data") || ($tipo == "hora") || ($tipo == "int") || ($tipo == "moeda")) {

                    if (($tipo == "int") && ($lslccheckbox)) {
                        if ($lstexibe_checkbox) {
                            $this->adicionarColunaCheckBox($idcoluna,$titulo,$html,$coluna->lslccheckbox_condicao);
                        }
                    } else {
                        //echo "idcoluna: $idcoluna -> $lslcnowrap";
                        $this->adicionarColuna($idcoluna,$titulo,$html,$tipo,$align,$width,$lslcnowrap,$lslcvisivel);
                    }

                    if ($link != "") {
                    	$this->adicionarLink($idcoluna,$link,$link_condicao,$lslclink_blank);
                    }

                    if ($lslccalculada) {
                    	$this->adicionarTotalizador($idcoluna,$condicao_totalizador,$condicao_subtotalizador);
                    }
                }
            }

        } else {
        	$this->_debug = true;
            $this->adicionarErro("Listagem nï¿½o encontrada.");
        }


    }

    public function getQuantidadeRegistros() {
        if ($this->_isarray) {
          $ret = count($this->dados);
        } else {
           if (count($this->dados)) {
                $ret = pg_num_rows($this->dados);
           } else {
                $ret = 0;
           }
        }
    //    if ($this->issublist) {
            if (is_object($this->subListagem)) {
                $ret = $ret + $this->subListagem->getQuantidadeRegistros();
            }
      //  }
        return $ret;
    }

    /**
     * Funï¿½ï¿½o para Alterar a Mensagem de Registros Encontrados.
     *
     * @param $msg
     */
    public function setMensagemRegistrosEncontrados($msg) { $this->msgregistros = $msg; }

    /**
     * Funï¿½ï¿½o para Alterar a Mensagem de Total de Registros Encontrados.
     *
     * @param $msg
     */
    public function setMensagemTotalizador($msg) { $this->msgtotalizador = $msg; }

    /**
     * Funï¿½ï¿½o para Alterar a Mensagem de Sub-Total de Registros Encontrados
     *
     * @param $msg
     */
    public function setMensagemSubTotalizador($msg) { $this->msgsubtotalizador = $msg; }

    /**
     * Funï¿½ï¿½o para Alterar a Mensagem de "Nenhum Resultado Encontrado".
     *
     * @param $msg
     */
    public function setMensagemNenhumResultado($msg) { $this->msgnenhumresultado = $msg; }

	/**
	 * Funï¿½ï¿½o que Indica se a Listagem ï¿½ Agrupada Alfabeticamente
	 *
	 * @param value (true ou false)
	 */
    public function setAgruparAlfabeticamente($value) {
        $this->agruparAlfabeticamente = $value;
    }

    /**
     * Funï¿½ï¿½o para Alterar a Opï¿½ï¿½o de Exibir os Sub-Totais de uma Listagem.
     *
     * @param $value (true ou false)
     */
    public function setMostrarSubTotais($value) {
        $this->exibesubtotais = $value;
    }

    public function setMostrarTotal($value) {
        $this->_exibetotalizadores = $value;
    }

    public function setMostrarLegendaTopo($value) {
        $this->_mostraLegendaTopo = $value;
    }

    public function setMostrarLegendaRodape($value) {
        $this->_mostraLegendaRodape = $value;
    }


    /**
     * Funï¿½ï¿½o para Alterar a Opï¿½ï¿½o de Exibir a Quantidade de Registros Retornados da Listagem.
     *
     * @param $exibir (true ou false)
     */
    public function setMostrarQuantidadeRegistros($exibir) {
        $this->_exibeqtdregistros = $exibir;
    }

    /**
     * Funï¿½ï¿½o para adicionar uma Coluna na Listagem.
     *
     * @param $idcoluna
     * @param $tipo
     * @param $titulo
     * @param $html
     * @param $width
     * @param $nowrap
     * @param $valign
     * @param $visivel
     * @return unknown_type
     */
    function adicionarColuna($idcoluna,$titulo,$html,$tipo = "text",$align = "",$width = "",$nowrap = false,$visivel = true) {
        $erro = false;
        if ($this->isColuna($idcoluna)) {
        	$erro = true;
        	$this->adicionarErro("addColuna()<br>$idcoluna jï¿½ foi adicionada na Listagem.");
        }
        if (!$erro) {
	        $coluna = new ListagemColuna($idcoluna,$tipo,$titulo,$html,$width,$nowrap,$align);
	        $coluna->setVisibilidade($visivel);
	        $coluna->setIdListagem($this->idlistagem);
	        array_push($this->colunas,$coluna);
        }
    }

    function adicionarColunaCalculada($idcoluna,$tipo,$html,$condicao_total = "1",$condicao_subtotal = "1") {
    	$erro = false;
        if ($this->isColuna($idcoluna)) {
        	$erro = true;
        	$this->adicionarErro("addColuna()<br>$idcoluna jï¿½ foi adicionada na Listagem.");
        }
        if (!$erro) {
    		$this->adicionarColuna($idcoluna,$tipo,$idcoluna,$html,"",true,"",false);
    		$this->adicionarTotalizador($idcoluna,$condicao_total,$condicao_subtotal);
        }
    }

    function adicionarLinhaDetalhamento($idlinha,$tipo,$html,$align = "left") {
        $width = "";
        $nowrap = false;
        $valign = $align;
        $coluna = new ListagemColuna($idlinha,$tipo,"",$html,$width,$nowrap,$valign);
        array_push($this->linhasdetalhamento,$coluna);
    }

    /**
     * Funï¿½ï¿½o para Adicionar uma Coluna com CheckBoxes.
     *
     * @param $idcoluna
     * @param $titulo
     * @param $html
     * @param $condicao
     * @param $width
     * @param $nowrap
     * @param $valign
     * @return unknown_type
     */
    function adicionarColunaCheckBox($idcoluna,$titulo,$value,$condicao = "",$width = "1%",$nowrap = false,$valign = "center") {
        $tipo = "check";
        $html = "{" . $value . "}";
        $coluna = new ListagemColuna($idcoluna,$tipo,$titulo,$html,$width,$nowrap,$valign);
        $coluna->setCondicao($condicao);
        $coluna->setIdListagem($this->idlistagem);
        array_push($this->colunas,$coluna);
    }

    /**
     * Funï¿½ï¿½o utilizada Internamente para verificar se o idcoluna passado jï¿½ foi adicionado a listagem.
     *
     * @param $idcoluna
     * @return unknown_type
     */
    protected function isColuna($idcoluna) {
        $ret = false;
        foreach ($this->colunas as $i => $col) {
            if ($col->getIdColuna() == $idcoluna) {
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * Adiciona um Link para uma ou mais Colunas.
     *
     * @param $idcolunas (Ids das colunas separados por vï¿½rgula)
     * @param $link Link para ser redirecionado.
     * @param $target
     * @return unknown_type
     */
    public function adicionarLink($idcolunas,$link,$condicao = "1",$targetblank = false,$popup = false) {
        $colunas = explode(",",$idcolunas);
        foreach ($colunas as $idcoluna) {
            $erro = false;
            if (!$this->isColuna($idcoluna)) {
                $this->adicionarErro("addLink()<br>$idcoluna nï¿½o ï¿½ uma coluna adicionada na Listagem.",false);
                $erro = true;
            }
            if (!$erro) {
                foreach ($this->colunas as $i => $col) {
                    if ($col->getIdColuna() == $idcoluna) {
                        $this->colunas[$i]->setLink($link,$condicao,$targetblank,$popup);
                    }
                }
            }
        }
    }

    public function RemoverColuna($idcolunas){
    	$colunas = explode(",",$idcolunas);
        foreach ($colunas as $idcoluna) {
            $erro = false;
            if (!$this->isColuna($idcoluna)) {
                $this->adicionarErro("addColunaCor()<br>$idcoluna nï¿½o ï¿½ uma coluna adicionada na Listagem.",false);
                $erro = true;
            }
            if (!$erro) {
                foreach ($this->colunas as $i => $col) {
                    if ($col->getIdColuna() == $idcoluna) {
                        unset($this->colunas[$i]);
                    }
                }
            }
        }
    }


    public function adicionarCor($idcolunas,$cor,$condicao = "1") {
        $colunas = explode(",",$idcolunas);
        $cor = str_replace("#","",$cor);
        foreach ($colunas as $idcoluna) {
            $erro = false;
            if (!$this->isColuna($idcoluna)) {
                $this->adicionarErro("addColunaCor()<br>$idcoluna nï¿½o ï¿½ uma coluna adicionada na Listagem.",false);
                $erro = true;
            }
            if (!$erro) {
                foreach ($this->colunas as $i => $col) {
                    if ($col->getIdColuna() == $idcoluna) {
                        $this->colunas[$i]->setCor($cor,$condicao);
                    }
                }
            }
        }
    }

    /**
     * Funï¿½ï¿½o para Adicionar um Indicador (Legenda).
     *
     * @param $idindicador Cï¿½digo Interno de Identificaï¿½ï¿½o do Identificador
     * @param $condicao Condiï¿½ï¿½o em PHP para Exibir o Indicador.
     * @param $tipo R,Q,T (REDONDO,QUADRADO,TRIANGULO)
     * @param $codigoimagem (1 a 19).
     * @param $legenda Legenda
     * @return unknown_type
     */
    function adicionarIndicador($idindicador,$condicao,$tipo = "R",$codigoimagem = "1",$legenda = "",$tamanho = "1") {
        $Indicador = new ListagemIndicador($idindicador,$condicao,$tipo,$codigoimagem,$legenda,$tamanho);
        $Indicador->setUrlBasePath($this->_url_base_path);
        if (!isset($this->arrindicadores[$idindicador])) { $this->arrindicadores[$idindicador] = array(); }
        array_push($this->arrindicadores[$idindicador],$Indicador);
    }

    /**
     * Funï¿½ï¿½o para Desenhar a Listagem.
     *
     * @return HTML
     */
    function desenhar() {
        $html = "";
        $this->incluiCssJavaScript($this->_url_base_path);
        if ($this->_lstoid != "") {
            $this->formataSQLParametros();
            $sql = $this->getSQL();
            $sql =  html_entity_decode($sql, ENT_QUOTES);
            $this->carregar($sql);

        }

        if ($this->_exibearquivocsv) {
            	$this->gerarArquivoXLS($this->_colunascsv);
        }
        $this->escreveErros();
        $this->escreveAbreList();
        $this->escreveLinhas();
        $this->escreveFechaList();
    }

    function incluiCssJavascript($addpath = "") {
    	echo "\n\n<script language=\"Javascript\" type=\"text/javascript\" src=\"$addpath/includes/js/mascaras.js\"></script>\n
<script language=\"Javascript\" type=\"text/javascript\" src=\"$addpath/includes/js/validacoes.js\"></script>\n
<script language=\"Javascript\" type=\"text/javascript\" src=\"$addpath/includes/js/auxiliares.js\"></script>\n
<script language=\"Javascript\" type=\"text/javascript\" src=\"$addpath/includes/js/calendar.js\"></script>\n
<script language=\"Javascript\" type=\"text/javascript\" src=\"$addpath/includes/js/FormularioUtil.js\"></script>\n
<link rel=\"stylesheet\" href=\"$addpath/includes/css/base_form.css\" media=\"screen\"></link>\n
<link rel=\"stylesheet\" href=\"$addpath/includes/css/calendar.css\" media=\"screen\"></link>\n\n";

    }

    protected function escreveLegenda() {
        if ($this->_exiberesultados) {
        	if (count($this->arrindicadores)) {
                $colspan=0;

                echo  "\n\t<table width='100%' class='TableMoldura''>
       	 			   \n\t\t<tr class='tableSubTitulo'>
    	               \n\t\t\t<td><h3>Legenda</h3></td>
    	               \n\t\t</tr>";
                echo "<tr><td align=left colspan='$colspan'>";
                foreach ($this->arrindicadores as $idindicador => $indicadores) {
                    $exibelinha = false;
                    foreach ($indicadores as $indicador) {
                         if ($indicador->getLegenda() != "") {
                            $exibelinha = true;
                         }
                    }
                    if ($exibelinha) {
                        echo "<br>&nbsp;&nbsp;";
                        $addlegenda = "";
                        foreach ($indicadores as $indicador) {
                            if ($indicador->getLegenda() != "") {
                                $addlegenda .= str_replace("{indicador_substituirpasta}","",$indicador->getHtml()) . " " . $indicador->getLegenda() . " | ";
                            }
                        }
                        $addlegenda = substr($addlegenda,0,strlen($addlegenda) - 3);
                        echo $addlegenda;
                        echo "<br>";
                    }
                }
                echo "<br></td></tr></table>";
            }
        }
    }

    /**
     * Funï¿½ï¿½o que desenha o cabeï¿½alho da Listagem.
     *
     * @return HTML
     */
    protected function escreveAbreList() {
        $colspan = count($this->colunas);

        $htmldivs = "";
        $html_icone = "";

        if (!$this->issublist) {
        	if ($this->_mostraLegendaTopo) {
        		$this->escreveLegenda();
        	}
            $addclass= " class='TableMoldura'";

            if ($this->_exiberesultados) {

            	if ($this->exibedivcolunas) {
                $html_icone = '<b>[<img id="'. $this->idlistagem . '_img_visivel" src="' . $this->_url_base_path . '/images/icones/maisTransparente.gif" OnClick="ListagemShowHide(event,\''. $this->idlistagem . '\',\''. $this->idlistagem . '_img_visivel\');" OnMouseOver="this.style.cursor=\'pointer\';" OnMouseOut="this.style.cursor=\'default\';">]</b>';
                $html_icone_2 = '<b>[<img id="'. $this->idlistagem . '_img_visivel_2" src="' . $this->_url_base_path . '/images/icones/menosTransparente.gif" OnClick="ListagemShowHide(event,\''. $this->idlistagem . '\',\''. $this->idlistagem . '_img_visivel\');" OnMouseOver="this.style.cursor=\'pointer\';" OnMouseOut="this.style.cursor=\'default\';">]</b>';

               // $html_icone .= '&nbsp;&nbsp;<b>[<img id="'. $this->idlistagem . '_img_grafico_visivel" src="images/icones/t1/fileGrafico.jpg" OnClick="ListagemShowHide(event,\''. $this->idlistagem . '\',\''. $this->idlistagem . '_img_grafico_visivel\');" OnMouseOver="this.style.cursor=\'pointer\';" OnMouseOut="this.style.cursor=\'default\';">]</b>';

                $cnt = 0;
                $htmlchecks = "";

                foreach ($this->colunas as $coluna) {
                	$titulo = $coluna->getNome();
                    $idcoluna = $coluna->getIdColuna();
                    $idlistagem = $this->idlistagem;
                    $visivel = $coluna->getVisibilidade();
                    $addvisivel = "";
                    if ($visivel) { $addvisivel = "checked"; }

                    $adddisabled = "";
                    if ($cnt == 0) { $adddisabled = " disabled"; $cnt = $cnt + 1; }

                    $htmlchecks .= '<input type="checkbox" class="checkbox" name="' . $idlistagem . '_ck_visivel_' . $idcoluna . '" id="' . $idlistagem . '_ck_visivel_' . $idcoluna . '" OnClick=" document.getElementById(\'' . $idlistagem . '_div_visivel\').style.display = \'none\'; document.getElementById(\'' . $idlistagem . '_div_visivel_load\').style.display = \'\'; alinhaDivDir(\'' .$idlistagem. '_div_visivel_load\'); setTimeout(\'ListagemExibeOcultaColuna(\\\'' . $idlistagem. '\\\',\\\'' .$idcoluna . '\\\');\')" value="' .$idcoluna . '" ' . $addvisivel .$adddisabled . '> ' . $titulo . '<br>';

                }


                $htmldivs = '<div id="' . $idlistagem. '_div_visivel" class="div_visivel" style="display:none;">
                                    <table width="100%">
                                        <tr>
                                            <td align="right">' .$html_icone_2. '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-left:5px;">'
.$htmlchecks .
'

                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="' . $idlistagem. '_div_visivel_load" class="div_visivel" style="display:none;">
                                    <table width="100%">
                                        <tr>
                                            <td style="padding-left:5px;">
                                                <img src="images/progress4.gif"></img> Carregando...
                                            </td>
                                        </tr>
                                    </table>
                                </div>';

                   }
            	}


        }

        echo  "\n\t$htmldivs<table id='Listagem_" . $this->getIdListagem() . "' width='100%'$addclass>
	             \n\t\t<tr class='tableSubTitulo'>
	             \n\t\t\t<td  colspan='$colspan'><div style='float: right;'>$html_icone&nbsp;&nbsp;</div><h3>"  . $this->titulo . "</h3></td>
	             \n\t\t</tr>";
    }

    /**
     * Funï¿½ï¿½o que desenha o HTML que fecha a listagem.
     *
     * @return HTML
     */
    protected function escreveFechaList() {
        echo  "\n\t</table>";

        if (!$this->issublist) {
        	if ($this->_mostraLegendaRodape) {
        		$this->escreveLegenda();
        	}
        }
    }

    public function setAgrupamento($idagrupamento,$titulo = "",$repetirtitulo = false) {
        $width = "";
        $nowrap = false;
        $align = "left";
      	//$html = "{" . $idagrupamento .  "}";
      	$html = $idagrupamento;
        if ($titulo == "") {
        	$titulo = "{" . $idagrupamento . "}";
        	$repetirtitulo = true;
        }
        $this->_repetirtituloagrupamento = $repetirtitulo;
        $tipo = "";
        $coluna = new ListagemColuna($idagrupamento,$tipo,$titulo,$html,$width,$nowrap,$align);
        $this->linhasagrupamento = array();
        array_push($this->linhasagrupamento,$coluna);
    }

    public function setParametro($idparametro,$valor) {
    	foreach ($this->arrparametros as $k => $parametro) {
    		if ($parametro['idparametro'] == $idparametro) {
                $this->arrparametros[$k]["valor"] = $valor;
            }
    	}
    }

    public function adicionarParametro($idparametro,$tipo,$obrigatorio = false,$valor = "",$valortestes = "") {
        $parametros = array();
        $parametros["idparametro"] = $idparametro;
        $parametros["tipo"] = $tipo;
        $parametros["valor"] = $valor;
        $parametros["obrigatorio"] = $obrigatorio;
        $parametros["valor_testes"] = $valortestes;

        if ($this->_debug) {
        	if (($tipo != "text") && ($tipo != "int") && ($tipo != "data")) {
        		$this->adicionarErro("Parï¿½metro '$idparametro' do tipo '$tipo' nï¿½o ï¿½ vï¿½lido.");
                return false;
        	}
        }

        if (!$this->isParametro($idparametro)) {
            array_push($this->arrparametros,$parametros);
        } else {
            if ($this->_debug) {
        	   $this->adicionarErro("Parï¿½metro '$idparametro' jï¿½ estï¿½ adicionado a Listagem.");
            }
        }
    }

    public function getSQLParametros() {
        $sql = $this->sql;
        $sqlparametros = array();
        $abre_chaves = explode("{",$sql);
        foreach ($abre_chaves as $chave) {
            $_chaves = array();
            if (stristr($chave,"}")) {
                $_chaves = explode("}",$chave);
                $sqlparametros[] = $_chaves[0];
            }
        }
        return $sqlparametros;
    }

    protected function formataSQLParametros() {
        $sql = $this->sql;

        $parametrosconsulta = $this->getSQLParametros();

        foreach ($parametrosconsulta as $parametro_consulta) {
            $achou = false;
            $achou_consulta = false;
            foreach ($this->arrparametros as $parametro_base) {
                if ($parametro_base["idparametro"] == $parametro_consulta) {
                	$achou = true;
                }
                if (in_array($parametro_base["idparametro"],$parametrosconsulta)) {
                    $par_base = $parametro_base["idparametro"];
                	$achou_consulta = true;
                }
            }
            if (!$achou) {
            	$this->_debug=true;
                $this->adicionarErro("Parï¿½metro '$parametro_consulta' usado na consulta nï¿½o foi adicionado no cadastro.",true);
            }
            if (!$achou_consulta) {
                $this->_debug=true;
                $this->adicionarErro("Parï¿½metro '$par_base' cadastrado nï¿½o ï¿½ usado na consulta.",true);
            }
        }


    	foreach ($this->arrparametros as $parametro) {
            $idparametro = $parametro["idparametro"];
            $valor = $parametro["valor"];
            $obrigatorio = $parametro["obrigatorio"];
            if (($obrigatorio == "t") && ($valor == "")) {
                $this->_debug=true;
            	$this->adicionarErro("Parï¿½metro '$idparametro ' ï¿½ obrigatï¿½rio.",true);
            }
            if (($parametro["tipo"] == "data") || ($parametro["tipo"] == "text")) {
            	$valor = "'" . $valor . "'";
            }
            if (($obrigatorio == "f") && ($parametro["valor"] == "")) {
                $valor = "null";
            }
    		$sql = str_replace("{" . $idparametro. "}",$valor,$sql);
    	}
        $this->sql = $sql;
    }

    protected function isParametro($idparametro) {
    	$ret = false;
        foreach ($this->arrparametros as $parametro) {
            if ($parametro['idparametro'] == $idparametro) {
                $ret = true;
            }
        }
        return $ret;
    }

    protected function isTotalizador($idcoluna) {
        $ret = false;
        foreach ($this->totalizadores as $totalizador) {
            if ($totalizador['idcoluna'] == $idcoluna) {
                $ret = true;
            }
        }
        return $ret;
    }

    function adicionarTotalizador($idcolunas,$condicaototal = "1",$condicaosubtotal = "1") {

        $colunas = explode(",",$idcolunas);
        foreach ($colunas as $idcoluna) {
            $erro = false;
            if (!$this->isColuna($idcoluna)) {
                $this->adicionarErro("adicionarTotalizador()<br>$idcoluna nï¿½o ï¿½ uma coluna adicionada na Listagem.",false);
                $erro = true;
            }
            if (!$erro) {

                $totalizador = array();
                $totalizador["idcoluna"] = $idcoluna;
                $totalizador["condicaototal"] = $condicaototal;
                $totalizador["condicaosubtotal"] = $condicaosubtotal;
                $this->totalizadores[$idcoluna] = $totalizador;

            }
        }

    }

    protected function escreveArquivo($arquivo) {
        if ($this->_temarquivocsv) {
        	fwrite($arquivo, $this->_txtarquivo);
            $this->_txtarquivo = "";
        }
    }


    /**
     * Funï¿½ï¿½o para desenhar as Linhas da Listagem.
     *
     * @return HTML
     */
    protected function escreveLinhas() {
        $html = "";
        $colspan = count($this->colunas);
        $idtd = 0;
        $handle = "";
        //$nomeform = $this->formpesquisa->getNome();

        if ($this->_temarquivocsv) {
            if (!$this->issublist) {
                $tmpfname = $this->_tmp_file_path . "Listagem_" . $this->idlistagem . "_" . time() . ".csv";
                $handle = fopen($tmpfname, "a+");
            }
        }

        if ($this->showthead) {
            if ($this->_exiberesultados) {
                echo  "\n\t\t<tr class='tableTituloColunas'>";
                foreach ($this->colunas as $col) {
                    $fwidth = $col->getWidth(true);
                    $addvisivel = "";
                    if (!$col->getVisibilidade()) {
                        $addvisivel = " style='display: none;' ";
                    }
                   // if ($col->getVisibilidade()) {
                    	echo  "\n\t\t\t<td id='td_tit_" . $col->getIdColuna() . "' $fwidth align='center'$addvisivel><h3>". $col->getNome() . "</h3></td>";
                   // }
                }
                echo  "\n\t\t</tr>";
            }
            if ($this->_temarquivocsv) {
	            foreach ($this->_colunasarquivo as $idcoluna) {
		    	   foreach ($this->colunas as $coluna) {
		    	   	  if ($coluna->getIdColuna() == $idcoluna) {
		    	   	  	$this->_txtarquivo .= '"' . $coluna->getNome() . '"' . ";";
		    	   	  }
		    	   }
		    	}
		    	$this->_txtarquivo .= "\n";
                $this->escreveArquivo($handle);

            }
        }

        if (!$this->issublist) {
            $cnt = 0;
            $this->subListagem->setQuantidadeRegistrosListagemPrincipal($this->getQuantidadeRegistros());
        } else {
            $cnt = $this->getQuantidadeRegistrosListagemPrincipal();
        }
        $temcheck = false;
        if (count($this->dados)) {

            $arrtotais = array();
            $arrsubtotais = array();
            $arrsubtotais_todos = array();
            $lastagrupamento = "";
            $cntagrupamentos = 0;
            $arragrupamentos = array();
            $newagrupamento = "";

            $mostroutituloagrupamento = false;

            if (!$this->_isarray) {
                pg_result_seek($this->dados, 0);
            }
            $class= "";

            $qtdlinhas = 0;

            $qtd_registros = $this->getQuantidadeRegistros();


            for ($qtd_reg = 1;$qtd_reg <= $qtd_registros; ++$qtd_reg) {

                if ($this->_isarray) {
                    if ($qtd_reg == 1) {
                        reset($this->dados);
                        $linha = current($this->dados);
                    } else {
                        $linha = next($this->dados);
                    }
                } else {
                    $linha = pg_fetch_array($this->dados);
                }

                $cnt = $cnt + 1;

                $qtdlinhas = $qtdlinhas + 1;

                $class = ( $class == "tdc" ) ? "tde" : "tdc";

                if (count($this->linhasagrupamento)) {
                    foreach ($this->linhasagrupamento as $coluna) {
                        $fwidth = $coluna->getWidth(true);
                        $falign = $coluna->getVAlign(true);
                        $addnowrap = "";
                        $align = $coluna->getAlign();
                        if ($coluna->getNowrap()) { $addnowrap = "nowrap"; }
                        $htmlcoluna = $coluna->getHtml($linha,$this->arrindicadores,$class,$qtdlinhas - 1);
                        if ($coluna->getNome() == "") {
                        	$tituloagrupamento = $htmlcoluna;
                        } else {
                        	$tituloagrupamento = $coluna->replaceValorLinha($coluna->getNome(),$linha);
                        }

                        if ($this->agruparAlfabeticamente) {
                            $tituloagrupamento = strtoupper(substr($tituloagrupamento,0,1));
                            $newagrupamento = $tituloagrupamento;
                            $align="center";
                        } else {
                        	$newagrupamento = strtoupper($htmlcoluna);
                        }



                        //AGRUPAMENTO
                        $cntcheckspan = 0;
                        if ($newagrupamento != $lastagrupamento) {
                            $cntagrupamentos = $cntagrupamentos + 1;

                            $arragrupamentos[$cntagrupamentos] = $newagrupamento;

                            //TOTALIZADOR SUB-TOTAL DO AGRUPAMENTO.
                            if ($this->exibesubtotais) {
                                if (count($this->totalizadores)) {
                                    if ($cntagrupamentos != 1) {
                                        if ($this->_exiberesultados) {
                                            echo   "\n\t\t<tr class='tableRodapeModelo2'>";
                                        }
                                        $mostroutotal = false;
                                        foreach ($this->colunas as $coluna) {
                                             $idcol = $coluna->getIdColuna();
                                             if ($this->isTotalizador($coluna->getIdColuna())) {
                                                 if (!$mostroutotal) {
                                                     $addtexto = "<h3>" . $this->msgsubtotalizador . "</h3>";
                                                     $txtarquivotexto = $this->msgsubtotalizador ;
                                                     $mostroutotal = true;
                                                 } else {
                                                     $addtexto = "";
                                                 }
                                                 if ($cntcheckspan) {
                                                 	if ($this->_temarquivocsv) {
	                                                 	for ($j=1; $j<= $cntcheckspan; ++$j) {
	                                                 		if ($j == $cntcheckspan) {
	                                                 			$this->_txtarquivo .= $txtarquivotexto;
	                                                 		}
	                                                 		$this->_txtarquivo .= ";";
	                                                 	}
                                                 	}
                                                 }
                                                 $cntcheckspan = 0;
                                                 $addvisivel = "";
                                                 if (!$coluna->getVisibilidade()) {
                                                     $addvisivel = " style='display: none;' ";
                                                 }
                                                 if ($coluna->getTipo() == "hora") {
                                                      $subtotal_valor = $arrsubtotais[$coluna->getIdColuna()];
                                                 } else {
                                                     if ($this->_totalizadorprecisao != 0) {
                                                         $subtotal_valor = number_format($arrsubtotais[$coluna->getIdColuna()],$this->_totalizadorprecisao,",",".");
                                                     } else {
                                                         $subtotal_valor = $arrsubtotais[$coluna->getIdColuna()];
                                                     }
                                                 }
                                                 if ($this->_exiberesultados) {
                                                    echo  "<td  id='subtotal_" . $idcol . "_$cntagrupamentos' align='right'$addvisivel>$addtexto<h3>" . $subtotal_valor . "</h3></td>";
                                                 }
                                                 $this->_txtarquivo .= $subtotal_valor . ";";
                                                 $arrsubtotais[$coluna->getIdColuna()] = 0;
                                                 $arrsubtotais_todos[$cntagrupamentos][$coluna->getIdColuna()] = 0;
                                             } else {
                                                 $addvisivel = "";
                                                 if (!$coluna->getVisibilidade()) {
                                                    $addvisivel = " style='display: none;' ";
                                                 }
                                                 if ($this->_exiberesultados) {
                                                    echo  "<td id='subtotal_$idcol" . "_" . $cntagrupamentos . "' $addvisivel></td>";
                                                 }
                                             }
                                        }
                                       // if ($cntcheckspan) { echo  "<td colspan='$cntcheckspan'>&nbsp;</td>";}
                                        if ($this->_exiberesultados) {
                                            echo   "\n\t\t</tr>";
                                        }
                                        if ($this->_temarquivocsv) {
                                        	$this->_txtarquivo .= "\n";
                                            $this->escreveArquivo($handle);
                                        }
                                    }
                                }
                            }



                            if (($this->_repetirtituloagrupamento) || (!$mostroutituloagrupamento)) {
                                if ($this->_exiberesultados) {
    	                            echo  "\n\t\t<tr>";
    	                            echo  "\n\t\t\t<td class='tableSubTitulo' colspan='$colspan' align='$align'><h3>" . $tituloagrupamento . "</h3></td>";
    	                            echo  "\n\t\t</tr>";
                                    $class = "tdc";
                                }
	                            if ($this->_temarquivocsv) {
	                            	$this->_txtarquivo .= '"' . $tituloagrupamento . '"' . ";\n";
	                            }
	                            $mostroutituloagrupamento = true;
                            }

                        }

                    }

                }


                if (count($this->colunas)) {

                    $addvisivellinha = "";
                    if ($this->_exibelinhasvazias == false) {
                        $exibe_linha = false;
                        $addvisivellinha = " style='display: none;' ";
                        foreach ($this->colunas as $coluna) {
                            $htmlcoluna = $coluna->getHtml($linha,$this->arrindicadores,$class,$idtd);
                            if (trim($htmlcoluna) != "") {
                            	$exibe_linha = true;
                                $addvisivellinha = "";
                            }
                        }
                    }

                    //if ($exibe_linha) {
                        if ($this->_exiberesultados) {
                            echo  "\n\t\t<tr class='" . $class . "' onmouseout=\"this.className='" . $class . "'\" onmouseover=\"this.className='" . $class . "Over'\" $addvisivellinha>";
                        }
                        foreach ($this->colunas as $coluna) {
                            if ($this->_exiberesultados) {
                                $fwidth = $coluna->getWidth(true);
                                $falign = $coluna->getVAlign(true);
                                $addnowrap = "";
                                $align = $coluna->getAlign();
                                if ($coluna->getNowrap()) { $addnowrap = " nowrap"; }

        						if ($this->issublist) {
        							$idtd = $cnt -2;
        						} else {
        							$idtd = $cnt -1;
        						}
                                $htmlcoluna = $coluna->getHtml($linha,$this->arrindicadores,$class,$idtd);

                                if (($coluna->getTipo() == "check") && ($htmlcoluna != "")) { $temcheck = true; $this->temcheck = true; }
                            }
                            if ($this->isTotalizador($coluna->getIdColuna())) {
                                $condicaototal = $this->totalizadores[$coluna->getIdColuna()]["condicaototal"];
                                $condicaosubtotal = $this->totalizadores[$coluna->getIdColuna()]["condicaosubtotal"];
                                $htmlvalorcoluna = $coluna->getValorHTML($linha);
                                if ($condicaototal != "1") {
                                    $ret_total = $coluna->validaCondicao($condicaototal,$linha,false);
                                } else {
                                    $ret_total = true;
                                }

                                if ($ret_total) {
                                    $tipo_coluna = $coluna->getTipo();
                                    if (isset($arrtotais[$coluna->getIdColuna()])) {
                                        if ($tipo_coluna != "hora") {
                                            $arrtotais[$coluna->getIdColuna()] += $htmlvalorcoluna;
                                        } else {
                                            $arrhoras = array($arrtotais[$coluna->getIdColuna()],$htmlvalorcoluna);
                                            $arrtotais[$coluna->getIdColuna()] = $this->somaIntervaloHoras($arrhoras);
                                        }
                                    } else {
                                        if ($tipo_coluna != "hora") {
                                    	    $arrtotais[$coluna->getIdColuna()] = 0;
                                            $arrtotais[$coluna->getIdColuna()] += $htmlvalorcoluna;
                                        } else {
                                            $arrtotais[$coluna->getIdColuna()] = "00:00:00";
                                            $arrhoras = array($arrtotais[$coluna->getIdColuna()],$htmlvalorcoluna);
                                            $arrtotais[$coluna->getIdColuna()] = $this->somaIntervaloHoras($arrhoras);
                                        }
                                    }
                                }
                                if ($condicaosubtotal != "1") {
                                    $ret_subtotal = $coluna->validaCondicao($condicaosubtotal,$linha,false);
                                } else {
                                	$ret_subtotal = true;
                                }
                                if ($ret_subtotal) {
                                    $tipo_coluna = $coluna->getTipo();
                                    if (isset($arrsubtotais[$coluna->getIdColuna()])) {
                                        if ($tipo_coluna != "hora") {
                                            $arrsubtotais[$coluna->getIdColuna()] +=  $htmlvalorcoluna;
                                        } else {
                                            $arrhoras = array($arrsubtotais[$coluna->getIdColuna()],$htmlvalorcoluna);
                                            $arrsubtotais[$coluna->getIdColuna()] =  $this->somaIntervaloHoras($arrhoras);
                                        }
                                    } else {
                                        if ($tipo_coluna != "hora") {
                                        	$arrsubtotais[$coluna->getIdColuna()] = 0;
                                            $arrsubtotais[$coluna->getIdColuna()] +=  $htmlvalorcoluna;
                                        } else {
                                            $arrsubtotais[$coluna->getIdColuna()] = "00:00:00";
                                            $arrhoras = array($arrsubtotais[$coluna->getIdColuna()],$htmlvalorcoluna);
                                            $arrsubtotais[$coluna->getIdColuna()] =  $this->somaIntervaloHoras($arrhoras);
                                        }
                                    }
                                    if (isset($arrsubtotais_todos[$cntagrupamentos][$coluna->getIdColuna()])) {
                                        $arrsubtotais_todos[$cntagrupamentos][$coluna->getIdColuna()] += $htmlvalorcoluna;
                                    } else {
                                        if ($tipo_coluna != "hora") {
                                    	   $arrsubtotais_todos[$cntagrupamentos][$coluna->getIdColuna()] = 0;
                                        } else {
                                           $arrsubtotais_todos[$cntagrupamentos][$coluna->getIdColuna()] = "00:00:00";
                                        }
                                    }
                                }
                            }
                            $addvisivel = "";
                            if (!$coluna->getVisibilidade()) {
                            	$addvisivel = " style='display: none;' ";
                            }

                            if ($this->_exiberesultados) {
                            	echo  "\n\t\t\t<td id='td_" . $coluna->getIdColuna() . "_" . $idtd . "' " . $falign . $fwidth .  " align='$align'" . $addnowrap . $addvisivel .  ">" . nl2br($htmlcoluna)  . "</td>";
                            }
                        }



                        //ARQUIVO CSV
                        if ($this->_temarquivocsv) {
                        	foreach ($this->_colunasarquivo as $colunaarq) {
                        		foreach ($this->colunas as $coluna) {
    					    		if ($coluna->getIdColuna() == $colunaarq) {
    					    			$htmlarquivo = $coluna->getTextoArquivo($linha,$this->arrindicadores);
    					    			$this->_txtarquivo .= '"' . $htmlarquivo . '"' . ";";
    					    		}
                        		}
    			    		}
                        }
                        if ($this->_exiberesultados) {
                            echo  "\n\t\t</tr>";
                        }
                   // }
                } else {
                    $this->adicionarErro("Nenhuma coluna foi adicionada.",true);
                }


                if ($this->_temarquivocsv) {
                	$this->_txtarquivo .= "\n";
                    $this->escreveArquivo($handle);
                }

                //LINHA ADICIONAL DE OBSERVAï¿½ï¿½ES, (DETALHAMENTO)
                if (count($this->linhasdetalhamento)) {
                    foreach ($this->linhasdetalhamento as $coluna) {
                        $fwidth = $coluna->getWidth(true);
                        $falign = $coluna->getVAlign(true);
                        $addnowrap = "";
                        $align = $coluna->getAlign();
                        if ($coluna->getNowrap()) { $addnowrap = " nowrap"; }
                        $htmlcoluna = "";
                        $htmlcoluna = $coluna->getHtml($linha,$this->arrindicadores,$class,$qtdlinhas - 1);

                        if ($this->_exiberesultados) {
                            if ($htmlcoluna == "") {
                                $addvisivel = " style='display: none;' ";
                            }
                            echo  "\n\t\t<tr id='tr_" . $coluna->getIdColuna() . "_" . $idtd . "' class='" . $class . "' onmouseout=\"this.className='" . $class . "'\" onmouseover=\"this.className='" . $class . "Over'\"" . $addvisivel. ">";
                            echo  "\n\t\t\t<td id='td_" . $coluna->getIdColuna() . "_" . $idtd . "' " . $falign . $fwidth . $addnowrap . " align='$align' colspan='" . count($this->colunas) . "'>" . $htmlcoluna  . "</td>";
                            echo  "\n\t\t</tr>";

                        }
                        if ($this->_temarquivocsv) {
                        	$htmlarquivo = $coluna->getTextoArquivo($linha,$this->arrindicadores);
                        	if ($htmlarquivo != "") {
                        		$this->_txtarquivo .= $htmlarquivo . ";\n";
                        	}
	                    }
                    }

                }

                $lastagrupamento = $newagrupamento;
                //$lastval = "";
            }
        }

        $this->escreveArquivo($handle);

        if (count($this->totalizadores)) {
            if ($this->exibesubtotais) {
                if ($this->_exiberesultados) {
                    echo   "\n\t\t<tr class='tableRodapeModelo1'>";
                }
                $mostrousubtotal = false;
                $cntcheckspan = 0;
                if ($cntagrupamentos != 1) {
                    $cntagrupamentos = $cntagrupamentos + 1;
                }
                foreach ($this->colunas as $coluna) {
                     $idcol = $coluna->getIdColuna();
                     if ($this->isTotalizador($coluna->getIdColuna())) {

                         if (!$mostrousubtotal) {
                             $addtexto = "<h3>" . $this->msgsubtotalizador . "</h3>";
                             $mostrousubtotal = true;
                             $txtarquivotexto = $this->msgsubtotalizador;
                         } else {
                             $addtexto = "";
                         }
                         if ($cntcheckspan) {

                         	if ($this->_temarquivocsv) {
                             	for ($j=1; $j<= $cntcheckspan; ++$j) {
                             		if ($j == $cntcheckspan) {
                             			$this->_txtarquivo .= $txtarquivotexto;
                             		}
                             		$this->_txtarquivo .= ";";
                             	}
                         	 }
                         }
                         $cntcheckspan = 0;
                         $addvisivel = "";
                             if (!$coluna->getVisibilidade()) {
                                $addvisivel = " style='display: none;' ";
                             }
                             if ($coluna->getTipo() == "hora") {
                                $subtotal_valor = $arrsubtotais[$coluna->getIdColuna()];
                             } else {
                                 if ($this->_totalizadorprecisao != 0) {
                                    $subtotal_valor = number_format($arrsubtotais[$coluna->getIdColuna()],$this->_totalizadorprecisao,",",".");
                                 } else {
                                    $subtotal_valor = $arrsubtotais[$coluna->getIdColuna()];
                                 }
                             }
                             if ($this->_exiberesultados) {
                        	   echo  "<td id='subtotal_$idcol" . "_" . $cntagrupamentos . "' align='right'$addvisivel>$addtexto&nbsp;<h3>" . $subtotal_valor . "</h3></td>";
                             }
                             if (in_array($idcol,$this->_colunasarquivo)) {
                        	   $this->_txtarquivo .= $subtotal_valor . ";";
                             }
                     } else {
                         $addvisivel = "";
                         if (!$coluna->getVisibilidade()) {
                            $addvisivel = " style='display: none;' ";
                         }
                         if ($this->_exiberesultados) {
                            echo  "<td id='subtotal_$idcol" . "_" . $cntagrupamentos . "' $addvisivel></td>";
                         }
                         if ($this->_temarquivocsv) {
                            if (in_array($idcol,$this->_colunasarquivo)) {
                                $this->_txtarquivo .= ";";
                            }
                         }
                     }
                }
                if ($this->_exiberesultados) {
                    echo   "\n\t\t</tr>";
                }
                if ($this->_temarquivocsv) {
                	$this->_txtarquivo .= "\n";
                }
            }



            if ($this->_exibetotalizadores) {
                if ($this->_exiberesultados) {
                    echo   "\n\t\t<tr class='tableRodapeModelo2'>";
                }
                $mostroutotal = false;
                $cntcheckspan = 0;
                foreach ($this->colunas as $coluna) {


                     $idcol = $coluna->getIdColuna();
                     if ($this->isTotalizador($coluna->getIdColuna())) {
                         if (!$mostroutotal) {
                             $addtexto = "<h3>" . $this->msgtotalizador .  "</h3>";
                             $mostroutotal = true;
                             $txtarquivotexto = $this->msgtotalizador;
                         } else {
                             $addtexto = "";
                         }

                         if ($cntcheckspan) {
                            if ($this->_temarquivocsv) {
                                for ($j=1; $j<= $cntcheckspan; ++$j) {
                                    if ($j == $cntcheckspan) {
                                        $this->_txtarquivo .= $txtarquivotexto;
                                    }
                                    $this->_txtarquivo .= ";";
                                }
                            }
                         }
                         $cntcheckspan = 0;
                         $addvisivel = "";
                         if (!$coluna->getVisibilidade()) {
                            $addvisivel = " style='display: none;' ";
                         }
                         if ($coluna->getTipo() == "hora") {
                            $total_valor = $arrtotais[$coluna->getIdColuna()];
                         } else {
                             if ($this->_totalizadorprecisao != 0) {
                                $total_valor = number_format($arrtotais[$coluna->getIdColuna()],$this->_totalizadorprecisao,",",".");
                             } else {
                                $total_valor = $arrtotais[$coluna->getIdColuna()];
                             }
                         }
                         if ($this->_exiberesultados) {
                            echo  "<td id='total_$idcol' align='right'$addvisivel>$addtexto&nbsp;<h3>" . $total_valor . "</h3></td>";
                         }
                         if ($this->_temarquivocsv) {
                            if (in_array($idcol,$this->_colunasarquivo)) {
                                $this->_txtarquivo .= $total_valor . ";";
                            }
                         }
                     } else {
                         $addvisivel = "";
                         if (!$coluna->getVisibilidade()) {
                            $addvisivel = " style='display: none;' ";
                         }
                         if ($this->_exiberesultados) {
                            echo  "<td id='total_$idcol' $addvisivel>&nbsp;</td>";
                         }
                         if ($this->_temarquivocsv) {
                            if (in_array($idcol,$this->_colunasarquivo)) {
                                $this->_txtarquivo .= ";";
                            }
                         }
                     }
                }
                if ($this->_exiberesultados) {
                    echo   "\n\t\t</tr>";
                }
                if ($this->_temarquivocsv) {
                    $this->_txtarquivo .= "\n";
                }
            }
        }

        $this->escreveArquivo($handle);


        if (!$this->issublist) {
            if (is_object($this->subListagem)) {
                if ($this->subListagem->getQuantidadeRegistros()) {
                	$this->subListagem->gerarArquivoXLS();
                    ob_start();
                    $this->subListagem->desenhar();
                    $htmllist = ob_get_contents();
                    ob_end_clean();
                    if ($this->_temarquivocsv) {
                    	$this->_txtarquivo .= $this->subListagem->getTextoArquivoCSV();
                    }
                    if ($this->_exiberesultados) {
                        echo  "<tr><td colspan='$colspan'>";
                        echo  $htmllist;
                        echo  "</td></tr>";
                    }
                    if ($this->subListagem->getTemCheckBox()) {
                        $temcheck = true;
                        $this->temcheck = true;
                    }
                }
            }
        }

        $this->escreveArquivo($handle);


   /*
       EXIBIR OS BOTï¿½ES DE + E - PARA QUANDO POSSUIR COLUNAS COM CHECKBOXES. COMENTADO POR QUE Nï¿½O FUNCIONA SE TIVER MAIS DE UM FORMULï¿½RIO NA MESMA JANELA. */
        if (!$this->issublist) {
            if ($temcheck) {
                if (count($this->colunas)) {
                    echo   "\n\t\t<tr class='tableRodapeModelo3'>";
                    $cntcheckspan = 0;
                    $qtd = $this->getQuantidadeRegistros();
                    foreach ($this->colunas as $coluna) {
                        if ($coluna->getTipo() == "check") {
                            if ($cntcheckspan) { echo  "<td colspan='$cntcheckspan'>&nbsp;</td>";}
                            $cntcheckspan = 0;
                            $idlistagem = $this->getIdListagem();
                            echo  "\n\t\t\t<td align=center nowrap>&nbsp;";
                            echo  "<input type='button' class='botao' name='btselecionar' onclick=\"ListagemSelecionachecks(1,'" . $qtd. "','" . $coluna->getIdColuna() . "_" . $idlistagem . "_');\" value='&nbsp;+&nbsp;'>&nbsp;";
                            echo  "<input type='button' class='botao' name='btdesselecionar' onclick=\"ListagemSelecionachecks(0,'" . $qtd. "','" . $coluna->getIdColuna() . "_" . $idlistagem . "_');\" value='&nbsp;-&nbsp;'>&nbsp;";
                            echo  "\n\t\t\t</td>";
                        } else {
                            $cntcheckspan = $cntcheckspan + 1;
                        }
                    }
                    if ($cntcheckspan) { echo  "<td colspan='$cntcheckspan'>&nbsp;</td>";}
                    echo   "\n\t\t</tr>";
                }
            }
        }



        if ($this->_exibeqtdregistros) {
            if ($this->_exiberesultados) {
                echo   "\n\t\t<tr class='tableRodapeModelo3'>\n\t\t\t<td colspan='$colspan' align=center>";
            }
            $qtd = $this->getQuantidadeRegistros();
            if ($cnt == 0) {
                if ($this->_exiberesultados) {
                    echo   $this->msgnenhumresultado;
                }
            }  else {
                if ($this->_exiberesultados) {
                echo   "<b>$qtd</b> " . $this->msgregistros;
                }
                if ($this->_temarquivocsv) {
            		$this->_txtarquivo .= $qtd . " " . $this->msgregistros;
            	}
            }
            if ($this->_exiberesultados) {
                echo   "</td>\n\t\t</tr>";
            }

        }

        $this->escreveArquivo($handle);


        if ($this->_temarquivocsv) {
        	if (!$this->issublist) {


		        fclose($handle);

		        $link=$tmpfname;

		        if ($cnt != 0) {
			        echo   "\n\t\t<tr class='tableRodapeModelo3'>\n\t\t\t<td colspan='$colspan' align=center>";
			        //echo   '<input type="button" name="btn_arquivoxls" value="Versï¿½o em XLS" class="botao" onclick="window.open(\'gera_csv_to_excel.php?arquivocsv=/' . $link . '\');" style="width: 90px;">';
                    echo   '&nbsp;<input type="button" name="btn_arquivoxls" value="Download CSV" class="botao" onclick="window.open(\'' . $this->_url_base_path . '/downloads.php?arquivo=' . $link . '\');" style="width:120px;">';
			        echo   "</td>\n\t\t</tr>";
		        } else {
		        	if ($this->_exibeqtdregistros) {
			        	echo   "\n\t\t<tr class='tableRodapeModelo3'>\n\t\t\t<td colspan='$colspan' align=center>";
				        echo   $this->msgnenhumresultado;
				        echo   "</td>\n\t\t</tr>";
		        	}
		        }
        	}
	    /*	//FORMULï¿½RIO
	        $this->form->addButton("btn_arquivo","Arquivo .XLS");
			$this->form->addCampoAcao("btn_arquivo","onclick","window.open('gera_csv_to_excel.php?arquivocsv=" . $link . "');");
			$this->form->addQuadro("quadro_arquivo");
			$this->form->addQuadroButton("quadro_arquivo","btn_arquivo");
	     */
        }

        /*
          FORMULï¿½RIO
        if (!$this->issublist) {
            if ($cnt != 0) {
                if (($temcheck) || ($this->_temarquivocsv)) {
                    echo "\n\t\t<tr>\n\t\t\t<td colspan='$colspan' align=center style='padding: 0px;'>";
                    ob_start();
                    $this->form->desenhaCampos();
                    $this->form->displayErros();
                    $htmlform = ob_get_contents();

                    ob_end_clean();
                    echo  $htmlform;
                }
            }
            echo   "</td>\n\t\t</tr>";
            ob_start();
            $this->form->fechaForm();
            $htmlform = ob_get_contents();
            ob_end_clean();
        } */
        //echo  $htmlform;

        $this->arrtotais = $arrtotais;
        $this->arrsubtotais = $arrsubtotais_todos;
        $this->arragrupamentos = $arragrupamentos;


    }

    public function setDebug($value) {
        $this->_debug = $value;
    }

    /**
     * Funï¿½ï¿½o usada internamente para adicionar um erro ao formulï¿½rio.
     *
     * @param string $msgerro Mensagem de Erro
     * @param boolean $fatal Se ï¿½ um erro Fatal = true ()
     * */
    protected function adicionarErro($msgerro,$fatal = false) {
        if ($fatal) { $this->_errors = array(); }
        $erro = array("msgerro" => $msgerro,"fatal" => $fatal);
        array_push($this->_errors,$erro);

        if ($fatal) {
            $this->escreveErros();
            exit;
        }

    }


    /**
     * Funï¿½ï¿½o usada para exibir os erros.
     *
     * */
    protected function escreveErros() {
        if ($this->_debug) {
            if (count($this->_errors)) {
                echo "<style type='text/css'>
                         #erros { background: #BAD0E5; color: #000000; font-size: 12px; Verdana,Arial,Helvetica,sans-serif; font-weight: bold; margin: 15px; padding: 0px; padding-top: 3px; padding-bottom: 3px;  text-align: left; } " .
                        " .erro { background: #E6EAEE; color: #000000; font-size: 12px; Verdana,Arial,Helvetica,sans-serif; font-weight: bold; margin: 2px; padding: 5px; text-align: left; }" .
                        "</style>";
                echo "<div id='erros'>";
                foreach ($this->_errors as $erro) {
                    echo "<div class=erro>";

                    if ($erro["fatal"]) {
                        $addmsg = "<span style='color: #990000;'><b>ERRO:</b></span> ";
                    } else {
                        $addmsg = "ERRO: ";
                    }
                    echo $addmsg . $erro["msgerro"];
                    echo "</div>";
                }
                echo "</div>";
            }
        } else {
            echo "MODO DEBUG ESTï¿½ DESATIVADO.";
        }
    }

    public function gerarArquivoXLS($colunasarquivo = "") {
    	if ($colunasarquivo == "") {
    		$arcolunas = array();
    		foreach ($this->colunas as $coluna) {
    			if ($coluna->getVisibilidade()) {
    			  $colunasarquivo .= $coluna->getIdColuna() . ",";
    			}
    		}
    		$colunasarquivo = substr($colunasarquivo,0,strlen($colunasarquivo)-1);
    	}
    	$arcolunas = explode(",",$colunasarquivo);
    	$txtarquivo = "";
    	$this->_colunasarquivo = $arcolunas;
		$this->_temarquivocsv = true;
    }

    /*
     * Funï¿½ï¿½o utilizada pra somar intervalos de Horas */
    public function somaIntervaloHoras($arrValores){

        // Somando separadamente os valores
        foreach($arrValores as $valor){

            $arrTmp = explode(':',$valor);

            $seg += $arrTmp[2];
            $min += $arrTmp[1];
            $hor += $arrTmp[0];
        }

        // Tratando os segundos
        if( $seg >= 60){

            $min += floor($seg / 60) ;

            while($seg >= 60){

                $seg = $seg - 60;
            }

        }
        // Tratando os minutos
        if( $min >= 60){

            $hor += floor($min / 60) ;

            while($min >= 60){

                $min = $min - 60;
            }

        }

        return str_pad($hor,2,'0',STR_PAD_LEFT).':'.str_pad($min,2,'0',STR_PAD_LEFT).':'.str_pad($seg,2,'0',STR_PAD_LEFT);
    }

    /**
     * Funï¿½ï¿½o que Retorna se a listagem Possui um checkBox adicionado.
     */
    public function getTemCheckBox() {
        return $this->temcheck;
    }

    /**
     * Funï¿½ï¿½o que retorna o array com os Totalizadores.
     */
    public function getTotais() {
    	return $this->arrtotais;
    }

    /**
     * Funï¿½ï¿½o que Retorna o array com os Sub-Totalizadores.
     */
    public function getSubTotais() {
    	return $this->arrsubtotais;
    }

    /**
     * Funï¿½ï¿½o que Retorna o array com os Sub-Totalizadores.
     */
    public function getAgrupamentos() {
        return $this->arragrupamentos;
    }

    /**
     * Funï¿½ï¿½o Interna Utilizada quando a Listagem possui uma sublistagem para guardar quantos registros a listagem principal tem.
     */
    protected function setQuantidadeRegistrosListagemPrincipal($value) {
    	$this->_qtdregistroslistagemprincipal = $value;
    }

    /**
     * Funï¿½ï¿½o Interna Utilizada quando a Listagem possui uma sublistagem para retornar quantos registros a listagem principal tem.
     */
    protected function getQuantidadeRegistrosListagemPrincipal() {
    	return $this->_qtdregistroslistagemprincipal;
    }

    protected function getTextoArquivoCSV() {
    	return $this->_txtarquivo;
    }

    public function autoAdicionarColunas() {
    	$i = pg_num_fields($this->dados);
        for ($j = 0; $j < $i; ++$j) {
            $fieldname = pg_field_name($this->dados, $j);
            $tipo = pg_field_type($this->dados, $j);
            $this->adicionarColuna("coluna_" .$j,"$fieldname","{" . $fieldname . "}","text","left");
        }
    }
}
?>
