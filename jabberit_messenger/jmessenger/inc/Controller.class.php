<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

class Controller
{
	const __CONTROLLER_SECURITY__				= 'controller-security';
	const __CONTROLLER_CONTENTES__				= 'controller-contentes';
	const __CONTROLLER_SECTIONS__				= 'controller-sections';

	const __STRING_ACCESS__						= 'string-access';
	const __STRING_DELIMITER__					= 'string-delimiter';

	const __CONTROLLER_CONTENTES_ITEM__			= 'item';
	const __CONTROLLER_CONTENTES_ITEM_PARAM__	= 'param';
	const __CONTROLLER_CONTENTES_ITEM_SECTION__	= 'section';

	const __CONTROLLER_SECTIONS_ITEM__			= 'item';

	private $fatalError							= false;
	private $_PATH								= "";

	public function __construct()
	{
		try
		{
			$controler_xml = dirname(__FILE__) . '/controller.xml';
			
			if ( !file_exists($controler_xml) )
				throw new Exception(__CLASS__ . ' [ ERROR ] :: the configuration file does not exist');

			$this->dom = new DOMDocument;
			$this->dom->preserveWhiteSpace = FALSE;
			$this->dom->load($controler_xml);

			unset($controler_xml);

			$this->controller_security = $this->dom->getElementsByTagName(self::__CONTROLLER_SECURITY__);
			if ( $this->controller_security->length === (int)0 )
				throw new Exception(__CLASS__ . ' [ ERROR #0 ] :: the tag "' . self::__CONTROLLER_SECURITY__ . '" does not exist');
			if ( $this->controller_security->length !== (int)1 )
				throw new Exception(__CLASS__ . ' [ ERROR #1 ] :: exists more of a tag "' . self::__CONTROLLER_SECURITY__ . '"');

			$this->controller_contentes = $this->dom->getElementsByTagName(self::__CONTROLLER_CONTENTES__);
			if ( $this->controller_contentes->length === (int)0 )
				throw new Exception(__CLASS__ . ' [ ERROR #2 ] :: the tag "' . self::__CONTROLLER_CONTENTES__ . '" does not exist');
			if ( $this->controller_contentes->length !== (int)1 )
				throw new Exception(__CLASS__ . ' [ ERROR #3 ] :: exists more of a tag "' . self::__CONTROLLER_CONTENTES__ . '"');
			$this->controller_contentes = $this->controller_contentes->item(0);

			$this->controller_sections = $this->dom->getElementsByTagName("controller-sections");
			if ( $this->controller_sections->length === (int)0 )
				throw new Exception(__CLASS__ . ' [ ERROR #4 ] :: the tag "' . self::__CONTROLLER_SECTIONS__ . '" does not exist');
			if ( $this->controller_sections->length !== (int)1 )
				throw new Exception(__CLASS__ . ' [ ERROR #5 ] :: exists more of a tag "' . self::__CONTROLLER_SECTIONS__ . '"');
			$this->controller_sections = $this->controller_sections->item(0);

			$this->string_access = $this->controller_security->item(0)->getElementsByTagName(self::__STRING_ACCESS__);
			if ( $this->string_access->length === (int)0 )
				throw new Exception(__CLASS__ . ' [ ERROR #6 ] :: the tag "' . self::__STRING_ACCESS__ . '" does not exist');
			if ( $this->string_access->length !== (int)1 )
				throw new Exception(__CLASS__ . ' [ ERROR #7 ] :: exists more of a tag "' . self::__STRING_ACCESS__ . '"');
			$this->string_access = $this->string_access->item(0)->nodeValue;

			$this->string_delimiter = $this->controller_security->item(0)->getElementsByTagName(self::__STRING_DELIMITER__);
			( $this->string_delimiter->length === (int)0 )
				and die(__CLASS__ . ' [ ERROR #8 ] :: the tag "' . self::__STRING_DELIMITER__ . '" does not exist');
			if ( $this->string_delimiter->length !== (int)1 )
				throw new Exception(__CLASS__ . ' [ ERROR #9 ] :: exists more of a tag "' . self::__STRING_DELIMITER__ . '"');
			$this->string_delimiter = $this->string_delimiter->item(0)->nodeValue;
		}
		catch(Exception $e)
		{
			$this->fatalError = true;
			return $e->getMessage();
		}
	}

	public function __call($name, $arguments)
	{
		if ( !$this->fatalError )
			switch ( $name )
			{
				case 'exec' :
					return $this->_exec($arguments[0], $arguments[1]);
				break;
				default : return "Method not avaible";
			}
	}

