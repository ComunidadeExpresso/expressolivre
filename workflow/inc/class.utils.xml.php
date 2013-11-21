<?php
require_once('class.utils.php');

/**
 * Generates XML from vectors and vice-versa
 * @author Carlos Eduardo Nogueira Gonçalves
 * @author Enderlin Ivan <enderlin.ivan@firegates.com> Classe Clean XML To Array
 * @link http://www.phpclasses.org/browse/package/3598.html Classe Clean XML To Array
 * @author Johnny Brochard <johnny.brochard@libertysurf.fr> Classe Array 2 XML
 * @link http://www.phpclasses.org/browse/package/1826.html Classe Array 2 XML
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 */
class XmlUtils extends Utils
{
	/**
	 * Xml parser container
	 * @var resource parser
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $parser;
	/**
	 * Parse result
	 * @var array pOut
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $pOut = array();
	/**
	 * Contains the overlap tag temporarily
	 * @var array track
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $track = array();
	/**
	 * Current tag level
	 * @var string tmpLevel
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $tmpLevel;
	/**
	 * Attribut of current tag
	 * @var array tmpAttrLevel
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $tmpAttrLevel = array();
	/**
	 * Write result
	 * @var string wOut
	 * @access private
	 * @see XmlUtils::fromXML
	 */
	var $wOut = '';

	/**
	 * XML Array
	 * @var array
	 * @access private
	 * @see XmlUtils::toXML
	 */
	var $XMLArray;

	/**
	 * DOM document instance
	 * @var DomDocument
	 * @access private
	 * @see XmlUtils::toXML
	 */
	var $doc;

