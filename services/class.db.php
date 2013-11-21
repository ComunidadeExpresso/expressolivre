<?php
/**
*
* Copyright (C) 2011 Consףrcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. 
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
* 6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaחu - PR - Brasil or at
* e-mail address prognus@prognus.com.br.
*
*
* @package    DBService
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consףrcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @sponsor    Caixa Econפmica Federal
* @version    1.0
* @since      2.4.0
*/

    if(!isset($_SESSION['phpgw_info']['expressomail']['server']['db_name'])) { 
	include_once('../header.inc.php'); 
	$_SESSION['phpgw_info']['expressomail']['server']['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];  
	$_SESSION['phpgw_info']['expressomail']['server']['db_host'] = $GLOBALS['phpgw_info']['server']['db_host']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_port'] = $GLOBALS['phpgw_info']['server']['db_port']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_user'] = $GLOBALS['phpgw_info']['server']['db_user']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_pass'] = $GLOBALS['phpgw_info']['server']['db_pass']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_type'] = $GLOBALS['phpgw_info']['server']['db_type']; 
    } 
    else{ 
	define('PHPGW_INCLUDE_ROOT','../');      
	define('PHPGW_API_INC','../phpgwapi/inc');       
	include_once(PHPGW_API_INC.'/class.db.inc.php'); 
    } 


class DBService
{    
    var $connection;
    var $db;
    var $user_id;

    function DBService()
    {
        $this->db = new db();		
        $this->db->Halt_On_Error = 'no';
        $this->db->connect(
            $_SESSION['phpgw_info']['expressomail']['server']['db_name'], 
            $_SESSION['phpgw_info']['expressomail']['server']['db_host'],
            $_SESSION['phpgw_info']['expressomail']['server']['db_port'],
            $_SESSION['phpgw_info']['expressomail']['server']['db_user'],
            $_SESSION['phpgw_info']['expressomail']['server']['db_pass'],
            $_SESSION['phpgw_info']['expressomail']['server']['db_type']
        );		
        $this -> user_id = $_SESSION['phpgw_info']['expressomail']['user']['account_id'];
    }