	public function __toString()
	{
		return __CLASS__;
	}

	private final function _exec(array &$pRequest, $pPath)
	{
		( $pRequest[$this->string_access] )
			or die(__CLASS__ . ' [ ERROR #10 ] :: bad string action argument');

		list($section_name, $ref, $alias) = explode($this->string_delimiter, $pRequest[$this->string_access]);
		unset($pRequest[$this->string_access]);

		$contents_itens = $this->controller_contentes->getElementsByTagName(self::__CONTROLLER_CONTENTES_ITEM__);

		for ( $i = 0; $i < $contents_itens->length && $contents_itens->item($i)->getAttribute(self::__CONTROLLER_CONTENTES_ITEM_PARAM__) != $section_name; ++$i );
		( !($i < $contents_itens->length) )
			and die(__CLASS__ . ' [ ERROR #11 ] :: invalid section "' . $section_name . '"');

		$section_name = $contents_itens->item($i)->getAttribute(self::__CONTROLLER_CONTENTES_ITEM_SECTION__);

		$section = $this->controller_sections->getElementsByTagName($section_name);
		( $section->length === (int)0 )
			and die(__CLASS__ . ' [ ERROR #12 ] :: the tag "' . $section_name . '" does not exist');
		( $section->length === (int)1 )
			or die(__CLASS__ . ' [ ERROR #13 ] :: exists more of a tag "' . $section_name . '"');
		$section = $section->item(0);

		$section_itens = $section->getElementsByTagName(self::__CONTROLLER_SECTIONS_ITEM__);

		if ( empty($alias) && $alias !== '0' )
			for ( $i = 0; $i < $section_itens->length && $section_itens->item($i)->getAttribute('ref') != $ref; ++$i );
		else
			for ( $i = 0; $i < $section_itens->length && ( $section_itens->item($i)->getAttribute('ref') != $ref || $section_itens->item($i)->getAttribute('alias') !== $alias); ++$i );

		( !($i < $section_itens->length) )
			and die(__CLASS__ . ' [ ERROR #14 ] :: invalid reference "' . $ref . '"');

		if( $section_itens->item($i)->getAttribute('path') )
			$path = $section_itens->item($i)->getAttribute('path');
		else if	( $section->getAttribute('path') )
			$path = $pPath . $section->getAttribute('path');
			
		if( !$path ) die(__CLASS__ . ' [ ERROR #15 ] :: bad path argument');

		$prefix = $section_itens->item($i)->getAttribute('prefix')
			or $prefix = $section->getAttribute('prefix');

		$suffix = $section_itens->item($i)->getAttribute('suffix')
			or $suffix = $section->getAttribute('suffix')
			or die(__CLASS__ . ' [ ERROR #16 ] :: bad suffix argument');

		return $this->$section_name(array("pSectionItem" => $section_itens->item($i), "pPath" => $path, "pPrefix" => $prefix, "pSuffix" => $suffix, "pRequest" => $pRequest));
	}

	private final function php()
	{
		$params = func_get_args();
		extract($params[0]);

		$class = $pSectionItem->getAttribute('class')
			and $method = $pSectionItem->getAttribute('method')
			or die(__CLASS__ . ' [ ERROR #17 ] :: bad class or method argument');

		$file = "{$pPath}/{$pPrefix}{$class}{$pSuffix}";

		file_exists($file)
			or die(__CLASS__ . ' [ ERROR #18 ] :: the file that has the class was not opened');

		require_once $file;

		$obj = new ReflectionClass($class);

		if ( $pRequest['classConstructor'] )
		{
			$obj = $obj->newInstance($pRequest['classConstructor']);
			unset($pRequest['classConstructor']);
		}
		else
			$obj = $obj->newInstance();

		$method = new ReflectionMethod($class, $method);
		$result = $method->invoke($obj, $pRequest);

		return $result;
	}

	private final function js()
	{
		$params = func_get_args();
		extract($params[0]);

		$js = $pSectionItem->getAttribute('js')
			or die(__CLASS__ . ' [ ERROR #18 ] :: bad js argument');

		$file = "{$pPath}/{$pPrefix}{$js}{$pSuffix}";

		file_exists($file)
			or die(__CLASS__ . ' [ ERROR #19 ] :: the file that has the class was not opened');

		$debug = $pSectionItem->parentNode->getAttribute('debug');
		
		return file_get_contents($file);
	}
}

?>
