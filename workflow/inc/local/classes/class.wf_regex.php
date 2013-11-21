<?php
/**
 * Classe repositorio de padroes de validacao de expressoes regulares
 *
 * @author Carlos Eduardo Nogueira Goncalves
 * @version 1.2
 * @todo Incluir mais validacoes pre-definidas.
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 */
class wf_regex
{
	/* INiCIO DECLARAcaO DE CONSTANTES */

	/**
	 * @var string $INDEX_BY_STRING Indexacao do array de resultados por string
	 * @access private
	 */
	var $INDEX_BY_STRING = "string";

	/**
	 * @var string $INDEX_BY_INT Indexacao do array de resultados por inteiro
	 * @access private
	 */
	var $INDEX_BY_INT = "int";

	/* FORMATOS ESPECiFICOS */

	/**
	 * @var string $CEP Padrao de validacao de CEPs
	 */
	var $CEP = '^[[:digit:]]{5}-[[:digit:]]{3}$';

	/**
	 * @attention Formato xxx.xxx.xxx-xx
	 * @var string $CPF Padrao de validacao de CPFs (Formato xxx.xxx.xxx-xx)
	 */
	var $CPF = '^([[:digit:]]{3}\.){2}[[:digit:]]{3}-[[:digit:]]{2}$';

	/**
	 * @var string $ADDR_MAC Padrao de validacao de enderecos MAC
	 */
	var $ADDR_MAC = '^(([0-9a-f]{2}):){5}([0-9a-f]{2})$';

	/**
	 * @var string $DATE Padrao de validacao de datas (Formato dd/mm/aaaa)
	 */
	var $DATE = '^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[12][0-9]{3}$';

	/**
	 * @var string $TIME Padrao de validacao de horas (Formato hh:mm)
	 */
	var $TIME = '^([01][[:digit:]]|2[0-3]):[0-5][[:digit:]]$';

	/**
	 * @var string $TIME_GLOBAL Padrão de horas (Formato D:mm, onde D = N dígitos sendo N >=1)
	 */
	var $TIME_GLOBAL = '^([[:digit:]])+:[0-5][[:digit:]]$';

	/**
	 * @var string $TEL_DDI Padrao de validacao de numeros de telefones com DDI (Formato +00 (00) 0000-0000, podendo ter parênteses e/ou hífen, ou não)
	 */
	var $TEL_DDI = '^([+][[:digit:]]{2})?([ (]{1,2}[[:digit:]]{2}[ )]{1,2})?[[:digit:]]{4}[ -]?[[:digit:]]{4}$';

	/**
	 * @var string $TEL_DDD Padrao de validacao de numeros de telefones com DDD (Formato (00) 0000-0000)
	 */
	var $TEL_DDD = '^\([[:digit:]]{2}\)\s[[:digit:]]{4}-[[:digit:]]{4}$';

	/**
	 * @var string $TEL Padrao de validacao de numeros de telefones (Formato 0000-0000)
	 */
	var $TEL = '^([[:digit:]]{4})-([[:digit:]]{4})$';

	/**
	 * @var string $ADDR_IP Padrao de validacao de enderecos IP
	 */
	var $ADDR_IP = '^(([1]?[0-9]{1,2}|2([0-4][0-9]|5[0-5]))\.){3}([1]?[0-9]{1,2}|2([0-4][0-9]|5[0-5]))$';

	/**
	 * @var string $ADDR_EMAIL Padrao de validacao de enderecos de correio eletronico (Formato mail@mail.mail[.mail])
	 */
	var $ADDR_EMAIL = '^([0-9a-zA-Z]+)([._-]?[0-9a-zA-Z]+)*[@]([0-9a-zA-Z]+)([._-]?[0-9a-zA-Z]+)*[.][a-zA-Z][a-zA-Z]+$';
//	var $ADDR_EMAIL = '^[A-Za-z0-9_.-]+@[[:lower:]]+\.[[:lower:]]+(\.[[:lower:]]+){0,2}$';
//	var $ADDR_EMAIL = '^((\w)|([[:punct:]]))+@[[:lower:]]+\.[[:lower:]]+(\.[[:lower:]]+){0,2}$';

	/**
	 * @var string $RG Padrao de validacao de documentos de RG
	 */
	var $RG = '^[[:digit:]]{1,2}(\.[[:digit:]]{3}){2}-(([[:digit:]])|([xX]))$';

