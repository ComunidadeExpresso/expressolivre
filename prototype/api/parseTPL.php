<?php

class ParseTPL
{
	
	static function load_tpl(&$data, $file){
      $tpl = '' ;
	if( $fd = @fopen($file,"r")){
		while( !feof($fd) ){
			$tpl = fread($fd,1024);
		}
	}
	foreach($data as $i => $v){
		$tpl = str_replace('['.$i.']',$v,$tpl);}
	
	return $tpl;
	}	
}

?>
