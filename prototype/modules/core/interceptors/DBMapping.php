<?php

class DBMapping {

     static $preferenceMap = array(  'id' => 'id',
                                     'name' => 'name',
                                     'module' => 'module_id');
     
     static $moduleMap = array(  'id' => 'id',
                                 'name' => 'name');
          
          
        
//Encode Creates     
    public function encodeCreatePreference(&$uri , &$params , &$criteria , $original , &$service){
        
    }
    
    public function encodeCreateModule(&$uri , &$params , &$criteria , $original , &$service){


        
    }
    
    private static function parseConcept( $data, &$map , $flip = false )
	{
        if($flip === true)
            $map = array_flip($map);
        
		$new = array();
		foreach ($data as $i => $v)
			if(array_key_exists($i, $map))
					$new[$map[$i]] = $v;
		return $new;
	}
}

?>
