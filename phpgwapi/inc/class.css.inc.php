<?php
  /**************************************************************************\
  * Expresso API - CSS                                                       *
  * ------------------------------------------------------------------------ *
  *  This program is Free Software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

       /**
       * Expresso CSS support class
       *
       * Only instanstiate this class using:
       * <code>
       *  if(!@is_object($GLOBALS['phpgw']->css))
       *  {
       *    $GLOBALS['phpgw']->css = CreateObject('phpgwapi.css');
       *  }
       * </code>
       */
	class css
	{
		private $files = array( );
		
		function css()
		{

		}
		
		function create_pack($cssFile){
			require_once('csstidy/class.csstidy.php');
			$csstidy = new csstidy();
			$csstidy->load_template('highest_compression');
			$cssFilePath=PHPGW_SERVER_ROOT.SEP.$cssFile.'pack.css';
			$fp = fopen($cssFilePath, 'w');
			if ($csstidy->parse(file_get_contents(PHPGW_SERVER_ROOT.SEP.$cssFile)))
				fwrite($fp, $csstidy->print->plain());
		}

		function get_css()
		{
			$path = ( ! empty( $GLOBALS[ 'phpgw_info' ][ 'server' ][ 'webserver_url' ] ) ) ?
				$GLOBALS[ 'phpgw_info' ][ 'server' ][ 'webserver_url' ] : '/';

			if ( strpos( $path, '/' ) != ( strlen( $path ) - 1 ) )
				$path .= '/';

			foreach ( $this -> files as $cssFile )
			{
				if ( $GLOBALS[ 'phpgw_info' ][ 'server' ][ 'csspacker' ] == "True" )
				{
					if ( ! file_exists( PHPGW_SERVER_ROOT . SEP . $cssFile . 'pack.css' ) )
						$this -> create_pack( $cssFile );

					$cssFile .= 'pack.css';
				}

				$out .= '<link type="text/css" rel="StyleSheet" href="' . $path . $cssFile . '" />';
			}

			return $out;
		}

		function validate_file($file, $stack = false )
		{
			if (file_exists(PHPGW_SERVER_ROOT . SEP . $file))
				$this->files[] = $file;
		}
	}
?>
