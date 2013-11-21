<?php
/* Upload de arquivos e encaminhado para seu respectivo conceito
 * com o source em base64 melhoria na performace.
 */

$data = $_POST;
	
if( count($_FILES) )
{
    $files = array();
    foreach( $_FILES as $name => $file )
    {
	if( is_array( $file['name'] ) )
	{
	    foreach( $file['name'] as $key => $value ){
		$counter = count($files);
		$files[$name.$counter] = array('name' => $file['name'][$counter], 
			'type' => $file['type'][$counter],
			'source' => base64_encode(file_get_contents( $file['tmp_name'][$counter], $file['size'][$counter])),
			'size' => $file['size'][$counter],
			'error' => $file['error'][$counter]
		);
	    }
	}else
	    $files[$name] = $file;
    } 

    $_FILES = $files;	


    if(isset($data['MAX_FILE_SIZE']))
	unset($data['MAX_FILE_SIZE']);	
}

	
require_once "api/controller.php";

Controller::addFallbackHandler( 0, function($e){ throw $e; } );

$result = array();
foreach( $data as $concept => &$content )
{
	if(!is_array($content))
		$content = array($content);
		
	foreach($content as $key => $value){
		try{
			$result[$concept][] = Controller::put( array( 'concept' => $concept ), $value );
		}catch(Exception $e){
			$result[$concept]['error'] = $e->getMessage();			
		} 
	}
}	
echo json_encode( $result );