<?php
session_id($_COOKIE['jupload']);
session_start();
$files = $_SESSION['juvar.files'];
unset($_SESSION['juvar.files']);
session_write_close();

include_once("../../header.session.inc.php");

$GLOBALS['phpgw_info']['flags'] = array
	(
		'currentapp'    => 'filemanager',
		'noheader'      => True,
		'nonavbar' => True,
		'nofooter'      => True,
		'noappheader'   => True,
		'enable_browser_class'  => True
	);


include_once("../../header.inc.php");


function convert_char( $String )
{
		$String = trim( str_replace( "\'", "", $String) );
		$String = str_replace( "'", "", $String );
		$String = str_replace( "ç", "c", $String );
		$String = str_replace( "Ç", "C", $String );
		$String = preg_replace( '/[áàâã]/', "a", $String );
		$String = preg_replace( '/[ÁÀÂÃ]/', "A", $String );
		$String = preg_replace( '/[éèê]/', "e", $String );
		$String = preg_replace( '/[ÉÈÊ]/', "E", $String );
		$String = preg_replace( '/[íìîï]/', "i", $String );
		$String = preg_replace( '/[ÍÌÎ]/', "I", $String );
		$String = preg_replace( '/[óòôõ]/', "o", $String );
		$String = preg_replace( '/[ÓÒÔÕ]/', "O", $String );
		$String = preg_replace( '/[úùû]/', "u", $String );
		$String = preg_replace( '/[ÚÙÛ]/', "U", $String );
		
		return $String;
}

$bo = CreateObject('filemanager.bofilemanager');

foreach ($files as $f)
{
	$newName = convert_char( $f['name'] );
	
	$_array = array(
			'from'		=> $f['fullName'],
			'to'		=> $newName,
			'relatives'	=> array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
	);
	
	if ( $bo->vfs->cp($_array) )
	{
		$bo->vfs->set_attributes(array(
			'string'		=> $newName,
			'relatives'		=> array( RELATIVE_ALL ),
			'attributes'	=> array( 'mime_type' => $f['mimetype'] )
		));
		
		$fullName = $f['fullName'];
		
		if( file_exists($fullName) )
		{
			exec("rm -f ".escapeshellcmd(escapeshellarg($fullName)));
		}
	}
}

echo "<script type='text/javascript' src='../js/after_upload.js'></script>";

?>