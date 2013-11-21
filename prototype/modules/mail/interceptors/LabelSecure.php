<?php

use prototype\api\Config as Config;

class LabelSecure {	

	//label:before.find
	public function addVerifyOwnerFilter (&$uri , &$params , &$criteria , $original ){
		
		$ownerFilter = array( '=' , 'uid' , Config::me('uidNumber') );
		
		if (isset($criteria['filter']) && $criteria['filter']!=NULL && count($criteria['filter']) != 0)
			$criteria['filter'] = array( 'AND', $criteria['filter'], $ownerFilter );
		else
			$criteria['filter'] =  $ownerFilter;
			
	}  

}

?>
