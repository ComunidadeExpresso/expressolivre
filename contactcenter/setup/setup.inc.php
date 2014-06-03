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

	/* Basic information about this app */
	$setup_info['contactcenter']['name']      = 'contactcenter';
	$setup_info['contactcenter']['title']     = 'ContactCenter';
	$setup_info['contactcenter']['version']   = '2.5.2';
	$setup_info['contactcenter']['app_order'] = 4;
	$setup_info['contactcenter']['enable']    = 1;

	$setup_info['contactcenter']['author'] = 'Raphael Derosso Pereira, Jonas Goes';
	$setup_info['contactcenter']['note']   = 'Contact Center is the new eGW addressbook. It manages all contacts, companies and their informations';
	$setup_info['contactcenter']['license']  = 'GPL';
	$setup_info['contactcenter']['description'] =
		'Contact Center is a modern addressbook application that introduces a new way to ' .
		'do things in the Web Application World. Its key advantages are the possibility to ' .
		'manage Contacts and Companies independently. Users can access 3 types of catalogs: ' .
		'Personal (which manages people and companies), Global (LDAP or DB) and Aliens (shared Personal).';

	$setup_info['contactcenter']['maintainer'] = 'Raphael Derosso Pereira';
	$setup_info['contactcenter']['maintainer_email'] = 'raphaelpereira@users.sourceforge.net';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['contactcenter']['hooks'][] = 'admin';
	$setup_info['contactcenter']['hooks'][] = 'preferences';
	$setup_info['contactcenter']['hooks'][] = 'config_validate';
	$setup_info['contactcenter']['hooks'][] = 'sidebox_menu';
	$setup_info['contactcenter']['hooks'][] = 'settings';
	
	/* ContactCenter Tables */
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_status';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_prefixes';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_suffixes';

	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_ct_rels';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_ct_addrs';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_ct_conns';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_co_rels';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_co_addrs';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_co_conns';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_typeof_co_legals';

	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_state';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_city';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_addresses';

	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_connections';

	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_company';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_company_rels';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_company_addrs';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_company_conns';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_company_legals';

	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact_rels';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact_addrs';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact_conns';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact_company';
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_contact_grps';
	
	$setup_info['contactcenter']['tables'][] = 'phpgw_cc_groups';
	
	/* Dependencies for this app to work */
	$setup_info['contactcenter']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('2.5.1.1')
	);
?>