    function search_contacts($search_for)
    { 

        $result = array();
        $query = 'select'
        
						. ' C.id_connection,'
						. ' A.id_contact,'
						. ' A.names_ordered,'
						. ' A.alias,'
						. ' A.birthdate,'
						. ' A.sex,'
						. ' A.pgp_key,'
						. ' A.notes,'
						. ' A.web_page,'
						. ' A.corporate_name,'
						. ' A.job_title,'
						. ' A.department,'
						. ' C.connection_name,'
						. ' C.connection_value,'
						. ' B.id_typeof_contact_connection,'
						. ' phpgw_cc_contact_addrs.id_typeof_contact_address,'
						. ' phpgw_cc_addresses.address1,'
						. ' phpgw_cc_addresses.address2,'
						. ' phpgw_cc_addresses.complement,'
						. ' phpgw_cc_addresses.postal_code,'
						. ' phpgw_cc_city.city_name,'
						. ' phpgw_cc_state.state_name,'
						. ' phpgw_cc_addresses.id_country'
						;

		$query .= ' from'
                        . ' phpgw_cc_contact A'
                        . ' inner join phpgw_cc_contact_conns B on ( A.id_contact = B.id_contact )'
                        . ' inner join phpgw_cc_connections C on ( B.id_connection = C.id_connection )'
                        . ' left join phpgw_cc_contact_addrs on ( A.id_contact = phpgw_cc_contact_addrs.id_contact )'
						. ' left join phpgw_cc_addresses on ( phpgw_cc_contact_addrs.id_address = phpgw_cc_addresses.id_address )'
						. ' left join phpgw_cc_city on ( phpgw_cc_addresses.id_city = phpgw_cc_city.id_city )'
						. ' left join phpgw_cc_state on ( phpgw_cc_addresses.id_state = phpgw_cc_state.id_state)'
						;
			
				$query .= ' where '
                                        . 'A.id_owner=' . $_SESSION['phpgw_info']['expressomail']['user']['account_id']
                                        . ' and lower(translate(names_ordered, \'באגדהיטךכםלןףעפץצתשûüְֱֲֳִָֹÊֻּֽֿ׃ׂװױײÚÙÛÜחַסׁ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))'
										. ' LIKE lower(translate(\'%' . $search_for . '%\', \'באגדהיטךכםלןףעפץצתשûüְֱֲֳִָֹÊֻּֽֿ׃ׂװױײÚÙÛÜחַסׁ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))';  
				
		//Se nדo existir parametro na busca, limita os usuarios no resultado da pesquisa.
				if(!$search_for){
					$query .= 'LIMIT 11';        
				}
				
        if (!$this->db->query($query))
             return null;
         
         while($this->db->next_record())
            $result[] = $this->db->row();

         
         
         $all_contacts = array(); 
			foreach( $result as $i => $object )
			{
				if ( ! array_key_exists( $object[ 'id_contact' ], $all_contacts ) )
					$all_contacts[ $object[ 'id_contact' ] ] = array(
						'connection_value' => '',
						'telephonenumber' => '',
						'mobile' => '',
						'cn' => '',
						'id_contact' => '',
						'id_connection' => '',
						'alias' => '',
						'birthdate' => '',
						'sex' => '',
						'pgp_key' => '',
						'notes' => '',
						'web_page' => '',
						'corporate_name' => '',
						'job_title' => '',
						'department' => '',				
						'mail' => '',
						'aternative-mail' => '',
						'business-phone' => '',
						'business-address' => '',
						'business-complement' => '',
						'business-postal_code' => '',
						'business-city_name' => '',
						'business-state_name' => '',
						'business-id_country' => '',
						'business-fax' => '',
						'business-pager' => '',
						'business-mobile' => '',
						'business-address-2' => '',
						'home-phone' => '',
						'home-address' => '',
						'home-complement' => '',
						'home-postal_code' => '',
						'home-city_name' => '',
						'home-state_name' => '',
						'home-fax' => '',
						'home-pager' => '',
						'home-address-2' => ''
						
						
					);

				switch( $object[ 'id_typeof_contact_connection' ] )
				{
					case 1 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'connection_value' ] = $object[ 'connection_value' ];
						switch ( strtolower( $object[ 'connection_name' ] ) )
						{
							case 'alternativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'alternative-mail' ] = $object[ 'connection_value' ];
								break;
							case 'principal' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'mail' ] = $object[ 'connection_value' ];
								break;
						}
						break;
					case 2 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'telephonenumber' ] = $object[ 'connection_value' ];
						switch ( strtolower( $object[ 'connection_name' ] ) )
						{
							case 'casa' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-phone' ] = $object[ 'connection_value' ];
								break;
							case 'celular' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'mobile' ] = $object[ 'connection_value' ];
								break;
							case 'trabalho' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-phone' ] = $object[ 'connection_value' ];
								break;								
							case 'fax' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-fax' ] = $object[ 'connection_value' ];
								break;
							case 'pager' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-pager' ] = $object[ 'connection_value' ];
								break;
							case 'celular corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-mobile' ] = $object[ 'connection_value' ];
								break;								
							case 'pager corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-pager' ] = $object[ 'connection_value' ];
								break;
							case 'fax corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-fax' ] = $object[ 'connection_value' ];
								break;
						}
						break;
				}

				$all_contacts[ $object[ 'id_contact' ] ][ 'cn' ] = utf8_encode($object[ 'names_ordered' ]);
				$all_contacts[ $object[ 'id_contact' ] ][ 'id_contact' ]    = $object[ 'id_contact' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'id_connection' ] = $object[ 'id_connection' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'alias' ]         = $object[ 'alias' ];				
				$all_contacts[ $object[ 'id_contact' ] ][ 'birthdate' ] 	= $object[ 'birthdate' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'sex' ]    		= $object[ 'sex' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'pgp_key' ] 		= $object[ 'pgp_key' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'notes' ]         = $object[ 'notes' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'web_page' ] 		= $object[ 'web_page' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'corporate_name' ]= $object[ 'corporate_name' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'job_title' ] 	= $object[ 'job_title' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'department' ]    = $object[ 'department' ];

				switch( $object[ 'id_typeof_contact_address' ] )
				{
					case 1 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-address' ]     = $object[ 'address1' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-address-2' ]   = $object[ 'address2' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-complement' ]  = $object[ 'complement' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-postal_code' ] = $object[ 'postal_code' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-city_name' ]   = $object[ 'city_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-state_name' ]  = $object[ 'state_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-id_country' ]  = $object[ 'id_country' ];
						break;
					case 2 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-address' ]     = $object[ 'address1' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-address-2' ]   = $object[ 'address2' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-complement' ]  = $object[ 'complement' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-postal_code' ] = $object[ 'postal_code' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-city_name' ]   = $object[ 'city_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-state_name' ]  = $object[ 'state_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-id_country' ]  = $object[ 'id_country' ];
						break;
				}
			}

			return array_values($all_contacts);
	}

	function search_groups($search_for)
    { 
        $result = array();
		$query = 'select'
						. ' G.oid,'
						. ' G.id_group,'
						. ' G.title,'
						. ' G.short_name';
		$query .= ' from'
                        . ' phpgw_cc_groups G';
		$query .= ' where '
						. ' G.owner=' . $_SESSION['phpgw_info']['expressomail']['user']['account_id']
						. ' and lower(translate(G.title, \'באגדהיטךכםלןףעפץצתשûüְֱֲֳִָֹÊֻּֽֿ׃ׂװױײÚÙÛÜחַסׁ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))' 
						. ' LIKE lower(translate(\'%' . $search_for . '%\', \'באגדהיטךכםלןףעפץצתשûüְֱֲֳִָֹÊֻּֽֿ׃ׂװױײÚÙÛÜחַסׁ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))';        
						
		if (!$this->db->query($query))
             return null;
         
         while($this->db->next_record())
            $result[] = $this->db->row();

		$all_contacts = array(); 
		foreach( $result as $i => $object )
		{
			if ( ! array_key_exists( $object[ 'oid' ], $all_contacts ) )
				$all_contacts[ $object[ 'oid' ] ] = array(
					'title' => '',
					'short_name' => '',
				);
				$all_contacts[ $object[ 'oid' ] ]['title'] = $object['title'];
				$all_contacts[ $object[ 'oid' ] ]['short_name'] = $object['short_name'];
				$all_contacts[ $object[ 'oid' ] ]['id'] = $object[ 'id_group' ];
		}
		return array_values($all_contacts);
	}
}

ServiceLocator::register( 'db', new DBService() );

?>