	/**
	 * @var string $CURRENCY Padrao de validacao de unidades monetarias
	 */
	var $CURRENCY = '^[R]?[$](([[:digit:]]){1,3}\.)*(([[:digit:]]){1,3}\,)(([[:digit:]]){2})$';

	/* PADROES AUXILIARES DE POSICIONAMENTO */

	/**
	 * @var string $_BEGIN Caracter de inicio de textos alternativo
	 */
	var $_BEGIN = '\A';

	/**
	 * @var string $BEGIN Caracter de inicio de textos
	 */
	var $BEGIN = '^';

	/**
	 * @var string $_END Caracter de fim de textos alternativo
	 */
	var $_END = '\Z';

	/**
	 * @var string $END Caracter de fim de textos
	 */
	var $END = '$';

	/**
	 * @var string $WORD_BEGIN Inicio de palavras. Ex: dia\b = dia, bom-dia.
	 */
	var $WORD_BEGIN = '\h';

	/**
	 * @var string $WORD_END Nao aceita caracteres no inicio de palavras. Ex: dia\b = lombardia, covardia
	 */
	var $WORD_END = '\H';

	/**
	 * @var string $BORDER Extremidade (limite) de palavras. Ex: dia\b = dia, bom-dia. \bdia = dia, diagnostico
	 */
	var $BORDER = '\b';

	/**
	 * @var string $NON_BORDER Dentro das palavras. Ex: min\B = domingo, administrador
	 */
	var $NON_BORDER = '\B';

	/* PADRoES AUXILIARES DE TIPO */

	/**
	 * @var string $DIGIT Digitos decimais
	 */
	var $DIGIT = '\d';

	/**
	 * @var string $NON_DIGIT Nao aceita digitos decimais
	 */
	var $NON_DIGIT = '\D';

	/**
	 * @var string $XDIGIT Digitos hexadecimais
	 */
	var $XDIGIT = '[[:xdigit:]]';

	/**
	 * @var string $ALPHA_NUM Letras e numeros
	 */
	var $ALPHA_NUM = '\w';

	/**
	 * @var string $NON_ALPHA_NUM Nao aceita letras e numeros
	 */
	var $NON_ALPHA_NUM = '\W';

	/**
	 * @var string $PUNCT Sinais de pontuacao
	 */
	var $PUNCT = '[[:punct:]]';

	/* PADRoES AUXILIARES DE CONTROLE DE QUANTIDADE */

	/**
	 * @var string $ZERO_OR_ONE Nenhum ou um caracter
	 */
	var $ZERO_OR_ONE = '?';

	/**
	 * @var string $ZERO_OR_INFINITE Nenhum ou infinitos caracteres
	 */
	var $ZERO_OR_INFINITE = '*';

	/**
	 * @var string $ONE_OR_INFINITE Um ou infinitos caracteres
	 */
	var $ONE_OR_INFINITE = '+';

	/* PADRoES AUXILIARES DE CAIXA DE TEXTO */

	/**
	 * @var string $UPPER Letras maiusculas
	 */
	var $UPPER = '[:upper:]';

	/**
	 * @var string $NON_UPPER Nao aceita letras maiusculas
	 */
	var $NON_UPPER = '\U';

	/**
	 * @var string $LOWER Letras minusculas
	 */
	var $LOWER = '[:lower:]';

	/**
	 * @var string $NON_LOWER Nao aceita letras minusculas
	 */
	var $NON_LOWER = '\L';

	/* META-CARACTERES */

	/**
	 * @var string $BEGIN_ESCAPE Caracter de inicio de trecho com valor literal
	 */
	var $BEGIN_ESCAPE = '\Q';

	/**
	 * @var string $END_ESCAPE Caracter de encerramento de trecho com valor literal
	 */
	var $END_ESCAPE = '\E';

	/**
	 * @var string $BEGIN_GROUP Caracter de inicio de trecho agrupado
	 */
	var $BEGIN_GROUP = '(';

	/**
	 * @var string $END_GROUP Caracter de encerramento de trecho agrupado
	 */
	var $END_GROUP = ')';

	/**
	 * @var string $OR Caracter logico OU (mais util em grupos)
	 */
	var $OR = '|';

