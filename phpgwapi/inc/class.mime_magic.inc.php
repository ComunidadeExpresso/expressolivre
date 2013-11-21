<?php
  /********************************************************************\
  * eGroupWare - eGroupWare API - mime magic                           *
  * http://www.egroupware.org                                          *
  * This program is part of the GNU project, see http://www.gnu.org    *
  *                                                                    *
  * Originally taken from the Horde Framework http://horde.org         *
  *                                                                    *
  * Copyright 1999-2003 Anil Madhavapeddy <anil@recoil.org>            *
  * Copyright 2002-2003 Michael Slusarz <slusarz@bigworm.colorado.edu> *
  * Copyright 2003 Free Software Foundation, Inc.                      *
  *                                                                    *
  * Ported to phpGroupWare by Dave Hall - dave.hall@mbox.com.au        *
  * Note: this class was relicensed as GPL by Dave Hall - all mods GPL *
  * --------------------------------------------                       *
  *  This program is Free Software; you can redistribute it and/or     *
  *  modify it under the terms of the GNU General Public License as    *
  *  published by the Free Software Foundation; either version 2 of    *
  *  the License, or (at your option) any later version.               *
  \********************************************************************/


	class mime_magic
	{
		//extension to mime type map
		var $mime_extension_map;

		//map of file contents to mime type
		var $mime_magic_file;
		
		/**
		* Wrapper to PHP5 Compatiable Constructor
		*
		* @author skwashd
		
		function mime_magic()
		{
			$this->__constructor()
		}
		*/

		/**
		* Constructor
		*
		* Load the map values into the instance arrays
		* @author skwashd
		*/
		function mime_magic() //__constructor()
		{
			$this->mime_extension_map = $this->get_mime_ext_map();
			$this->mime_magic_file = $this->get_mime_magic_file();
		}

		/**
		* Attempt to convert a file extension to a MIME type
		*
		* This is the simplest MIME type guessing function - rough but fast.
		* If the MIME type is not found then 'application/octet-stream'
		* is returned.
		*
		* @access public
		*
		* @param string $ext  The file extension to be mapped to a MIME type.
		*
		* @return string  The MIME type of the file extension.
		*/
		function ext2mime($ext)
		{
			if (empty($ext))
			{
				return 'text/plain';//assume no extension is a text file
			}
			else
			{
				$ext = strtolower($ext);
				if (!array_key_exists($ext, $this->mime_extension_map))
				{
					return 'application/octet-stream';
				}
				else
				{
					return $this->mime_extension_map[$ext];
				}
			}
		}

		/**
		* Attempt to convert a filename to a MIME type, based on the
		* global and application specific config files.
		*
		* Unlike ext2mime, this function will return
		* 'application/octet-stream' for any unknown or empty extension
		*
		* @access public
		*
		* @param string $filename  The filename to be mapped to a MIME type.
		*
		* @return string  The MIME type of the filename.
		* @author skwashd - changed it to make it work with file.tar.gz etc
		*/
		function filename2mime($filename)
		{
			$fn_parts = explode('.', $filename);
			if (is_array($fn_parts))
			{
				return $this->ext2mime($fn_parts[count($fn_parts)-1]);
			}
			return 'application/octet-stream';
		}

		/* temporary fix for apps using the old name */
		function filename2mine($filename)
		{
			return $this->filename2mime($filename);
		}

		/**
		* Attempt to convert a MIME type to a file extension, based
		* on the global Horde and application specific config files.
		*
		* If we cannot map the type to a file extension, we return false.
		*
		* @access public
		*
		* @param string $type  The MIME type to be mapped to a file extension.
		*
		* @return string  The file extension of the MIME type.
		*/
		function mime2ext($type)
		{
			$key = array_search($type, $this->mime_extension_map);
			if (empty($type) || ($key === false))
			{
				return false;
			}
			else
			{
				return $key;
			}
		}

		/**
		* Uses variants of the UNIX "file" command to attempt to determine the
		* MIME type of an unknown file.
		*
		* @access public
		*
		* @param string $filename The filename (including full path) to the file to analyze.
		*
		* @return string  The MIME type of the file.  Returns false if either
		*                 the file type isn't recognized or the file command is
		*                 not available.
		*/
		function analyze_file($path)
		{
			/* If the PHP Mimetype extension is available, use that. */
			if (function_exists('mime_content_type'))
			{
				return mime_content_type($path);
			}
			else
			{
				/* Use a built-in magic file. */
				if (!($fp = @fopen($path, 'rb')))
				{
					return false;
				}
				foreach ($this->mime_magic_file as $offset => $odata)
				{
					foreach ($odata as $length => $ldata)
					{
						@fseek($fp, $offset, SEEK_SET);
						$lookup = @fread($fp, $length);
						if (!empty($ldata[$lookup]))
						{
							fclose($fp);
							return $ldata[$lookup];
						}
					}
				}
				fclose($fp);
			}
			return false;
		}

		/**
		* Instead of using an existing file a chunk of data is used for
		* testing.  Best to handle the file creation here, to make sure
		* it is secure and it is properly cleaned up.  Really just
		* a temp file creation and clean up method wrapper for analyze_file()
		*
		* @param string $data the data to analyze
		*
		* @param string MIME type false for none.
		*
		* @author skwashd
		*/
		function analyze_data($data)
		{
			if(!is_writeable(@$GLOBALS['phpgw_info']['server']['temp_dir']))
			{
				//nothing we can do but bail out
				return false;
			}

			mt_srand(time());
			$filename = $GLOBALS['phpgw_info']['server']['temp_dir'] . SEP
				. md5( time() + mt_rand() ) . '.tmp';

			$fp = @fopen($filename, 'ab');
			if(!$fp || !$data)
			{
				//houston we have a problem - bail out
				return false;
			}

			if(!fwrite($fp, $data))
			{
				//bail out again
				return false;
			}
			fclose($fp);
			chmod($filename, 0600); //just to be cautious

			$mime = $this->analyze_file($filename);

			unlink($filename);//remove the temp file

			return $mime;
		}

		/**
		* Get an array containing a mapping of common file extensions to
		* MIME types.
		*
		* Original array taken from http://horde.org
		*
		* @author skwashd
		*
		* @return array of extenstion to mime mappings
		*/
		function get_mime_ext_map()
		{
			return array(
				'ai'	=> 'application/postscript',
				'aif'	=> 'audio/x-aiff',
				'aifc'	=> 'audio/x-aiff',
				'aiff'	=> 'audio/x-aiff',
				'asc'	=> 'application/pgp', //changed by skwashd - was text/plain
				'asf'	=> 'video/x-ms-asf',
				'asx'	=> 'video/x-ms-asf',
				'au'	=> 'audio/basic',
				'avi'	=> 'video/x-msvideo',
				'bcpio'	=> 'application/x-bcpio',
				'bin'	=> 'application/octet-stream',
				'bmp'	=> 'image/bmp',
				'c'	=> 'text/plain', // or 'text/x-csrc', //added by skwashd
				'c++'	=> 'text/plain', // or 'text/x-c++src', //added by skwashd
				'cc'	=> 'text/plain', // or 'text/x-c++src', //added by skwashd
				'cs'	=> 'text/plain', //added by skwashd - for C# src
				'cpp'	=> 'text/x-c++src', //added by skwashd
				'cxx'	=> 'text/x-c++src', //added by skwashd
				'cdf'	=> 'application/x-netcdf',
				'class'	=> 'application/octet-stream',//secure but application/java-class is correct
				'com'	=> 'application/octet-stream',//added by skwashd
				'cpio'	=> 'application/x-cpio',
				'cpt'	=> 'application/mac-compactpro',
				'csh'	=> 'application/x-csh',
				'css'	=> 'text/css',
				'csv'	=> 'text/comma-separated-values',//added by skwashd
				'dcr'	=> 'application/x-director',
				'diff'	=> 'text/diff',
				'dir'	=> 'application/x-director',
				'dll'	=> 'application/octet-stream',
				'dms'	=> 'application/octet-stream',
				'doc'	=> 'application/msword',
				'dot'	=> 'application/msword',//added by skwashd
				'dvi'	=> 'application/x-dvi',
				'dxr'	=> 'application/x-director',
				'eps'	=> 'application/postscript',
				'etx'	=> 'text/x-setext',
				'exe'	=> 'application/octet-stream',
				'ez'	=> 'application/andrew-inset',
				'gif'	=> 'image/gif',
				'gtar'	=> 'application/x-gtar',
				'gz'	=> 'application/x-gzip',
				'h'	=> 'text/plain', // or 'text/x-chdr',//added by skwashd
				'h++'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hh'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hpp'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hxx'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hdf'	=> 'application/x-hdf',
				'hqx'	=> 'application/mac-binhex40',
				'htm'	=> 'text/html',
				'html'	=> 'text/html',
				'ice'	=> 'x-conference/x-cooltalk',
				'ics'	=> 'text/calendar',
				'ief'	=> 'image/ief',
				'ifb'	=> 'text/calendar',
				'iges'	=> 'model/iges',
				'igs'	=> 'model/iges',
				'jar'	=> 'application/x-jar', //added by skwashd - alternative mime type
				'java'	=> 'text/x-java-source', //added by skwashd
				'jpe'	=> 'image/jpeg',
				'jpeg'	=> 'image/jpeg',
				'jpg'	=> 'image/jpeg',
				'js'	=> 'application/x-javascript',
				'kar'	=> 'audio/midi',
				'latex'	=> 'application/x-latex',
				'lha'	=> 'application/octet-stream',
				'log'	=> 'text/plain',
				'lzh'	=> 'application/octet-stream',
				'm3u'	=> 'audio/x-mpegurl',
				'man'	=> 'application/x-troff-man',
				'me'	=> 'application/x-troff-me',
				'mesh'	=> 'model/mesh',
				'mid'	=> 'audio/midi',
				'midi'	=> 'audio/midi',
				'mif'	=> 'application/vnd.mif',
				'mov'	=> 'video/quicktime',
				'movie'	=> 'video/x-sgi-movie',
				'mp2'	=> 'audio/mpeg',
				'mp3'	=> 'audio/mpeg',
				'mpe'	=> 'video/mpeg',
				'mpeg'	=> 'video/mpeg',
				'mpg'	=> 'video/mpeg',
				'mpga'	=> 'audio/mpeg',
				'ms'	=> 'application/x-troff-ms',
				'msh'	=> 'model/mesh',
				'mxu'	=> 'video/vnd.mpegurl',
				'nc'	=> 'application/x-netcdf',
				'oda'	=> 'application/oda',
				'patch'	=> 'text/diff',
				'pbm'	=> 'image/x-portable-bitmap',
				'pdb'	=> 'chemical/x-pdb',
				'pdf'	=> 'application/pdf',
				'pgm'	=> 'image/x-portable-graymap',
				'pgn'	=> 'application/x-chess-pgn',
				'pgp'	=> 'application/pgp',//added by skwashd
				'php'	=> 'application/x-httpd-php',
				'php3'	=> 'application/x-httpd-php3',
				'pl'	=> 'application/x-perl',
				'pm'	=> 'application/x-perl',
				'png'	=> 'image/png',
				'pnm'	=> 'image/x-portable-anymap',
				'po'	=> 'text/plain',
				'ppm'	=> 'image/x-portable-pixmap',
				'ppt'	=> 'application/vnd.ms-powerpoint',
				'ps'	=> 'application/postscript',
				'qt'	=> 'video/quicktime',
				'ra'	=> 'audio/x-realaudio',
				'ram'	=> 'audio/x-pn-realaudio',
				'ras'	=> 'image/x-cmu-raster',
				'rgb'	=> 'image/x-rgb',
				'rm'	=> 'audio/x-pn-realaudio',
				'roff'	=> 'application/x-troff',
				'rpm'	=> 'audio/x-pn-realaudio-plugin',
				'rtf'	=> 'text/rtf',
				'rtx'	=> 'text/richtext',
				'sgm'	=> 'text/sgml',
				'sgml'	=> 'text/sgml',
				'sh'	=> 'application/x-sh',
				'shar'	=> 'application/x-shar',
				'shtml'	=> 'text/html',
				'silo'	=> 'model/mesh',
				'sit'	=> 'application/x-stuffit',
				'skd'	=> 'application/x-koan',
				'skm'	=> 'application/x-koan',
				'skp'	=> 'application/x-koan',
				'skt'	=> 'application/x-koan',
				'smi'	=> 'application/smil',
				'smil'	=> 'application/smil',
				'snd'	=> 'audio/basic',
				'so'	=> 'application/octet-stream',
				'spl'	=> 'application/x-futuresplash',
				'src'	=> 'application/x-wais-source',
				'stc'	=> 'application/vnd.sun.xml.calc.template',
				'std'	=> 'application/vnd.sun.xml.draw.template',
				'sti'	=> 'application/vnd.sun.xml.impress.template',
				'stw'	=> 'application/vnd.sun.xml.writer.template',
				'sv4cpio'	=> 'application/x-sv4cpio',
				'sv4crc'	=> 'application/x-sv4crc',
				'swf'	=> 'application/x-shockwave-flash',
				'sxc'	=> 'application/vnd.sun.xml.calc',
				'sxd'	=> 'application/vnd.sun.xml.draw',
				'sxg'	=> 'application/vnd.sun.xml.writer.global',
				'sxi'	=> 'application/vnd.sun.xml.impress',
				'sxm'	=> 'application/vnd.sun.xml.math',
				'sxw'	=> 'application/vnd.sun.xml.writer',
				't'	=> 'application/x-troff',
				'tar'	=> 'application/x-tar',
				'tcl'	=> 'application/x-tcl',
				'tex'	=> 'application/x-tex',
				'texi'	=> 'application/x-texinfo',
				'texinfo'	=> 'application/x-texinfo',
				'tgz'	=> 'application/x-gtar',
				'tif'	=> 'image/tiff',
				'tiff'	=> 'image/tiff',
				'tr'	=> 'application/x-troff',
				'tsv'	=> 'text/tab-separated-values',
				'txt'	=> 'text/plain',
				'ustar'	=> 'application/x-ustar',
				'vbs'	=> 'text/plain', //added by skwashd - for obvious reasons
				'vcd'	=> 'application/x-cdlink',
				'vcf'	=> 'text/x-vcard',
				'vcs'	=> 'text/calendar',
				'vfb'	=> 'text/calendar',
				'vrml'	=> 'model/vrml',
				'vsd'	=> 'application/vnd.visio',
				'wav'	=> 'audio/x-wav',
				'wax'	=> 'audio/x-ms-wax',
				'wbmp'	=> 'image/vnd.wap.wbmp',
				'wbxml'	=> 'application/vnd.wap.wbxml',
				'wm'	=> 'video/x-ms-wm',
				'wma'	=> 'audio/x-ms-wma',
				'wmd'	=> 'application/x-ms-wmd',
				'wml'	=> 'text/vnd.wap.wml',
				'wmlc'	=> 'application/vnd.wap.wmlc',
				'wmls'	=> 'text/vnd.wap.wmlscript',
				'wmlsc'	=> 'application/vnd.wap.wmlscriptc',
				'wmv'	=> 'video/x-ms-wmv',
				'wmx'	=> 'video/x-ms-wmx',
				'wmz'	=> 'application/x-ms-wmz',
				'wrl'	=> 'model/vrml',
				'wvx'	=> 'video/x-ms-wvx',
				'xbm'	=> 'image/x-xbitmap',
				'xht'	=> 'application/xhtml+xml',
				'xhtml'	=> 'application/xhtml+xml',
				'xls'	=> 'application/vnd.ms-excel',
				'xlt'	=> 'application/vnd.ms-excel',
				'xml'	=> 'application/xml',
				'xpm'	=> 'image/x-xpixmap',
				'xsl'	=> 'text/xml',
				'xwd'	=> 'image/x-xwindowdump',
				'xyz'	=> 'chemical/x-xyz',
				'z'	=> 'application/x-compress',
				'zip'	=> 'application/zip',
			);
		}

		/**
		* Get the mime magic mapping file - last resort test
		*
		* Note Taken from horde.org - no copyright notice attached
		*
		* @author skwashd - converted to a function
		*
		* @return array mime magic data
		*/
		function get_mime_magic_file()
		{
			$mime_magic[0][30]["\145\166\141\154\40\42\145\170\145\143\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
	$mime_magic[0][24]["\145\166\141\154\40\42\145\170\145\143\40\57\165\163\162\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][23]["\103\157\155\155\157\156\40\163\165\142\144\151\162\145\143\164\157\162\151\145\163\72\40"] = 'text/x-patch';
			$mime_magic[0][23]["\75\74\154\151\163\164\76\156\74\160\162\157\164\157\143\157\154\40\142\142\156\55\155"] = 'application/data';
			$mime_magic[0][22]["\101\115\101\116\104\101\72\40\124\101\120\105\123\124\101\122\124\40\104\101\124\105"] = 'application/x-amanda-header';
			$mime_magic[0][22]["\107\106\61\120\101\124\103\110\61\60\60\60\111\104\43\60\60\60\60\60\62\60"] = 'audio/x-gus-patch';
			$mime_magic[0][22]["\107\106\61\120\101\124\103\110\61\61\60\60\111\104\43\60\60\60\60\60\62\60"] = 'audio/x-gus-patch';
			$mime_magic[0][22]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][22]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][22]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][22]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][22]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][22]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][22]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][22]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][22]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][22]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][21]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\163\150"] = 'application/x-zsh';
			$mime_magic[0][21]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\172\163\150"] = 'application/x-zsh';
			$mime_magic[0][21]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\163\150"] = 'application/x-zsh';
			$mime_magic[0][21]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\172\163\150"] = 'application/x-zsh';
			$mime_magic[0][21]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][21]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][21]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][21]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][21]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][20]["\145\166\141\154\40\42\145\170\145\143\40\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][20]["\43\41\11\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\145"] = 'text/script';
			$mime_magic[0][20]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\145"] = 'text/script';
			$mime_magic[0][20]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\163\150"] = 'application/x-sh';
			$mime_magic[0][20]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\172\163\150"] = 'application/x-zsh';
			$mime_magic[0][19]["\103\162\145\141\164\151\166\145\40\126\157\151\143\145\40\106\151\154\145"] = 'audio/x-voc';
			$mime_magic[0][19]["\41\74\141\162\143\150\76\156\137\137\137\137\137\137\137\137\137\137\105"] = 'application/x-ar';
			$mime_magic[0][19]["\41\74\141\162\143\150\76\156\137\137\137\137\137\137\137\137\66\64\105"] = 'application/data';
			$mime_magic[0][19]["\43\41\57\165\163\162\57\154\157\143\141\154\57\142\151\156\57\141\145"] = 'text/script';
			$mime_magic[0][18]["\106\151\114\145\123\164\101\162\124\146\111\154\105\163\124\141\122\164"] = 'text/x-apple-binscii';
			$mime_magic[0][18]["\43\41\40\57\165\163\162\57\154\157\143\141\154\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][18]["\45\41\120\123\55\101\144\157\142\145\106\157\156\164\55\61\56\60"] = 'font/type1';
			$mime_magic[0][17]["\43\41\57\165\163\162\57\154\157\143\141\154\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][16]["\105\170\164\145\156\144\145\144\40\115\157\144\165\154\145\72"] = 'audio/x-ft2-mod';
			$mime_magic[0][16]["\123\164\141\162\164\106\157\156\164\115\145\164\162\151\143\163"] = 'font/x-sunos-news';
			$mime_magic[0][16]["\43\41\11\57\165\163\162\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][16]["\43\41\11\57\165\163\162\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][16]["\43\41\11\57\165\163\162\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][16]["\43\41\40\57\165\163\162\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][16]["\43\41\40\57\165\163\162\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][16]["\43\41\40\57\165\163\162\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][16]["\74\115\141\153\145\162\104\151\143\164\151\157\156\141\162\171"] = 'application/x-framemaker';
			$mime_magic[0][16]["\74\115\141\153\145\162\123\143\162\145\145\156\106\157\156\164"] = 'font/x-framemaker';
			$mime_magic[0][15]["\43\41\11\57\165\163\162\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][15]["\43\41\40\57\165\163\162\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][15]["\43\41\57\165\163\162\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][15]["\43\41\57\165\163\162\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][15]["\43\41\57\165\163\162\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][14]["\41\74\141\162\143\150\76\156\144\145\142\151\141\156"] = 'application/x-dpkg';
			$mime_magic[0][14]["\43\41\57\165\163\162\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][14]["\74\41\104\117\103\124\131\120\105\40\110\124\115\114"] = 'text/html';
			$mime_magic[0][14]["\74\41\144\157\143\164\171\160\145\40\150\164\155\154"] = 'text/html';
			$mime_magic[0][13]["\107\111\115\120\40\107\162\141\144\151\145\156\164"] = 'application/x-gimp-gradient';
			$mime_magic[0][12]["\122\145\164\165\162\156\55\120\141\164\150\72"] = 'message/rfc822';
			$mime_magic[0][12]["\43\41\11\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][12]["\43\41\11\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][12]["\43\41\11\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][12]["\43\41\11\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][12]["\43\41\11\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][12]["\43\41\40\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][12]["\43\41\40\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][12]["\43\41\40\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][12]["\43\41\40\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][12]["\43\41\40\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][11]["\43\41\11\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][11]["\43\41\11\57\142\151\156\57\143\163\150"] = 'application/x-csh';
			$mime_magic[0][11]["\43\41\11\57\142\151\156\57\153\163\150"] = 'application/x-ksh';
			$mime_magic[0][11]["\43\41\40\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][11]["\43\41\40\57\142\151\156\57\143\163\150"] = 'application/x-csh';
			$mime_magic[0][11]["\43\41\40\57\142\151\156\57\153\163\150"] = 'application/x-ksh';
			$mime_magic[0][11]["\43\41\57\142\151\156\57\142\141\163\150"] = 'application/x-sh';
			$mime_magic[0][11]["\43\41\57\142\151\156\57\147\141\167\153"] = 'application/x-awk';
			$mime_magic[0][11]["\43\41\57\142\151\156\57\156\141\167\153"] = 'application/x-awk';
			$mime_magic[0][11]["\43\41\57\142\151\156\57\160\145\162\154"] = 'application/x-perl';
			$mime_magic[0][11]["\43\41\57\142\151\156\57\164\143\163\150"] = 'application/x-csh';
			$mime_magic[0][10]["\102\151\164\155\141\160\146\151\154\145"] = 'image/unknown';
			$mime_magic[0][10]["\123\124\101\122\124\106\117\116\124\40"] = 'font/x-bdf';
			$mime_magic[0][10]["\43\41\11\57\142\151\156\57\162\143"] = 'text/script';
			$mime_magic[0][10]["\43\41\11\57\142\151\156\57\163\150"] = 'application/x-sh';
			$mime_magic[0][10]["\43\41\40\57\142\151\156\57\162\143"] = 'text/script';
			$mime_magic[0][10]["\43\41\40\57\142\151\156\57\163\150"] = 'application/x-sh';
			$mime_magic[0][10]["\43\41\57\142\151\156\57\141\167\153"] = 'application/x-awk';
			$mime_magic[0][10]["\43\41\57\142\151\156\57\143\163\150"] = 'application/x-csh';
			$mime_magic[0][10]["\43\41\57\142\151\156\57\153\163\150"] = 'application/x-ksh';
			$mime_magic[0][10]["\74\115\141\153\145\162\106\151\154\145"] = 'application/x-framemaker';
			$mime_magic[0][9]["\122\145\143\145\151\166\145\144\72"] = 'message/rfc822';
			$mime_magic[0][9]["\123\164\141\162\164\106\157\156\164"] = 'font/x-sunos-news';
			$mime_magic[0][9]["\211\114\132\117\0\15\12\32\12"] = 'application/data';
			$mime_magic[0][9]["\43\41\57\142\151\156\57\162\143"] = 'text/script';
			$mime_magic[0][9]["\43\41\57\142\151\156\57\163\150"] = 'application/x-sh';
			$mime_magic[0][9]["\55\162\157\155\61\146\163\55\60"] = 'application/x-filesystem';
			$mime_magic[0][9]["\74\102\157\157\153\106\151\154\145"] = 'application/x-framemaker';
			$mime_magic[0][8]["\117\156\154\171\40\151\156\40"] = 'text/x-patch';
			$mime_magic[0][8]["\147\151\155\160\40\170\143\146"] = 'application/x-gimp-image';
			$mime_magic[0][8]["\155\163\147\143\141\164\60\61"] = 'application/x-locale';
			$mime_magic[0][8]["\32\141\162\143\150\151\166\145"] = 'application/data';
			$mime_magic[0][8]["\41\74\120\104\106\76\41\156"] = 'application/x-prof';
			$mime_magic[0][8]["\74\115\111\106\106\151\154\145"] = 'application/x-framemaker';
			$mime_magic[0][7]["\101\162\164\151\143\154\145"] = 'message/news';
			$mime_magic[0][7]["\120\103\104\137\117\120\101"] = 'x/x-photo-cd-overfiew-file';
			$mime_magic[0][7]["\351\54\1\112\101\115\11"] = 'application/data';
			$mime_magic[0][7]["\41\74\141\162\143\150\76"] = 'application/x-ar';
			$mime_magic[0][7]["\72\40\163\150\145\154\154"] = 'application/data';
			$mime_magic[0][6]["\116\165\106\151\154\145"] = 'application/data';
			$mime_magic[0][6]["\116\365\106\351\154\345"] = 'application/data';
			$mime_magic[0][6]["\60\67\60\67\60\61"] = 'application/x-cpio';
			$mime_magic[0][6]["\60\67\60\67\60\62"] = 'application/x-cpio';
			$mime_magic[0][6]["\60\67\60\67\60\67"] = 'application/x-cpio';
			$mime_magic[0][6]["\74\115\141\153\145\162"] = 'application/x-framemaker';
			$mime_magic[0][6]["\74\124\111\124\114\105"] = 'text/html';
			$mime_magic[0][6]["\74\164\151\164\154\145"] = 'text/html';
			$mime_magic[0][5]["\0\1\0\0\0"] = 'font/ttf';
			$mime_magic[0][5]["\0\4\36\212\200"] = 'application/core';
			$mime_magic[0][5]["\102\101\102\131\114"] = 'message/x-gnu-rmail';
			$mime_magic[0][5]["\102\105\107\111\116"] = 'application/x-awk';
			$mime_magic[0][5]["\103\157\162\145\1"] = 'application/x-executable-file';
			$mime_magic[0][5]["\104\61\56\60\15"] = 'font/x-speedo';
			$mime_magic[0][5]["\106\162\157\155\72"] = 'message/rfc822';
			$mime_magic[0][5]["\115\101\123\137\125"] = 'audio/x-multimate-mod';
			$mime_magic[0][5]["\120\117\136\121\140"] = 'text/vnd.ms-word';
			$mime_magic[0][5]["\120\141\164\150\72"] = 'message/news';
			$mime_magic[0][5]["\130\162\145\146\72"] = 'message/news';
			$mime_magic[0][5]["\144\151\146\146\40"] = 'text/x-patch';
			$mime_magic[0][5]["\225\64\62\62\336"] = 'application/x-locale';
			$mime_magic[0][5]["\336\62\62\64\225"] = 'application/x-locale';
			$mime_magic[0][5]["\74\110\105\101\104"] = 'text/html';
			$mime_magic[0][5]["\74\110\124\115\114"] = 'text/html';
			$mime_magic[0][5]["\74\150\145\141\144"] = 'text/html';
			$mime_magic[0][5]["\74\150\164\155\154"] = 'text/html';
			$mime_magic[0][5]["\75\74\141\162\76"] = 'application/x-ar';
			$mime_magic[0][4]["\0\0\0\314"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\0\0\4"] = 'font/x-snf';
			$mime_magic[0][4]["\0\0\1\107"] = 'application/x-object-file';
			$mime_magic[0][4]["\0\0\1\113"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\0\1\115"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\0\1\117"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\0\1\201"] = 'application/x-object-file';
			$mime_magic[0][4]["\0\0\1\207"] = 'application/data';
			$mime_magic[0][4]["\0\0\1\263"] = 'video/mpeg';
			$mime_magic[0][4]["\0\0\1\272"] = 'video/mpeg';
			$mime_magic[0][4]["\0\0\1\6"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\0\201\154"] = 'application/x-apl-workspace';
			$mime_magic[0][4]["\0\0\377\145"] = 'application/x-library-file';
			$mime_magic[0][4]["\0\0\377\155"] = 'application/data';
			$mime_magic[0][4]["\0\0\3\347"] = 'application/x-library-file';
			$mime_magic[0][4]["\0\0\3\363"] = 'application/x-executable-file';
			$mime_magic[0][4]["\0\144\163\56"] = 'audio/basic';
			$mime_magic[0][4]["\0\1\22\127"] = 'application/core';
			$mime_magic[0][4]["\0\22\326\207"] = 'image/x11';
			$mime_magic[0][4]["\0\3\233\355"] = 'application/data';
			$mime_magic[0][4]["\0\3\233\356"] = 'application/data';
			$mime_magic[0][4]["\0\5\26\0"] = 'application/data';
			$mime_magic[0][4]["\0\5\26\7"] = 'application/data';
			$mime_magic[0][4]["\0\5\61\142"] = 'application/x-db';
			$mime_magic[0][4]["\0\6\25\141"] = 'application/x-db';
			$mime_magic[0][4]["\103\124\115\106"] = 'audio/x-cmf';
			$mime_magic[0][4]["\105\115\117\104"] = 'audio/x-emod';
			$mime_magic[0][4]["\106\106\111\114"] = 'font/ttf';
			$mime_magic[0][4]["\106\117\116\124"] = 'font/x-vfont';
			$mime_magic[0][4]["\107\104\102\115"] = 'application/x-gdbm';
			$mime_magic[0][4]["\107\111\106\70"] = 'image/gif';
			$mime_magic[0][4]["\10\16\12\17"] = 'application/data';
			$mime_magic[0][4]["\110\120\101\113"] = 'application/data';
			$mime_magic[0][4]["\111\111\116\61"] = 'image/tiff';
			$mime_magic[0][4]["\111\111\52\0"] = 'image/tiff';
			$mime_magic[0][4]["\114\104\110\151"] = 'application/data';
			$mime_magic[0][4]["\114\127\106\116"] = 'font/type1';
			$mime_magic[0][4]["\115\115\0\52"] = 'image/tiff';
			$mime_magic[0][4]["\115\117\126\111"] = 'video/x-sgi-movie';
			$mime_magic[0][4]["\115\124\150\144"] = 'audio/midi';
			$mime_magic[0][4]["\115\247\356\350"] = 'font/x-hp-windows';
			$mime_magic[0][4]["\116\124\122\113"] = 'audio/x-multitrack';
			$mime_magic[0][4]["\120\113\3\4"] = 'application/zip';
			$mime_magic[0][4]["\122\111\106\106"] = 'audio/x-wav';
			$mime_magic[0][4]["\122\141\162\41"] = 'application/x-rar';
			$mime_magic[0][4]["\123\121\123\110"] = 'application/data';
			$mime_magic[0][4]["\124\101\104\123"] = 'application/x-tads-game';
			$mime_magic[0][4]["\125\103\62\32"] = 'application/data';
			$mime_magic[0][4]["\125\116\60\65"] = 'audio/x-mikmod-uni';
			$mime_magic[0][4]["\12\17\10\16"] = 'application/data';
			$mime_magic[0][4]["\131\246\152\225"] = 'x/x-image-sun-raster';
			$mime_magic[0][4]["\145\377\0\0"] = 'application/x-ar';
			$mime_magic[0][4]["\150\163\151\61"] = 'image/x-jpeg-proprietary';
			$mime_magic[0][4]["\16\10\17\12"] = 'application/data';
			$mime_magic[0][4]["\177\105\114\106"] = 'application/x-executable-file';
			$mime_magic[0][4]["\17\12\16\10"] = 'application/data';
			$mime_magic[0][4]["\1\130\41\246"] = 'application/core';
			$mime_magic[0][4]["\1\146\143\160"] = 'font/x-pcf';
			$mime_magic[0][4]["\211\120\116\107"] = 'image/x-png';
			$mime_magic[0][4]["\23\127\232\316"] = 'application/x-gdbm';
			$mime_magic[0][4]["\23\172\51\104"] = 'font/x-sunos-news';
			$mime_magic[0][4]["\23\172\51\107"] = 'font/x-sunos-news';
			$mime_magic[0][4]["\23\172\51\120"] = 'font/x-sunos-news';
			$mime_magic[0][4]["\23\172\51\121"] = 'font/x-sunos-news';
			$mime_magic[0][4]["\24\2\131\31"] = 'font/x-libgrx';
			$mime_magic[0][4]["\260\61\63\140"] = 'application/x-bootable';
			$mime_magic[0][4]["\2\10\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\10\1\6"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\10\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\10\377\145"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\12\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\12\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\12\377\145"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\13\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\13\1\13"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\13\1\15"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\13\1\16"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\13\1\6"] = 'application/x-object-file';
			$mime_magic[0][4]["\2\13\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\14\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\14\1\13"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\14\1\14"] = 'application/x-lisp';
			$mime_magic[0][4]["\2\14\1\15"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\14\1\16"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\14\1\6"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\14\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\14\377\145"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\20\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\20\1\13"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\20\1\15"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\20\1\16"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\20\1\6"] = 'application/x-object-file';
			$mime_magic[0][4]["\2\20\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\24\1\10"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\24\1\13"] = 'application/x-executable-file';
			$mime_magic[0][4]["\2\24\1\15"] = 'application/x-object-file';
			$mime_magic[0][4]["\2\24\1\16"] = 'application/x-library-file';
			$mime_magic[0][4]["\2\24\1\6"] = 'application/x-object-file';
			$mime_magic[0][4]["\2\24\1\7"] = 'application/x-executable-file';
			$mime_magic[0][4]["\361\60\100\273"] = 'image/x-cmu-raster';
			$mime_magic[0][4]["\366\366\366\366"] = 'application/x-pc-floppy';
			$mime_magic[0][4]["\377\106\117\116"] = 'font/x-dos';
			$mime_magic[0][4]["\41\74\141\162"] = 'application/x-ar';
			$mime_magic[0][4]["\43\41\11\57"] = 'text/script';
			$mime_magic[0][4]["\43\41\40\57"] = 'text/script';
			$mime_magic[0][4]["\52\123\124\101"] = 'application/data';
			$mime_magic[0][4]["\52\52\52\40"] = 'text/x-patch';
			$mime_magic[0][4]["\56\162\141\375"] = 'audio/x-pn-realaudio';
			$mime_magic[0][4]["\56\163\156\144"] = 'audio/basic';
			$mime_magic[0][4]["\61\143\167\40"] = 'application/data';
			$mime_magic[0][4]["\61\276\0\0"] = 'text/vnd.ms-word';
			$mime_magic[0][4]["\62\62\67\70"] = 'application/data';
			$mime_magic[0][4]["\74\115\115\114"] = 'application/x-framemaker';
			$mime_magic[0][4]["\74\141\162\76"] = 'application/x-ar';
			$mime_magic[0][3]["\102\132\150"] = 'application/x-bzip2';
			$mime_magic[0][3]["\106\101\122"] = 'audio/mod';
			$mime_magic[0][3]["\115\124\115"] = 'audio/x-multitrack';
			$mime_magic[0][3]["\123\102\111"] = 'audio/x-sbi';
			$mime_magic[0][3]["\124\117\103"] = 'audio/x-toc';
			$mime_magic[0][3]["\12\107\114"] = 'application/data';
			$mime_magic[0][3]["\146\154\143"] = 'application/x-font';
			$mime_magic[0][3]["\146\154\146"] = 'font/x-figlet';
			$mime_magic[0][3]["\33\105\33"] = 'image/x-pcl-hp';
			$mime_magic[0][3]["\33\143\33"] = 'application/data';
			$mime_magic[0][3]["\377\377\174"] = 'application/data';
			$mime_magic[0][3]["\377\377\176"] = 'application/data';
			$mime_magic[0][3]["\377\377\177"] = 'application/data';
			$mime_magic[0][3]["\43\41\40"] = 'text/script';
			$mime_magic[0][3]["\43\41\57"] = 'text/script';
			$mime_magic[0][3]["\4\45\41"] = 'application/postscript';
			$mime_magic[0][3]["\55\150\55"] = 'application/data';
			$mime_magic[0][3]["\61\143\167"] = 'application/data';
			$mime_magic[0][2]["\0\0"] = 'application/x-executable-file';
			$mime_magic[0][2]["\102\115"] = 'image/x-bmp';
			$mime_magic[0][2]["\102\132"] = 'application/x-bzip';
			$mime_magic[0][2]["\111\103"] = 'image/x-ico';
			$mime_magic[0][2]["\112\116"] = 'audio/x-669-mod';
			$mime_magic[0][2]["\115\132"] = 'application/x-ms-dos-executable';
			$mime_magic[0][2]["\120\61"] = 'image/x-portable-bitmap';
			$mime_magic[0][2]["\120\62"] = 'image/x-portable-graymap';
			$mime_magic[0][2]["\120\63"] = 'image/x-portable-pixmap';
			$mime_magic[0][2]["\120\64"] = 'image/x-portable-bitmap';
			$mime_magic[0][2]["\120\65"] = 'image/x-portable-graymap';
			$mime_magic[0][2]["\120\66"] = 'image/x-portable-pixmap';
			$mime_magic[0][2]["\151\146"] = 'audio/x-669-mod';
			$mime_magic[0][2]["\161\307"] = 'application/x-cpio';
			$mime_magic[0][2]["\166\377"] = 'application/data';
			$mime_magic[0][2]["\1\110"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\111"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\124"] = 'application/data';
			$mime_magic[0][2]["\1\125"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\160"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\161"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\175"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\177"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\20"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\203"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\21"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\210"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\217"] = 'application/x-object-file';
			$mime_magic[0][2]["\1\224"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\227"] = 'application/x-executable-file';
			$mime_magic[0][2]["\1\332"] = 'x/x-image-sgi';
			$mime_magic[0][2]["\1\36"] = 'font/x-vfont';
			$mime_magic[0][2]["\1\6"] = 'application/x-executable-file';
			$mime_magic[0][2]["\307\161"] = 'application/x-bcpio';
			$mime_magic[0][2]["\313\5"] = 'application/data';
			$mime_magic[0][2]["\352\140"] = 'application/x-arj';
			$mime_magic[0][2]["\367\131"] = 'font/x-tex';
			$mime_magic[0][2]["\367\203"] = 'font/x-tex';
			$mime_magic[0][2]["\367\312"] = 'font/x-tex';
			$mime_magic[0][2]["\36\1"] = 'font/x-vfont';
			$mime_magic[0][2]["\375\166"] = 'application/x-lzh';
			$mime_magic[0][2]["\376\166"] = 'application/data';
			$mime_magic[0][2]["\377\145"] = 'application/data';
			$mime_magic[0][2]["\377\155"] = 'application/data';
			$mime_magic[0][2]["\377\166"] = 'application/data';
			$mime_magic[0][2]["\377\330"] = 'image/jpeg';
			$mime_magic[0][2]["\377\37"] = 'application/data';
			$mime_magic[0][2]["\37\213"] = 'application/x-gzip';
			$mime_magic[0][2]["\37\235"] = 'application/compress';
			$mime_magic[0][2]["\37\236"] = 'application/data';
			$mime_magic[0][2]["\37\237"] = 'application/data';
			$mime_magic[0][2]["\37\240"] = 'application/data';
			$mime_magic[0][2]["\37\36"] = 'application/data';
			$mime_magic[0][2]["\37\37"] = 'application/data';
			$mime_magic[0][2]["\37\377"] = 'application/data';
			$mime_magic[0][2]["\45\41"] = 'application/postscript';
			$mime_magic[0][2]["\4\66"] = 'font/linux-psf';
			$mime_magic[0][2]["\57\57"] = 'text/cpp';
			$mime_magic[0][2]["\5\1"] = 'application/x-locale';
			$mime_magic[0][2]["\6\1"] = 'application/x-executable-file';
			$mime_magic[0][2]["\6\2"] = 'application/x-alan-adventure-game';
			$mime_magic[0][2]["\7\1"] = 'application/x-executable-file';
			$mime_magic[1][3]["\120\116\107"] = 'image/x-png';
			$mime_magic[1][3]["\127\120\103"] = 'text/vnd.wordperfect';
			$mime_magic[2][6]["\55\154\150\64\60\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\144\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\60\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\61\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\62\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\63\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\64\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\150\65\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\172\163\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\172\64\55"] = 'application/x-lha';
			$mime_magic[2][5]["\55\154\172\65\55"] = 'application/x-lha';
			$mime_magic[2][2]["\0\21"] = 'font/x-tex-tfm';
			$mime_magic[2][2]["\0\22"] = 'font/x-tex-tfm';
			$mime_magic[4][4]["\155\144\141\164"] = 'video/quicktime';
			$mime_magic[4][4]["\155\157\157\166"] = 'video/quicktime';
			$mime_magic[4][4]["\160\151\160\145"] = 'application/data';
			$mime_magic[4][4]["\160\162\157\146"] = 'application/data';
			$mime_magic[4][2]["\257\21"] = 'video/fli';
			$mime_magic[4][2]["\257\22"] = 'video/flc';
			$mime_magic[6][18]["\45\41\120\123\55\101\144\157\142\145\106\157\156\164\55\61\56\60"] = 'font/type1';
			$mime_magic[7][22]["\357\20\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60\60"] = 'application/core';
			$mime_magic[7][4]["\0\105\107\101"] = 'font/x-dos';
			$mime_magic[7][4]["\0\126\111\104"] = 'font/x-dos';
			$mime_magic[8][4]["\23\172\53\105"] = 'font/x-sunos-news';
			$mime_magic[8][4]["\23\172\53\110"] = 'font/x-sunos-news';
			$mime_magic[10][25]["\43\40\124\150\151\163\40\151\163\40\141\40\163\150\145\154\154\40\141\162\143\150\151\166\145"] = 'application/x-shar';
			$mime_magic[20][4]["\107\111\115\120"] = 'application/x-gimp-brush';
			$mime_magic[20][4]["\107\120\101\124"] = 'application/x-gimp-pattern';
			$mime_magic[20][4]["\375\304\247\334"] = 'application/x-zoo';
			$mime_magic[21][8]["\41\123\103\122\105\101\115\41"] = 'audio/x-st2-mod';
			$mime_magic[24][4]["\0\0\352\153"] = 'application/x-dump';
			$mime_magic[24][4]["\0\0\352\154"] = 'application/x-dump';
			$mime_magic[24][4]["\0\0\352\155"] = 'application/data';
			$mime_magic[24][4]["\0\0\352\156"] = 'application/data';
			$mime_magic[65][4]["\106\106\111\114"] = 'font/ttf';
			$mime_magic[65][4]["\114\127\106\116"] = 'font/type1';
			$mime_magic[257][8]["\165\163\164\141\162\40\40\60"] = 'application/x-gtar';
			$mime_magic[257][6]["\165\163\164\141\162\60"] = 'application/x-tar';
			$mime_magic[0774][2]["\332\276"] = 'application/data';
			$mime_magic[1080][4]["\103\104\70\61"] = 'audio/x-oktalyzer-mod';
			$mime_magic[1080][4]["\106\114\124\64"] = 'audio/x-startracker-mod';
			$mime_magic[1080][4]["\115\41\113\41"] = 'audio/x-protracker-mod';
			$mime_magic[1080][4]["\115\56\113\56"] = 'audio/x-protracker-mod';
			$mime_magic[1080][4]["\117\113\124\101"] = 'audio/x-oktalyzer-mod';
			$mime_magic[1080][4]["\61\66\103\116"] = 'audio/x-taketracker-mod';
			$mime_magic[1080][4]["\63\62\103\116"] = 'audio/x-taketracker-mod';
			$mime_magic[1080][4]["\64\103\110\116"] = 'audio/x-fasttracker-mod';
			$mime_magic[1080][4]["\66\103\110\116"] = 'audio/x-fasttracker-mod';
			$mime_magic[1080][4]["\70\103\110\116"] = 'audio/x-fasttracker-mod';
			$mime_magic[2048][7]["\120\103\104\137\111\120\111"] = 'x/x-photo-cd-pack-file';
			$mime_magic[2080][29]["\115\151\143\162\157\163\157\146\164\40\105\170\143\145\154\40\65\56\60\40\127\157\162\153\163\150\145\145\164"] = 'application/vnd.ms-excel';
			$mime_magic[2080][27]["\115\151\143\162\157\163\157\146\164\40\127\157\162\144\40\66\56\60\40\104\157\143\165\155\145\156\164"] = 'text/vnd.ms-word';
			$mime_magic[2080][26]["\104\157\143\165\155\145\156\164\157\40\115\151\143\162\157\163\157\146\164\40\127\157\162\144\40\66"] = 'text/vnd.ms-word';
			$mime_magic[2112][9]["\115\123\127\157\162\144\104\157\143"] = 'text/vnd.ms-word';
			$mime_magic[2114][5]["\102\151\146\146\65"] = 'application/vnd.ms-excel';
			$mime_magic[4098][7]["\104\117\123\106\117\116\124"] = 'font/x-dos';
			$mime_magic[68158480][2]["\23\177"] = 'application/x-filesystem';
			$mime_magic[68158480][2]["\23\217"] = 'application/x-filesystem';
			$mime_magic[68158480][2]["\44\150"] = 'application/x-filesystem';
			$mime_magic[68158480][2]["\44\170"] = 'application/x-filesystem';
			$mime_magic[70779960][2]["\357\123"] = 'application/x-linux-ext2fs';

			return $mime_magic;
		}
	}
?>
