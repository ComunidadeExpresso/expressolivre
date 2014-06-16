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
	
	$oProc->query('ALTER TABLE phpgw_cc_contact ALTER COLUMN last_status SET DEFAULT \'N\'::bpchar;');
	$oProc->query('ALTER TABLE phpgw_cc_contact ALTER COLUMN last_update SET DEFAULT (date_part(\'epoch\'::text, (\'now\'::text)::timestamp(3) with time zone) * (1000)::double precision);');
	$oProc->query(
'CREATE FUNCTION share_catalog_delete()
	RETURNS trigger
	LANGUAGE plpgsql
	AS $$
	BEGIN
		IF ( old.acl_appname = \'contactcenter\' AND old.acl_location <> \'run\' ) THEN
			DELETE
			FROM phpgw_cc_contact_rels
			WHERE id_contact = old.acl_location::bigint
			AND id_related = old.acl_account
			AND id_typeof_contact_relation = 1;
		END IF;
		RETURN new;
	END;
$$;');
	$oProc->query(
'CREATE FUNCTION share_catalog_insert()
	RETURNS trigger
	LANGUAGE plpgsql
	AS $$
	BEGIN
		IF ( new.acl_appname = \'contactcenter\' AND new.acl_location <> \'run\' ) THEN
			INSERT
			INTO phpgw_cc_contact_rels ( id_contact, id_related, id_typeof_contact_relation )
			VALUES ( new.acl_location::integer, new.acl_account, 1 );
		END IF;
		RETURN new;
	END;
$$;');
	$oProc->query('CREATE TRIGGER trig_share_catalog_delete AFTER DELETE ON phpgw_acl FOR EACH ROW EXECUTE PROCEDURE share_catalog_delete();');
	$oProc->query('CREATE TRIGGER trig_share_catalog_insert AFTER INSERT ON phpgw_acl FOR EACH ROW EXECUTE PROCEDURE share_catalog_insert();');
	
	/* Default Contact Center Data */
	$oProc->query("insert into phpgw_cc_typeof_ct_conns (id_typeof_contact_connection,contact_connection_type_name) values(1,'Email')");
	$oProc->query("insert into phpgw_cc_typeof_ct_conns (id_typeof_contact_connection,contact_connection_type_name) values(2,'Telefone')");
	$oProc->query("insert into phpgw_cc_typeof_ct_addrs (id_typeof_contact_address,contact_address_type_name) values(1,'Comercial')");
	$oProc->query("insert into phpgw_cc_typeof_ct_addrs (id_typeof_contact_address,contact_address_type_name) values(2,'Residencial')");
	
	// Populate brazilian database.
	include("states_pt-br.inc.php");
	include("cities_pt-br.inc.php");