	/**
	 * @var string $V_TAB Caracter de tabulacao vertical
	 */
	var $V_TAB = '\v';

	/**
	 * @var string $TAB Caracter de tabulacao horizontal
	 */
	var $TAB = '\t';

	/**
	 * @var string $NEW_LINE Caracter de linha nova
	 */
	var $NEW_LINE = '\n';

	/**
	 * @var string $CARRIAGE_RETURN Caracter de retorno de carro
	 */
	var $CARRIAGE_RETURN = '\r';

	/**
	 * @var string $BLANK Caracteres de espaco e tabulacao
	 */
	var $BLANK = '[:blank:]';

	/* FIM DECLARAcaO DE CONSTANTES */


	/* INiCIO DECLARAcaO DE ATRIBUTOS */

	/**
     * @var array $validResults Array indexado pelos valores de entrada, com os respectivos resultados de validacao
	 */
	var $validResults;

	/**
	 * @var boolean $indexBy Modo de indexacao dos resultados da validacao de multiplos valores
	 */
	var $indexBy = INDEX_BY_STRING;

	/**
	 * @var string $custom Expressao regular personalizada
	 */
	var $custom;

	/* FIM DECLARAcaO DE ATRIBUTOS */


	/* INiCIO DECLARAcaO DE MeTODOS */

	/**
	 * Construtor.
	 *
	 * @param string $indexBy Modo de indexacao dos resultados da validacao de multiplos valores.
	 * @return object
	 */
	function wf_regex( $indexBy = "string" )
	{
		$this->indexBy = $indexBy;
	}

	/**
	 * Metodo para construir expressao regular personalizada.
	 *
	 * @param string $chars Caracteres a serem adicionados.
	 * @return void
	 */
	function append ( $chars )
	{
		$this->custom .= $chars;
	}

	/**
	 * Metodo para adicionar um caracter literal
	 *
	 * @param string $char Caracter.
	 * @return void
	 */
	function appendEscaped ( $char )
	{
		$this->custom .= '\Q' . $char . '\E';
	}

	/**
	 * Metodo para retorna um caracter literal
	 *
	 * @param string $char Caracter
	 * @return string
	 */
	function getEscaped ( $char )
	{
		return('\Q' . $char . '\E');
	}

	/**
	 * Metodo para adicionar um grupo de caracteres
	 *
	 * @param string $chars Caracteres
	 * @return void
	 */
	function appendGroup ( $chars )
	{
		$this->custom .= '(' . $chars . ')';
	}

	/**
	 * Metodo para retornar um grupo de caracteres
	 *
	 * @param string $chars Caracteres
	 * @return string
	 */
	function getGroup ( $chars )
	{
		return('(' . $chars . ')');
	}

	/**
	 * Metodo para adicionar uma lista a expressao regular personalizada
	 *
	 * @param string $chars Caracteres permitidos pela lista
	 * @return void
	 */
	function appendList ( $chars )
	{
		$this->custom .= '[' . $chars . ']';
	}

	/**
	 * Metodo para retornar uma lista
	 *
	 * @param string $chars Caracteres permitidos pela lista
	 * @return string
	 */
	function getList ( $chars )
	{
		return('[' . $chars . ']');
	}

	/**
	 * Metodo para adicionar uma lista negada a expressao regular personalizada
	 *
	 * @param string $chars Caracteres proibidos pela lista negada
	 * @return void
	 */
	function appendDeniedList ( $chars )
	{
		$this->custom .= '[^' . $chars . ']';
	}

	/**
	 * Metodo para retornar uma lista negada
	 *
	 * @param string $chars Caracteres proibidos pela lista negada
	 * @return string
	 */
	function getDeniedList ( $chars )
	{
		return('[^' . $chars . ']');
	}

	/**
	 * Metodo para adicionar uma intervalo de caracteres em uma lista
	 *
	 * @param string $ini Caracter inicial do intervalo
	 * @param string $end Caracter final do intervalo
	 * @return void
	 */
	function appendIntervalList ( $ini, $end )
	{
		$this->custom .= '[' . $ini . '-' . $end . ']';
	}

	/**
	 * Metodo para retornar uma intervalo de caracteres em uma lista
	 *
	 * @param string $ini Caracter inicial do intervalo
	 * @param string $end Caracter final do intervalo
	 * @return string
	 */
	function getIntervalList ( $ini, $end )
	{
		return('[' . $ini . '-' . $end . ']');
	}

