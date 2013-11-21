<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	$phpgw_baseline = array(
		'phpgw_cc_status' => array(
			'fd' => array(
				'id_status' => array( 'type' => 'int', 'precision' => 2, 'nullable' => false ),
				'status_name' => array( 'type' => 'varchar', 'precision' => 30 )
			),
			'pk' => array( 'id_status' ),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_prefixes' => array(
			'fd' => array(
				'id_prefix' => array( 'type' => 'int', 'precision' => 2, 'nullable' => false ),
				'prefix' => array( 'type' => 'varchar','precision' => 30)
			),
			'pk' => array('id_prefix'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_suffixes' => array(
			'fd' => array(
				'id_suffix' => array( 'type' => 'int', 'precision' => 2,'nullable' => false),
				'suffix' => array( 'type' => 'varchar','precision' => 30)
			),
			'pk' => array('id_suffix'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		
		/* Version 2.0 */
		'phpgw_cc_typeof_ct_rels' => array(
			'fd' => array(
				'id_typeof_contact_relation' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'contact_relation_type_name' => array( 'type' => 'varchar','precision' => 30),
				'contact_relation_is_subordinated' => array( 'type' => 'bool' )
			),
			'pk' => array('id_typeof_contact_relation'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_typeof_ct_addrs' => array(
			'fd' => array(
				'id_typeof_contact_address' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'contact_address_type_name' => array( 'type' => 'varchar', 'precision' => 30)
			),
			'pk' => array('id_typeof_contact_address'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_typeof_ct_conns' => array(
			'fd' => array(
				'id_typeof_contact_connection' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'contact_connection_type_name' => array( 'type' => 'varchar','precision' => 30)
			),
			'pk' => array('id_typeof_contact_connection'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		/* Version 2.0 */
		'phpgw_cc_typeof_co_rels' => array(
			'fd' => array(
				'id_typeof_company_relation' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'company_relation_type_name' => array( 'type' => 'varchar','precision' => 30),
				'company_relation_is_subordinated' => array(  'type' => 'bool' )
			),
			'pk' => array('id_typeof_company_relation'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_typeof_co_addrs' => array(
			'fd' => array(
				'id_typeof_company_address' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'company_address_type_name' => array( 'type' => 'varchar','precision' => 30)
			),
			'pk' => array('id_typeof_company_address'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_typeof_co_conns' => array(
			'fd' => array(
				'id_typeof_company_connection' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'company_connection_type_name' => array( 'type' => 'varchar','precision' => 30)
			),
			'pk' => array('id_typeof_company_connection'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_typeof_co_legals' => array(
			'fd' => array(
				'id_typeof_company_legal' => array( 'type' => 'int', 'precision' => 4,'nullable' => false),
				'legal_type_name' => array( 'type' => 'varchar', 'precision' => 60)
			),
			'pk' => array('id_typeof_company_legal'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		

		'phpgw_cc_state' => array(
			'fd' => array(
				'id_state' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false),
				'id_country' => array( 'type' => 'char', 'nullable' => false, 'precision' => 2),
				'state_name' => array( 'type' => 'varchar', 'precision' => 30),
				'state_symbol' => array( 'type' => 'varchar', 'precision' => 10)
			),
			'pk' => array('id_state'),
			'fk' => array('id_country' => array('phpgw_common_country_list' => 'id_country')),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_city' => array(
			'fd' => array(
				'id_city' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_state' => array( 'type' => 'int', 'precision' => 8 ),
				'id_country' => array( 'type' => 'char', 'nullable' => false, 'precision' => 2),
				'city_timezone' => array( 'type' => 'int', 'precision' => 2 ),
				'city_geo_location' => array(  'type'  => 'varchar', 'precision' => 40 ),
				'city_name' => array( 'type'  => 'varchar', 'precision' => 60, 'nullable' => 'false' ),				
			),
			'pk' => array('id_city'),
			'fk' => array(
				'id_state'   => array('phpgw_cc_state' => 'id_state')
			),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_addresses' => array(
			'fd' => array(
				'id_address' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_city' => array( 'type'  => 'int', 'precision' => 8 ),
				'id_state' => array( 'type' => 'int', 'precision' => 8 ),
				'id_country' => array( 'type' => 'char', 'nullable' => false, 'precision' => 2),
				'address1' => array( 'type'  => 'varchar', 'precision' => 60 ),
				'address2' => array( 'type'  => 'varchar', 'precision' => 60 ),
				'complement' => array( 'type'  => 'varchar', 'precision' => 30 ),
				'address_other' => array( 'type'  => 'varchar', 'precision' => 60 ),
				'postal_code' => array( 'type'  => 'varchar', 'precision' => 15 ),
				'po_box' => array( 'type'  => 'varchar', 'precision' => 30 ),
				'address_is_default' => array(  'type' => 'bool' )
			),
			'pk' => array('id_address'),
			'fk' => array(
				'id_city' => array('phpgw_cc_city' => 'id_city'),
				'id_state'   => array('phpgw_cc_state' => 'id_state')
			),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_cc_connections' => array(
			'fd' => array(
				'id_connection' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'connection_name' => array( 'type'  => 'varchar', 'precision' => 50 ),
				'connection_value' => array( 'type'  => 'varchar', 'precision' => 50 ),
				'connection_is_default' => array(  'type' => 'bool' )
			),
			'pk' => array('id_connection'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		/* Version 2.0 */
		'phpgw_cc_company' => array(
			'fd' => array(
				'id_company'       => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_company_owner' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'company_name'     => array( 'type'  => 'varchar', 'precision' => 30 ),
				'company_notes'    => array( 'type'  => 'text' ),
			),
			'pk' => array('id_company'),
			'fk' => array(
				//'id_company_owner' => array('phpgw_accounts' => 'account_id'),
			),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_company_rels' => array(
			'fd' => array(
				'id_company' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_related' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_company_relation' => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_company', 'id_related'),
			'fk' => array(
				'id_company' => array('phpgw_cc_company' => 'id_company'),
				'id_related' => array('phpgw_cc_company' => 'id_company'),
				'id_typeof_company_relation' => array('phpgw_cc_typeof_co_rels' => 'id_typeof_company_relation')
			),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_company_addrs' => array(
			'fd' => array(
				'id_company' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_address' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_company_address' => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_company', 'id_address'),
			'fk' => array(
				'id_company' => array('phpgw_cc_company' => 'id_company'),
				'id_address' => array('phpgw_cc_addresses' => 'id_address'),
				'id_typeof_company_address' => array('phpgw_cc_typeof_co_addrs' => 'id_typeof_company_address')			
			),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_company_conns' => array(
			'fd' => array(
				'id_company' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_connection' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_company_connection' => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_company', 'id_connection'),
			'fk' => array(
				'id_company' => array('phpgw_cc_company' => 'id_company'),
				'id_connection' => array('phpgw_cc_connections' => 'id_connection'),
				'id_typeof_company_connection' => array('phpgw_cc_typeof_co_conns' => 'id_typeof_company_connection')			
			),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_company_legals' => array(
			'fd' => array(
				'id_company_legal' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_company' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false),
				'id_typeof_company_legal' => array( 'type' => 'int', 'precision' => 4, 'nullable' => false ),
				'legal_info_name' => array( 'type'  => 'varchar', 'precision' => 30 ),
				'legal_info_value' => array( 'type'  => 'varchar', 'precision' => 30 ),
			),
			'pk' => array( 'id_company_legal' ),
			'fk' => array(
				'id_company' => array('phpgw_cc_company' => 'id_company'),
				'id_typeof_company_legal' => array('phpgw_cc_typeof_co_legals' => 'id_typeof_company_legal')			
			),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_cc_contact' => array(
			'fd' => array(
				'id_contact'	=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_owner'		=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_status'		=> array( 'type' => 'int', 'precision' => 4 ),
				'photo'			=> array( 'type' => 'blob' ),
				'alias'			=> array( 'type' => 'varchar', 'precision' => 30 ),
				'id_prefix'		=> array( 'type' => 'int', 'precision' => 4 ),
				'given_names'	=> array( 'type' => 'varchar', 'precision' => 100 ),
				'family_names'	=> array( 'type' => 'varchar', 'precision' => 100 ),
				'names_ordered'	=> array( 'type' => 'varchar', 'precision' => 100 ),
				'id_suffix'		=> array( 'type' => 'int', 'precision' => 4 ),
				'birthdate'		=> array( 'type' => 'date' ),
				'sex'			=> array( 'type' => 'char', 'precision' => 1 ),
				'pgp_key'		=> array( 'type' => 'text' ),
				'notes'			=> array( 'type' => 'text' ),
				'is_global'		=> array( 'type' => 'bool' ),
				'corporate_name'=> array( 'type' => 'varchar', 'precision' => 100),
				'web_page'=> array( 'type' => 'varchar', 'precision' => 100),
				'job_title'=> array( 'type' => 'varchar', 'precision' => 40),
				'department'=> array( 'type' => 'varchar', 'precision' => 30)
			),
			'pk' => array('id_contact'),
			'fk' => array(
				'id_status' => array('phpgw_cc_status' => 'id_status'),
				'id_prefix' => array('phpgw_cc_prefixes' => 'id_prefix'),
				'id_suffix' => array('phpgw_cc_suffixes' => 'id_suffix')
			),
			'ix' => array('is_global'),
			'uc' => array()
		),
		
		/* Version 2.0 */
		'phpgw_cc_contact_rels' => array(
			'fd' => array(
				'id_contact' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_related' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_contact_relation'  => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_contact', 'id_related'),
			'fk' => array(
				'id_contact' => array('phpgw_cc_contact' => 'id_contact'),
				'id_related' => array('phpgw_cc_contact' => 'id_contact'),
				'id_typeof_contact_relation' => array('phpgw_cc_typeof_ct_rels' => 'id_typeof_contact_relation')
			),
			'ix' => array(),
			'uc' => array()
		),
		
		'phpgw_cc_contact_addrs' => array(
			'fd' => array(
				'id_contact'	=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_address'	=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_contact_address' => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_contact', 'id_address'),
			'fk' => array(
				'id_contact' => array('phpgw_cc_contact' => 'id_contact'),
				'id_address' => array('phpgw_cc_addresses' => 'id_address'),
				'id_typeof_contact_address' => array('phpgw_cc_typeof_ct_addrs' => 'id_typeof_contact_address')
			),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_contact_grps' => array(
			'fd' => array(
				'id_group'	=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_connection'	=> array( 'type' => 'int', 'precision' => 8, 'nullable' => false )
			),
			'pk' => array(),
			'fk' => array(
				'id_group' => array('phpgw_cc_groups' => 'id_group'),
				'id_connection' => array('phpgw_cc_connections' => 'id_connection'),
			),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cc_contact_conns' => array(
			'fd' => array(
				'id_contact'    => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_connection' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_typeof_contact_connection' => array( 'type' => 'int', 'precision' => 4 )
			),
			'pk' => array('id_contact', 'id_connection'),
			'fk' => array(
				'id_contact' => array('phpgw_cc_contact' => 'id_contact'),
				'id_connection' => array('phpgw_cc_connections' => 'id_connection'),
				'id_typeof_contact_connection' => array('phpgw_cc_typeof_ct_conns' => 'id_typeof_contact_connection')
			),
			'ix' => array(),
			'uc' => array()
		),
		
		/* Version 2.0 */
		'phpgw_cc_contact_company' => array(
			'fd' => array(
				'id_contact' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'id_company' => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'title'      => array( 'type' => 'varchar', 'precision' => 30 ),
				'department' => array( 'type' => 'varchar', 'precision' => 30 ),
				'default_contact' => array( 'type' => 'bool' ),
				'default_company' => array( 'type' => 'bool' ),
			),
			'pk' => array('id_contact', 'id_company'),
			'fk' => array(
				'id_contact' => array('phpgw_cc_contact' => 'id_contact'),
				'id_company' => array('phpgw_cc_company' => 'id_company')
			),
			'ix' => array(),
			'uc' => array()
		),
		/* Version 2.0 */
		'phpgw_cc_groups' => array(
			'fd' => array(
				'id_group'       => array( 'type' => 'auto', 'nullable' => false ),				
				'title'     => array( 'type'  => 'varchar', 'precision' => 50 ),
				'owner'     => array( 'type' => 'int', 'precision' => 8, 'nullable' => false ),
				'short_name'    => array( 'type'  => 'text', 'precision' => 21 ),
			),
			'pk' => array('id_group'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),		
	);
?>
