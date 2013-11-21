<?php

session_id( $_GET['id'] );
session_start();
setcookie( 'jupload', $_GET['id'], time()+3600 );

include 'jupload.php';

function handle_uploaded_files($juploadPhpSupportClass, $files) 
{
	return
		"<P>We are in the 'handle_uploaded_files' callback function, in the index.php script. To avoid double coding, we "
		. "just call the default behavior of the JUpload PHP class. Just replace this by your code...</P>"
		. $juploadPhpSupportClass->defaultAfterUploadManagement();
	;

}

// Set Max File Size
$maxFileSize = "10M";

if ( file_exists('setFileMaxSize.php') )
{
	require_once('setFileMaxSize.php');
	$maxFileSize  = unserialize(base64_decode($SET_FILE_MAX_SIZE));
	$maxFileSize  = trim($maxFileSize)."M";
}

// Set Max Chunk Size
$maxChunkSize = ini_get('upload_max_filesize');
$maxChunkSize = ( $maxChunkSize*1024 )*1024;

$appletParameters = array(
							'maxFileSize'		=> $maxFileSize,
							'maxChunkSize'		=> $maxChunkSize,
							'archive'			=> '../tp/juploader/wjhk.jupload.jar',
							'afterUploadURL'	=> 'after_upload.php',
							'sendMD5Sum'		=> 'true',
							'showLogWindow'		=> 'false',
							'debugLevel'		=> 0 
						);

$classParameters = array(
							'demo_mode'		=> false,
							'allow_subdirs'	=> true,
							'destdir'		=> '/tmp'  //Where to store the files on the webserver 
						);

$juploadPhpSupportClass = new JUpload($appletParameters, $classParameters);

echo '<div align="left"><!--JUPLOAD_FILES--></div>';
echo '<div align="left"><!--JUPLOAD_APPLET--></div>';

?>