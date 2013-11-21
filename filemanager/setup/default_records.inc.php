<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	// CREATE TABLE PHPGW_VFS
 	$res_exists = $oProc->query("select count(*) from information_schema.tables where table_name = 'phpgw_vfs'");
    $oProc->next_record();
    if(!$oProc->f(0)) {
		$oProc->query("CREATE TABLE phpgw_vfs ( file_id integer DEFAULT nextval(('seq_phpgw_vfs'::text)::regclass) NOT NULL,
					owner_id integer NOT NULL,
					createdby_id integer,
					modifiedby_id integer,
					created date DEFAULT '1970-01-01'::date NOT NULL,
					modified date,
					size integer,
					mime_type character varying(200),
					deleteable character(1) DEFAULT 'Y'::bpchar,
					comment character varying(255),
					app character varying(25),
					directory character varying(255),
					name character varying(128) NOT NULL,
					link_directory character varying(255),
					link_name character varying(128),
					version character varying(30) DEFAULT '0.0.0.0'::character varying NOT NULL,
					content text,
					type integer DEFAULT 0,
					summary bytea
					);");
	
		// CREATE SEQUENCE
		$oProc->query("CREATE SEQUENCE seq_phpgw_vfs INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;");
	
		// SET VALUE SEQUENCE
		$oProc->query("SELECT pg_catalog.setval('seq_phpgw_vfs', 4, true);");		
    }
    
	// CREATE TABLE PHPGW_VFS_QUOTA
	$oProc->query("CREATE TABLE phpgw_vfs_quota (directory VARCHAR(100), quota_size INT NOT NULL, PRIMARY KEY (directory));");
	
	// DELETE
	$oProc->query("DELETE FROM phpgw_config WHERE config_app = 'filemanager';");
	$oProc->query("DELETE FROM phpgw_preferences WHERE preference_app = 'filemanager';");
	
	// INSERT
	$oProc->query("INSERT INTO phpgw_config VALUES ('filemanager','filemanager_quota_size',500);");
	$oProc->query("INSERT INTO phpgw_config VALUES ('filemanager','filemanager_Max_file_size',20);");
	$oProc->query('INSERT INTO phpgw_preferences VALUES (-2,\'filemanager\',\'a:19:{s:4:"name";s:1:"1";s:9:"mime_type";s:1:"1";s:4:"size";s:1:"1";s:7:"created";s:1:"1";s:8:"modified";s:1:"1";s:5:"owner";s:1:"1";s:12:"createdby_id";s:1:"1";s:13:"modifiedby_id";s:1:"1";s:3:"app";s:1:"0";s:7:"comment";s:1:"1";s:7:"version";s:1:"1";s:12:"viewinnewwin";s:1:"1";s:12:"viewonserver";s:1:"1";s:13:"viewtextplain";s:1:"1";s:6:"dotdot";s:1:"1";s:8:"dotfiles";s:1:"1";s:8:"pdf_type";s:8:"portrait";s:14:"pdf_paper_type";s:2:"a4";s:14:"files_per_page";s:3:"200";}\');');
	$oProc->query("INSERT INTO phpgw_vfs (owner_id, createdby_id, modifiedby_id, created, modified, size, mime_type, deleteable, comment, app, directory, name, link_directory, link_name) VALUES (1,0,0,'1970-01-01',NULL,NULL,'Directory','Y',NULL,NULL,'/','', NULL, NULL);");
	$oProc->query("INSERT INTO phpgw_vfs (owner_id, createdby_id, modifiedby_id, created, modified, size, mime_type, deleteable, comment, app, directory, name, link_directory, link_name) VALUES (2,0,0,'1970-01-01',NULL,NULL,'Directory','Y',NULL,NULL,'/','home', NULL, NULL);");

?>