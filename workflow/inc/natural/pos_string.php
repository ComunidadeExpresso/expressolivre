<?php

/*
 * Criado em 21/10/2008
 *
 * Autor: André Alexandre Ávila/DIDES-C1/Celepar
 * Projeto NatPHPDC1
 *
 */
/**
====================== FORMATO ======================
[FIELD] => array
			[type]   = N(numeric), S(String), A(Array)
			[length] = 6(six numbers), 9.2 or 9,2
			if have array
			[length] number of loops
			[fields] = fields of array
					=>
						[FIELD] => array
			 	 			[type]
			 	 			[length]
			 	 			[fields]

================ FORMATO DO RETORNO =================
 array ('name' => 'value')
 array ('name' => array(
			0 => array(
				name1 => value1,
				name2 => value2,
				name3 => value3
		)))
=====================================================
 * Prioridade de tratamento:
 * 1 length
 * 2 div
 * 3 array e str
 * 4 funcarr e funcstr
 * 5 trim global
*/

class PosString {
	public $trim = false;
	private $types = null;
	public $debug = false;
	public $clearStep = true;
	public $dbgln = array ();

	function __construct($type) {
		$this->types = & $type;
	}
	/**
	Funcao sprintf com suporte a array
	*/
	private function sprintf_array($string, $array) {
		$keys = array_keys($array);
		$keysmap = array_flip($keys);
		$values = array_values($array);
		array_unshift($values, $string);
		return call_user_func_array('sprintf', $values);
	}
	/**
	 * Monta String de acordo com o formato
	 * @param Array Formato
	 * @param Array Dados Array
	 * @return String String Posicional
	 */
	public function mountString($format, $dataArr) {
		$dataS = '';
		$iz = 0;
		foreach ($format as $fieldName => $fieldDef) {
			$type = $this->types->getType($fieldDef['type']);
			if (!isset ($type['loop'])) {
				$length = 0;
				$value = '';
				// procura o length
				if (isset ($type['length']))
					$length = $type['length'];
				if (isset ($fieldDef['length']))
					$length = $fieldDef['length'];
				if (isset ($dataArr[$fieldName]))
					$value = $dataArr[$fieldName];
				if (isset ($type['div']) && is_numeric($value))
					$value = (int) $value * $type['div'];
				/* tipagem com mais opções e flexivel */
				/* volta p/ valor original */
				if (isset ($type['arr']) && isset ($type['str']))
					$value = $this->returnFormat($value, $type['arr'], $type['str']);
				if (isset ($type['funcstr']) && method_exists($this->types, $type['funcstr'])) {
					$mtd = $type['funcstr'];
					$value = $this->types-> $mtd ($value);
				}
				/* Usa o PAD para completar o campo caso não seja do tamanho necessário */
				$padN = STR_PAD_RIGHT;
				$padF = ' ';
				if (isset ($type['padl']) || isset ($type['padr'])) {
					if (isset ($type['padl'])) {
						$padN = STR_PAD_LEFT;
						$padF = $type['padl'];
					} else {
						$padN = STR_PAD_RIGHT;
						$padF = $type['padr'];
					}
				}
				$value = str_pad($value, $length, $padF, $padN);
				if ($this->debug) {
					$this->dbgln[] = "Campo:{$fieldName};Tipo:{$fieldDef['type']};Tamanho:{$length};Valor:{$value};";
				}
				$dataS .= $value;
			} else {
				$iz = 0;
				// Loop para pegar os dados do nivel abaixo
				/* No caso da geracao de string percorrer todos os campos eh necessario para
				 * se manter a ordem correta
				 */
				while ($iz < $fieldDef['length']) {
					$value = '';
					if (isset ($dataArr[$fieldName])) {
						if ($type['type'] == 'assoc')
							$value = $dataArr[$fieldName][$iz];
						else
							$value = $dataArr[$fieldName][$iz];
					}
					$dataS .= $this->mountString($fieldDef['fields'], $value);
					++$iz;
				}
			}
		}
		return $dataS;
	}

	/**
	 * Monta Array de acordo com o formato
	 * @param Array Formato
	 * @param String String Posicional
	 * @return Array Dados
	 */
	public function mountResult($format, $string, & $pt = 0) {
		if ($pt == 0 && $this->clearStep)
			$this->dbgln = array ();
		$data = array ();
		$iz = 0;
		foreach ($format as $fieldName => $fieldDef) {
			$type = $this->types->getType($fieldDef['type']);
			if (!isset ($type['loop'])) {
				$length = 0;
				// procura o length
				if (isset ($type['length']))
					$length = $type['length'];
				if (isset ($fieldDef['length']))
					$length = $fieldDef['length'];
				$value = substr($string, $pt, $length);
				if (isset ($type['div']))
					$value = (float) $value / $type['div'];
				if (isset ($type['arr']) && isset ($type['str']))
					$value = $this->returnFormat($value, $type['str'], $type['arr']);
				if (isset ($type['funcarr']) && method_exists($this->types, $type['funcarr'])) {
					$mtd = $type['funcarr'];
					$value = $this->types-> $mtd ($value);
				}
				$pt += $length;
				$data[$fieldName] = ($this->trim) ? (trim($value)) : ($value);
				if ($this->debug) {
					$this->dbgln[] = "Campo:{$fieldName};Tipo:{$fieldDef['type']};Tamanho:{$length};Valor:{$data[$fieldName]};";
				}
			} else {
				$iz = 0;
				$data[$fieldName] = array();
				// caso haja um campo com o limitador de ocorrencias define ele como o length
				if (isset ($fieldDef['index']) && $fieldDef['index'] != null)
					$fieldDef['length'] = $data[$fieldDef['index']];
				// Loop para pegar os dados do nivel abaixo
				//echo 'ddsadsa'.$type['type'];
				while ($iz < $fieldDef['length']) {
					if ($type['type'] == 'assoc'){
						$tmp = $this->mountResult($fieldDef['fields'], $string, $pt);
						$data[$fieldName] = array_merge_recursive($data[$fieldName],$tmp);
					} else {
						$data[$fieldName][$iz] = $this->mountResult($fieldDef['fields'], $string, $pt);
					}
					++$iz;
				}
			}
		}
		return $data;
	}
	/**
	 * Conta o Tamanho total dos campos
	 * @param Array Formato
	 * @return Integer Tamanho total dos campos
	 */
	public function countFormat($format) {
		$pt = 0;
		foreach ($format as $fieldName => $fieldDef) {
			$type = $this->types->getType($fieldDef['type']);
			if (!isset ($type['loop'])) {
				$pt += $fieldDef['length'];
			} else {
				$pt += $fieldDef['length'] * $this->countFormat($fieldDef['fields']);
			}
		}
		return $pt;
	}
	/**
	 * Obtem o resultado da array de log.
	 * @param bool Limpar array de log após obte-la
	 */
	function getDebugLog($clear = false) {
		$log = $this->dbgln;
		if ($clear)
			$this->dbgln = array ();
		return $log;
	}
	/**
	 * Formata valor de acordo com o padrão sprintf
	 * @param String Entrada
	 * @param String Formato entrada
	 * @param String Formato saída
	 */
	private function returnFormat($value, $typeIn, $typeOut) {
		$tmp = sscanf($value, $typeIn);
		if (!is_array($tmp)) {
			return $value;
		}
		$ret = $this->sprintf_array($typeOut, $tmp);
		return $ret;
	}
}
?>
