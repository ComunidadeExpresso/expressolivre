<?php

require_once ROOTPATH.'/modules/calendar/interceptors/Helpers.php';

class LDAPMapping extends Helpers {

   public function encodeFindUser( &$uri , &$params , &$criteria , $original ){
            if(isset($criteria['filter']))
            {
                if($criteria['filter'][0] === '*' && $criteria['filter'][1] === 'name') //Busca pelo nome usar DFD00032
                    $criteria['filter'] = array('AND',
                                                array('AND' , 
                                                            array('=' , 'objectClass' , 'phpgwAccount'),
                                                            array('OR' , 
                                                                        array('=' , 'phpgwAccountType' , 'i'),
                                                                        array('=' , 'phpgwAccountType' , 'u'),
                                                                        array('=' , 'phpgwAccountType' , 's')
                                                                 ) 
                                                    ),
                                                 array('AND' ,  
                                                                array('!','phpgwAccountVisible','-1'),
                                                                array('OR', 
                                                                        array('=','phpgwAccountStatus','A'),
                                                                        array('=','accountStatus','active'),
                                                                        array('=','objectClass','posixGroup')
                                                                    ),
                                                                array('OR', 
                                                                        array('*','name',$criteria['filter'][2]),
                                                                        array('*','givenName',$criteria['filter'][2]),
                                                                        array('*','uid',$criteria['filter'][2]),
                                                                        array('*','sn',$criteria['filter'][2]),
                                                                        array('*','displayName',$criteria['filter'][2]),
                                                                        array('*','mail',$criteria['filter'][2]),
                                                                        array('*','mailAlternateAddress',$criteria['filter'][2]),
                                                                    )
                                                          )
                                                );
            
                else
                     $criteria['filter'] = array('AND',
                                                array('AND' , 
                                                            array('=' , 'objectClass' , 'phpgwAccount'),
                                                            array('OR' , 
                                                                        array('=' , 'phpgwAccountType' , 'i'),
                                                                        array('=' , 'phpgwAccountType' , 'u'),
                                                                        array('=' , 'phpgwAccountType' , 's')
                                                                 )
                                                     ),$criteria['filter']
                                                );                      
            }
          
    }  
   

       public function encodeFindGroup( &$uri , &$params , &$criteria , $original ){
            if(isset($criteria['filter']))
            {
                if($criteria['filter'][0] === '*' && $criteria['filter'][1] === 'name') //Busca pelo nome group DFD00032
                    $criteria['filter'] = array('AND',
                                                array('AND' , 
                                                            array('=' , 'objectClass' , 'phpgwAccount'),
                                                            array('=' , 'phpgwAccountType' , 'g')
                                                                 
                                                    ),
                                                 array('AND' ,  
                                                                array('!','phpgwAccountVisible','-1'),
                                                                array('OR', 
                                                                        array('=','accountStatus','active'),
                                                                        array('=','objectClass','posixGroup')
                                                                    ),
                                                                array('OR', 
                                                                        array('*','name',$criteria['filter'][2]),
                                                                        array('*','id',$criteria['filter'][2]),
                                                                        array('*','mail',$criteria['filter'][2]),
                                                                        array('*','mailAlternateAddress',$criteria['filter'][2]),
                                                                    )
                                                          )
                                                );
                
                
                    else
                        $criteria['filter'] = array('AND',
                            array('AND' , 
                                        array('=' , 'objectClass' , 'phpgwAccount'),
                                        array('=' , 'phpgwAccountType' , 'g')
                                    ),$criteria['filter']
                            );                      
            }
          
    }  
     
    
    
}

?>