	/**
	 * Metodo para adicionar uma intervalo de caracteres em uma lista negada
	 *
	 * @param string $ini Caracter inicial do intervalo
	 * @param string $end Caracter final do intervalo
	 * @return void
	 */
	function appendIntervalDeniedList ( $ini, $end )
	{
		$this->custom .= '[^' . $ini . '-' . $end . ']';
	}

	/**
	 * Metodo para retornar uma intervalo de caracteres em uma lista negada
	 *
	 * @param string $ini Caracter inicial do intervalo
	 * @param string $end Caracter final do intervalo
	 * @return string
	 */
	function getIntervalDeniedList ( $ini, $end )
	{
		return('[^' . $ini . '-' . $end . ']');
	}

	/**
	 * Metodo para adicionar um controle de quantidade de caracteres a expressao regular personalizada
	 *
	 * @param int $count Quantidade permitida
	 * @return void
	 */
	function appendCount ( $count )
	{
		$this->custom .= '{' . $count . '}';
	}

	/**
	 * Metodo para retornar um controle de quantidade de caracteres
	 *
	 * @param int $count Quantidade permitida
	 * @return string
	 */
	function getCount ( $count )
	{
		return('{' . $count . '}');
	}

	/**
	 * Metodo para adicionar um controle de quantidade de caracteres a expressao regular personalizada
	 *
	 * @param int $min Quantidade minima
	 * @param int $max Quantidade maxima (vazio para infinito)
	 * @return void
	 */
	function appendCountInterval ( $min, $max )
	{
		$this->custom .= '{' . $min . ',' . $max . '}';
	}

	/**
	 * Metodo para retornar um controle de quantidade de caracteres
	 *
	 * @param int $min Quantidade minima
	 * @param int $max Quantidade maxima (vazio para infinito)
	 * @return string
	 */
	function getCountInterval ( $min, $max )
	{
		return('{' . $min . ',' . $max . '}');
	}

	/**
	 * Metodo para limpar expressao regular personalizada
	 *
	 * @return void
	 */
	function clear ( )
	{
		$this->custom = "";
	}

	/**
	 * Metodo para alterar a forma de indexacao dos resultados da validacao de multiplos valores
	 *
	 * @param string $mode Modo de indexacao. Deve ser wf_regex->INDEX_BY_STRING ou wf_regex->INDEX_BY_INT
	 * @return void
	 */
	function setIndexMode ( $mode )
	{
		if ( ( $mode == $this->INDEX_BY_STRING ) || ( $mode == $this->INDEX_BY_INT ) )
			$this->indexBy = $mode;
	}

	/**
	 * Valida valor de entrada com base em padroes de expressoes regulares
	 *
	 * @param string $pattern Padrao de expressao regular
	 * @param string $input Valor de entrada a ser validado
	 * @param boolean $caseSensitive Considerar caixa do valor de entrada
	 * @return boolean Resultado da validacao
	 */
	function validate( $pattern, $input, $caseSensitive = TRUE )
	{
		if ( $caseSensitive )
			return( preg_match("/$pattern/", (string) $input ) );
		else
			return( preg_match("/$pattern/i", (string) $input ) );
	}

	/**
	 * Valida valores de entrada em forma de array com base em padroes de expressoes regulares
	 *
	 * @param string $pattern Padrao de expressao regular
	 * @param array $input Valor de entrada a ser validado
	 * @param boolean $caseSensitive Considerar caixa do valor de entrada
	 * @return boolean Resultado da validacao
	 */
	function validateEntries( $pattern, $input, $caseSensitive = TRUE )
	{
		//caso o valor de entrada nao seja um array chama a funcao de validacao de valores individuais
		if (! is_array( $input ) )
			return( $this->validate( $pattern, $input, $caseSensitive ) );

		//faz a validacao de cada elemento do array e armazena o resultado no atributo da classe
		foreach ( $input as $item )
		{
			if ($this->indexBy == $this->INDEX_BY_STRING)
				$this->validResults[ (string) $item ] = $this->validate( $pattern, $item, $caseSensitive );
			else
				$this->validResults[ ] = $this->validate( $pattern, $item, $caseSensitive );
		}
	}

	/* FIM DECLARAcaO DE MeTODOS */
}
?>
