<?php


/*
 * Criado em 22/10/2008
 *
 * Autor: Andrй Alexandre Бvila/DIDES-C1/Celepar
 * Projeto NatPHPDC1
 *
 */
/**
 * Tipos Padrгo
 * Classe de tipagem padrгo para comunicaзгo com mainframe
 * Manter essa classe sem alteraзхes
 * Para mudar tipos, criar uma nova classe extendndo essa e passar ao construtor da natphp.
 *
 * Situacoes invalidas:
 * padl e padr no mesmo tipo
 * str sem arr e vice-versa
 * type assoc num array input
 *
 * Quando utilizar o div:
 * Quando o Mainframe retorna um valor 1234567890 mas seria 12345678.90 o valor, divide-se por 100
 * Ao se enviar novamente ao mainframe ele multiplica por 100 e envia
 *
 * Prioridade de tratamento:
 * 1 length
 * 2 div
 * 3 array e str
 * 4 funcarr e funcstr
 * 5 trim global
 */
class NatType {
	private $types = array (
		'A' => array (
			'loop' => true,
			'type' =>'num'
		),
		'AA' => array (
			'loop' => true,
			'type' =>'assoc'
		),
		'N' => array (
			'padl' => '0'
		),
		'S' => array (
			'padr' => ' '
		),
		'DATE' => array (
			'str' => '%04d%02d%02d',
			'arr' => '%04d-%02d-%02d'
		),
		'DATEBR' => array (
			'str' => '%02d%02d%04d',
			'arr' => '%02d/%02d/%04d'
		),
		'DATEDMY' => array (
			'str' => '%04d%02d%02d',
			'arr' => '%3$02d/%2$02d/%1$04d'
		),
		'DTIME' => array (
			'str' => '%04d%02d%02d%02d%02d%02d',
			'arr' => '%04d-%02d-%02d %02d:%02d:%02d'
		),
		'DT' => array (
			'str' => '%04d%02d%02d%02d%02d%02d',
			'arr' => '%04d-%02d-%02d %02d:%02d:%02d'
		),
		'TIME' => array (
			'str' => '%02d%02d%02d',
			'arr' => '%02d:%02d:%02d'
		),
		'DEC' => array (
			'str' => '%0.2f',
			'arr' => '%0.2f',
			'div' => 100,
			'padl' => '0'
		),
		'MONEY' => array (
			'str' => '%f',
			'arr' => '%0.2f',
			'div' => 100,
			'padl' => '0'
		),
		'SSA' => array (
			'padr' => ' ',
			'funcstr' => 'removeAcentos'
		)//string sem acento
	);

	function setType($name, $val) {
		$this->types[$name] = $val;
	}

	function getType($name) {
		if (!isset ($this->types[$name]))
			return $this->types['S'];
		return $this->types[$name];
	}

	function removeAcentos($Msg) {
		$a = array (
			"/[ВАБДГ]/"	=> "A",
			"/[вгабд]/"	=> "a",
			"/[КИЙЛ]/" 	=> "E",
			"/[кийл]/" 	=> "e",
			"/[ОНМП]/" 	=> "I",
			"/[онмп]/" 	=> "i",
			"/[ФХТУЦ]/" => "O",
			"/[фхтуц]/" => "o",
			"/[ЫЩЪЬ]/" 	=> "U",
			"/[ыъщь]/" 	=> "u",
			"/з/" 		=> "c",
			"/З/" 		=> "C"
		);
		return preg_replace(array_keys($a), array_values($a), $Msg);
	}
}
?>