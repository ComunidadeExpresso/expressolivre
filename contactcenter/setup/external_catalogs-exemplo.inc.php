<?php
/*
 * Created on 20/08/2007 Por Bruno costa
 *
 *	Arquivo de configuracao de catalogos externos
 *
 */

 
 
 	$external_srcs	=	array(
  					1	=>	array(
  						'name'		=>	'Catálogo Externo',
  						'host'		=>	'ldap://localhost',
  						'dn'		=>	'dc=pr,dc=gov,dc=br',
  						'acc'		=>	'',
  						'pw'		=>	'',
  						'obj'		=>	'inetOrgPerson',
  						'branch'	=>	strtolower('ou'),
  						'visible' => $data['cc_ldap_query_automatic']
  					)

  	); 
  	$external_mappings	= array(
  					1	=> array(
				'contact.id_contact'               => array('dn'),
				'contact.photo'                    => array('jpegPhoto'),
				'contact.prefixes.prefix'          => false,
				'contact.alias'                    => array('alias'),
				'contact.given_names'              => array('givenName'),
				'contact.family_names'             => array('sn'),
				'contact.names_ordered'            => array('cn'),//,'displayName'),
				'contact.suffixes.suffix'          => false,
				'contact.birthdate'                => false,
				'contact.sex'                      => false,
				'contact.pgp_key'                  => false,
				'contact.notes'                    => false,
				'contact.mail_forwarding_address' => array('mailForwardingAddress'),
				'contact.account_type' => array('phpgwAccountType'),
                'contact.account_status'           => array('phpgwAccountStatus'),
                'contact.account_visible'          => array('phpgwAccountVisible'),
				'contact.object_class'             => array('objectClass'),
				'contact.business_info.title'      => array('title'),
				'contact.business_info.department' => array('ou'),
				'contact.company.company_name'     => array('o'),
				'contact.company.company_notes'    => array('businessCategory'),
				
				'contact.contact_related.names_ordered' => 'contact.contact_related.typeof_relation.contact_relation_name',
				'contact.contact_related.typeof_relation.contact_relation_name' =>  array(
					'manager'   => array('manager'),
					'secretary' => array('secretary')
				),
				
				'contact.address.address1'         => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('street', 'st', 'postalAddress', 'homePostalAddress'),
				),
				
				'contact.address.postal_code'      => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('PostalCode'),
				),
				
				'contact.address.city.city_name'   => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('l'),
				),
				
				'contact.address.city.state.state_name'       => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => false,
				),
				
				'contact.address.city.country.id_country'     => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('c')
				),
				
				'contact.connection.connection_value'         => 'contact.connection.typeof_connection.contact_connection_type_name',
				'contact.connection.typeof_connection.contact_connection_type_name' => array (
					'email'  => array('mail'),
					'phone'  => array('telephoneNumber'),
					'mobile' => array('mobile'),
					'pager'  => array('pager'),
					'fax'    => array('facsimileTelephoneNumber'),
					'telex'  => array('telexNumber')
				))
			);	
?>