	/**
	 * Gets vector from XML
	 *
	 * @access  public
	 * @param   string $src Source
	 * @param   string $typeof Source type : NULL, FILE, CURL
	 * @param   string $encoding Encoding type
	 * @return  array
	 */
	function fromXML ( $src, $typeof = 'FILE', $encoding = 'UTF-8' )
	{
		// ini;
		// (re)set array;
		$this->pOut = array();
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $encoding);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, '_startHandler', '_endHandler');
		xml_set_character_data_handler($this->parser, '_contentHandler');
		// format source;
		if($typeof == NULL) {
			$data = $src;
		}
		elseif($typeof == 'FILE') {
			$fop = fopen($src, 'r');
			$data = fread($fop, filesize($src));
			fclose($fop);
		}
		elseif($typeof == 'CURL') {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $src);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$data = curl_exec($curl);
			curl_close($curl);
		}
		else {
			$this->raiseError('O analisador XML precisa de informações', __FILE__, __LINE__);
		}
		// parse $data;
		$parse = xml_parse($this->parser, $data);
		if(!$parse) {
			$this->raiseError
			(
				'XML Error : '                                      .
				xml_error_string(xml_get_error_code($this->parser)) .
				' at line '                                         .
				xml_get_current_line_number($this->parser)          ,
				__FILE__                                            ,
				__LINE__
			);
		}
		// destroy parser;
		xml_parser_free($this->parser);
		// unset extra vars;
		unset($data,
			  $this->track,
			  $this->tmpLevel,
			  $this->tmpAttrLevel);
		// remove global tag and return the result;
		return $this->pOut[0][key($this->pOut[0])];
	}

	/**
	 * Converts vectors to XML
	 *
	 * @param array $input Vector, associative or not, to be parsed into XML
	 * @param string $rootName Root tag's label
	 * @access public
	 * @return string
	 */
	function toXML($input, $rootName="")
	{
		if (is_array($input) && count($input) != 0){
			$this->XMLArray = $input;
		} else {
			$this->raiseError('O argumento a ser convertido deve ser um array com pelo menos 1 elemento', __FILE__, __LINE__);
		}
		global $debug;
		$this->doc = domxml_new_doc("1.0");
		$arr = array();
		if (count($this->XMLArray) > 1) {
			if ($rootName != "") {
				$root = $this->doc->create_element($rootName);
			} else {
				$root = $this->doc->create_element("root");
				$rootName = "root";
			}
			$arr = $this->XMLArray;
		} else {
			$key = key($this->XMLArray);
			$val = $this->XMLArray[$key];
			if (!is_int($key)) {
				$root = $this->doc->create_element($key);
				$rootName = $key;
			} else {
				if ($rootName != "") {
					$root = $this->doc->create_element($rootName);
				} else {
					$root = $this->doc->create_element("root");
					$rootName = "root";
				}
			}
			$arr = $this->XMLArray[$key];
		}
		$root = $this->doc->append_child($root);
		$this->_addArray($arr, $root, $rootName);
		return($this->doc->dump_mem(true));
	}

	/**
	 * Manages the open tag, and these attributs by callback
	 * The purpose is to create a pointer : {{int ptr}}
	 * If the pointer exists, we have a multi-tag situation
	 * Tag name  is stocked like : '<tag>'
	 * Attributs is stocked like : '<tag>-ATTR'
	 * Returns TRUE but built $this->pOut
	 *
	 * @access  private
	 * @param   resource $parser Parser resource
	 * @param   string $tag Tag name
	 * @param   array $attr Attribut
	 * @return  bool
	 * @see XmlUtils::fromXML
	 */
	function _startHandler ( $parser, $tag, $attr )
	{
		static $attrLevel = -1;
		static $fnstAttr = TRUE;
		++$attrLevel;
		// built $this->track;
		$this->track[] = $tag;
		// place pointer to the end;
		end($this->track);
		// temp level;
		$this->tmpLevel = key($this->track);
		// built $this->pOut;
		if(!isset($this->pOut[key($this->track)][$tag])) {
			$this->pOut[key($this->track)][$tag] = '{{'.key($this->track).'}}';
			$attrLevel = 0;
			$this->tmpAttrLevel = array();
		}
		// built attributs;
		if(!empty($attr)) {
			$this->tmpAttrLevel[] = $attrLevel;
			end($this->tmpAttrLevel);
			// it's the first attribut;
			if(!isset($this->pOut[key($this->track)][$tag.'-ATTR'])) {
				$this->pOut[key($this->track)][$tag.'-ATTR'] = $attr;
			} else { // or it's not the first;
				// so it's the second;
				if($fnstAttr === TRUE) {
					$this->pOut[key($this->track)][$tag.'-ATTR'] = array(
						prev($this->tmpAttrLevel) => $this->pOut[key($this->track)][$tag.'-ATTR'],
						next($this->tmpAttrLevel) => $attr
					);
					$fnstAttr = FALSE;
				} else { // or one other;
					$this->pOut[key($this->track)][$tag.'-ATTR'][current($this->tmpAttrLevel)] = $attr;
				}
			}
		}
		return TRUE;
	}

	/**
	 * Detects the pointer, or the multi-tag by callback
	 * If we have a pointer, the method replaces this pointer by the content
	 * Else we have a multi-tag, the method add a element to this array
	 * This method returns TRUE but built $this->pOut
	 *
	 * @access  private
	 * @param   resource $parser Parser resource
	 * @param   string $contentHandler Tag content
	 * @return  bool
	 * @see XmlUtils::fromXML
	 */
	function _contentHandler ( $parser, $contentHandler )
	{
		// remove all spaces;
		if(!preg_match('#^[[:space:]]*$#', $contentHandler)) {
			// $contentHandler is a string;
			if(is_string($this->pOut[key($this->track)][current($this->track)])) {
				// then $contentHandler is a pointer : {{int ptr}}     case 1;
				if(preg_match('#{{([0-9]+)}}#', $this->pOut[key($this->track)][current($this->track)])) {
					$this->pOut[key($this->track)][current($this->track)] = $contentHandler;
				} else { // or then $contentHandler is a multi-tag content      case 2;
					$this->pOut[key($this->track)][current($this->track)] = array(
						0 => $this->pOut[key($this->track)][current($this->track)],
						1 => $contentHandler
					);
				}
			} else { // or $contentHandler is an array;
				// then $contentHandler is the multi-tag array         case 1;
				if(isset($this->pOut[key($this->track)][current($this->track)][0])) {
					$this->pOut[key($this->track)][current($this->track)][] = $contentHandler;
				} else { // or then $contentHandler is a node-tag               case 2;
					$this->pOut[key($this->track)][current($this->track)] = array(
						0 => $this->pOut[key($this->track)][current($this->track)],
						1 => $contentHandler
					);
				}
			}
		}
		return TRUE;
	}

	/**
	 * Detects the last pointer by callback
	 * Move the last tags block up
	 * And reset some temp variables
	 * This method returns TRUE but built $this->pOut
	 * @param   resource $parser Parser resource
	 * @param   string $tag Tag name
	 * @return  bool
	 * @see XmlUtils::fromXML
	 * @access  public
	 */
	function _endHandler ( $parser, $tag )
	{
		// if level--;
		if(key($this->track) == $this->tmpLevel-1) {
			// search up tag;
			// use array_keys if an empty tag exists (taking the last tag);
			// if it's a normal framaset;
			$keyBack = array_keys($this->pOut[key($this->track)], '{{'.key($this->track).'}}');
			$count = count($keyBack);
			if($count != 0) {
				$keyBack = $keyBack{$count-1};
				// move this level up;
				$this->pOut[key($this->track)][$keyBack] = $this->pOut[key($this->track)+1];
			} else { // if we have a multi-tag framaset ($count == 0);
				// if place is set;
				if(isset($this->pOut[key($this->track)][current($this->track)][0])) {
					// if it's a string, we built an array;
					if(is_string($this->pOut[key($this->track)][current($this->track)])) {
						$this->pOut[key($this->track)][current($this->track)] = array(
							0 => $this->pOut[key($this->track)][current($this->track)],
							1 => $this->pOut[key($this->track)+1]
						);
					} else { // else add an index into the array;
						$this->pOut[key($this->track)][current($this->track)][] = $this->pOut[key($this->track)+1];
					}
				} else { // else set the place;
					$this->pOut[key($this->track)][current($this->track)] = array(
						0 => $this->pOut[key($this->track)][current($this->track)],
						1 => $this->pOut[key($this->track)+1]
					);
				}
			}
			// kick $this->pOut level out;
			array_pop($this->pOut);
			end($this->pOut);
		}
		// re-temp level;
		$this->tmpLevel = key($this->track);
		// kick $this->track level out;
		array_pop($this->track);
		end($this->track);
		return TRUE;
	}

	/**
	 * Recursively converts nested arrays to nested XML tags
	 *
	 * @param array $arr
	 * @param object &$n DomNode instance
	 * @param string $name
	 * @see XmlUtils::toXML
	 * @access public
	 * @return void
	 */
	function _addArray($arr, &$n, $name="")
	{
		foreach ($arr as $key => $val) {
			if (is_int($key)) {
				if (strlen($name)>1) {
					$newKey = substr($name, 0, strlen($name)-1);
				} else {
					$newKey="item";
				}
			} else {
				$newKey = $key;
			}
			$node = $this->doc->create_element($newKey);
			if (is_array($val)) {
				$this->_addArray($arr[$key], $node, $key);
			} else {
				$nodeText = $this->doc->create_text_node($val);
				$node->append_child($nodeText);
			}
			$n->append_child($node);
		}
	}
}
?>