<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  - Nilton Emilio Buhrer Neto <nilton.neto@gmail.com>                      *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/
	/* Default Contact Center Data */	
	$oProc->query("insert into phpgw_cc_typeof_ct_conns (id_typeof_contact_connection,contact_connection_type_name) values(1,'Email')");
	$oProc->query("insert into phpgw_cc_typeof_ct_conns (id_typeof_contact_connection,contact_connection_type_name) values(2,'Telefone')");
	$oProc->query("insert into phpgw_cc_typeof_ct_addrs (id_typeof_contact_address,contact_address_type_name) values(1,'Comercial')");	
	$oProc->query("insert into phpgw_cc_typeof_ct_addrs (id_typeof_contact_address,contact_address_type_name) values(2,'Residencial')");
  $oProc->query("ALTER TABLE phpgw_cc_groups SET WITH OIDS;");
	// Populate brazilian database.
	include("states_pt-br.inc.php"); 
	include("cities_pt-br.inc.php");