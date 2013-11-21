<?php
  /***************************************************************************\
  * eGroupWare - Workflow                                                     *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Sidnei Augusto Drovetto Jr <drovetto@gmail.com>                        *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/
/* Default Workflow Database */

/* extract the database connection parameters */
if (!empty($GLOBALS['phpgw']->ADOdb->database))
{
	$workflowHostInfo = array(
		'dbname' => $GLOBALS['phpgw']->ADOdb->database,
		'host' => $GLOBALS['phpgw']->ADOdb->host,
		'port' => $GLOBALS['phpgw']->ADOdb->port,
		'user' => $GLOBALS['phpgw']->ADOdb->user,
		'password' => $GLOBALS['phpgw']->ADOdb->password);
}
else
{
	$hostInfo = "dbname= host= password= port= user= " . $GLOBALS['phpgw']->ADOdb->host;
	$hostInfo = explode(' ', $hostInfo);
	$workflowHostInfo = array();
	foreach ($hostInfo as $parameter)
	{
		$currentParameter = explode('=', $parameter);
		$workflowHostInfo[$currentParameter[0]] = isset($currentParameter[1]) ? $currentParameter[1] : "";
	}
}

if (($workflowHostInfo['password']{0} == "'") && ($workflowHostInfo['password']{strlen($workflowHostInfo['password'])-1} == "'"))
	$workflowHostInfo['password'] = substr($workflowHostInfo['password'], 1, strlen($workflowHostInfo['password']) - 2);

/* connect to the egroupware database */
$GLOBALS['phpgw']->ADOdb->connect($workflowHostInfo['host'].":".$workflowHostInfo['port'], $workflowHostInfo['user'], $workflowHostInfo['password'], $workflowHostInfo['dbname']);

/* create the workflow database */
$GLOBALS['phpgw']->ADOdb->query("CREATE DATABASE workflow WITH OWNER = postgres TEMPLATE = template0 ENCODING = 'LATIN1'");
$GLOBALS['phpgw']->ADOdb->query("CREATE USER admin_workflow WITH PASSWORD 'admin_workflow' NOCREATEDB NOCREATEUSER VALID UNTIL 'infinity'");
$GLOBALS['phpgw']->ADOdb->query("CREATE GROUP workflow");
$GLOBALS['phpgw']->ADOdb->query("ALTER GROUP workflow ADD USER admin_workflow");
$GLOBALS['phpgw']->ADOdb->query("GRANT workflow TO admin_workflow");

/* pre-configure the module */
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'workflow_database_type', 'pgsql'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_type', 'pgsql'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_admin_password', 'admin_workflow'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_name', 'workflow'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_host', 'localhost'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_port', '5432'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'database_admin_user', 'admin_workflow'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'intranet_subnetworks', '10.0.0.0/8'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'log_type_file', 'True'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'log_type_firebug', 'True'));
$GLOBALS['phpgw']->ADOdb->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES(?, ?, ?)", array('workflow', 'log_level', '0'));

/* connect to the new database */
$workflowDB = $GLOBALS['phpgw']->ADOdb;
if ($workflowDB->connect($workflowHostInfo['host'].":".$workflowHostInfo['port'], $workflowHostInfo['user'], $workflowHostInfo['password'], 'workflow'))
{
	/* create a sample application (Music CD Loan) */
	$workflowDB->query("CREATE USER admin_cds WITH PASSWORD 'admin_cds' NOCREATEDB NOCREATEUSER VALID UNTIL 'infinity'");
	$workflowDB->query("ALTER GROUP workflow ADD USER admin_cds");
	$workflowDB->query("CREATE SCHEMA cds AUTHORIZATION admin_cds");
	$workflowDB->query("GRANT ALL ON SCHEMA cds TO postgres");
	$workflowDB->query("GRANT ALL ON SCHEMA cds TO admin_cds");
	$workflowDB->query("CREATE TABLE cds.cdcollection(cdid int4 NOT NULL, title varchar(200), status varchar(40), usuario varchar(200), CONSTRAINT cdcollection_pkey PRIMARY KEY (cdid))");
	$workflowDB->query("ALTER TABLE cds.cdcollection OWNER TO admin_cds");
	$workflowDB->query("GRANT ALL ON TABLE cds.cdcollection TO postgres WITH GRANT OPTION");
	$workflowDB->query("GRANT ALL ON TABLE cds.cdcollection TO admin_cds");
	$workflowDB->query("COMMENT ON TABLE cds.cdcollection IS 'Exemplo de Empréstimo de CDs'");
	$workflowDB->query("INSERT INTO cdcollection VALUES (1, 'Xuxa', 'disponivel', '')");
	$workflowDB->query("INSERT INTO cdcollection VALUES (2, 'Roberto Carlos', 'disponivel', '')");
	$workflowDB->query("INSERT INTO cdcollection VALUES (3, 'Pink Floyd', 'disponivel', '')");

	/* create the new workflow tables */
	$workflowDB->query('CREATE TABLE organizacao (organizacao_id serial NOT NULL, nome character varying(20) NOT NULL, descricao character varying(100) NOT NULL, url_imagem character varying(200), ativa character varying(1) NOT NULL, sitio CHARACTER VARYING(100), gidnumber_simplificado character varying(100))');
	$workflowDB->query('CREATE TABLE area_status (area_status_id serial NOT NULL, organizacao_id integer NOT NULL, descricao character varying(50) NOT NULL, nivel integer NOT NULL)');
	$workflowDB->query('CREATE TABLE centro_custo (organizacao_id integer NOT NULL, centro_custo_id serial NOT NULL, nm_centro_custo integer NOT NULL, grupo character varying(30), descricao character varying(100) NOT NULL)');
	$workflowDB->query('CREATE TABLE localidade (organizacao_id integer NOT NULL, localidade_id serial NOT NULL, centro_custo_id integer, descricao character varying(50) NOT NULL, empresa CHARACTER VARYING(100), endereco CHARACTER VARYING(100), complemento CHARACTER VARYING(50), cep CHARACTER VARYING(9), bairro CHARACTER VARYING(30), cidade CHARACTER VARYING(50), uf CHARACTER(2), externa CHARACTER VARYING(1))');
	$workflowDB->query('CREATE TABLE funcionario (funcionario_id int4 NOT NULL, area_id integer NOT NULL, localidade_id integer NOT NULL, centro_custo_id integer, organizacao_id integer NOT NULL, funcionario_status_id integer NOT NULL, cargo_id int4, nivel int2, funcionario_categoria_id int4, titulo CHARACTER VARYING(30), funcao CHARACTER VARYING(200), data_admissao DATE, apelido CHARACTER VARYING(20))');
	$workflowDB->query('CREATE TABLE area (organizacao_id integer NOT NULL, area_id serial NOT NULL, area_status_id integer NOT NULL, superior_area_id integer, centro_custo_id integer, titular_funcionario_id int4, sigla character varying(20) NOT NULL, descricao character varying(100) NOT NULL, ativa character varying(1) NOT NULL, auxiliar_funcionario_id int4)');
	$workflowDB->query('CREATE TABLE funcionario_status (funcionario_status_id serial NOT NULL, descricao character varying(50) NOT NULL, exibir character varying(1) NOT NULL, organizacao_id integer NOT NULL)');
	$workflowDB->query('CREATE TABLE cargo (cargo_id serial NOT NULL, descricao character varying(150), organizacao_id int4)');
	$workflowDB->query('CREATE TABLE funcionario_categoria (funcionario_categoria_id serial NOT NULL, descricao character varying(150), organizacao_id int4 NOT NULL)');
	$workflowDB->query('CREATE TABLE telefone (telefone_id serial NOT NULL, descricao character varying(50) NOT NULL, numero character varying(50) NOT NULL, organizacao_id integer NOT NULL)');
	$workflowDB->query('CREATE TABLE substituicao (substituicao_id serial NOT NULL, area_id integer NOT NULL, funcionario_id integer NOT NULL, data_inicio date NOT NULL, data_fim date NOT NULL)');


	/* add the constraints */

	/* primary keys */
	$workflowDB->query('ALTER TABLE ONLY organizacao ADD CONSTRAINT organizacao_pkey PRIMARY KEY (organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY area_status ADD CONSTRAINT areastatus_pkey PRIMARY KEY (area_status_id)');
	$workflowDB->query('ALTER TABLE ONLY centro_custo ADD CONSTRAINT centrocusto_pkey PRIMARY KEY (centro_custo_id)');
	$workflowDB->query('ALTER TABLE ONLY localidade ADD CONSTRAINT localidade_pkey PRIMARY KEY (localidade_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT funcionario_pkey PRIMARY KEY (funcionario_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT area_pkey PRIMARY KEY (area_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario_status ADD CONSTRAINT funcionario_status_pkey PRIMARY KEY (funcionario_status_id)');
	$workflowDB->query('ALTER TABLE ONLY telefone ADD CONSTRAINT telefone_pkey PRIMARY KEY (telefone_id)');
	$workflowDB->query('ALTER TABLE ONLY substituicao ADD CONSTRAINT substituicao_pkey PRIMARY KEY (substituicao_id)');
	$workflowDB->query('ALTER TABLE ONLY cargo ADD CONSTRAINT cargo_pkey PRIMARY KEY (cargo_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario_categoria ADD CONSTRAINT funcionario_categoria_pkey PRIMARY KEY (funcionario_categoria_id)');


	/* foreign keys */
	$workflowDB->query('ALTER TABLE ONLY funcionario_status ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY area_status ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY centro_custo ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY localidade ADD CONSTRAINT "$1" FOREIGN KEY (centro_custo_id) REFERENCES centro_custo(centro_custo_id)');
	$workflowDB->query('ALTER TABLE ONLY localidade ADD CONSTRAINT "$2" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$1" FOREIGN KEY (centro_custo_id) REFERENCES centro_custo(centro_custo_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$2" FOREIGN KEY (localidade_id) REFERENCES localidade(localidade_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$3" FOREIGN KEY (area_id) REFERENCES area(area_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$4" FOREIGN KEY (funcionario_status_id) REFERENCES funcionario_status(funcionario_status_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$5" FOREIGN KEY (cargo_id) REFERENCES cargo(cargo_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario ADD CONSTRAINT "$6" FOREIGN KEY (funcionario_categoria_id) REFERENCES funcionario_categoria(funcionario_categoria_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$1" FOREIGN KEY (superior_area_id) REFERENCES area(area_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$2" FOREIGN KEY (centro_custo_id) REFERENCES centro_custo(centro_custo_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$3" FOREIGN KEY (titular_funcionario_id) REFERENCES funcionario(funcionario_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$4" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$5" FOREIGN KEY (area_status_id) REFERENCES area_status(area_status_id)');
	$workflowDB->query('ALTER TABLE ONLY area ADD CONSTRAINT "$6" FOREIGN KEY (auxiliar_funcionario_id) REFERENCES funcionario(funcionario_id)');
	$workflowDB->query('ALTER TABLE ONLY telefone ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY substituicao ADD CONSTRAINT "$1" FOREIGN KEY (area_id) REFERENCES area(area_id)');
	$workflowDB->query('ALTER TABLE ONLY substituicao ADD CONSTRAINT "$2" FOREIGN KEY (funcionario_id) REFERENCES funcionario(funcionario_id)');
	$workflowDB->query('ALTER TABLE ONLY cargo ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');
	$workflowDB->query('ALTER TABLE ONLY funcionario_categoria ADD CONSTRAINT "$1" FOREIGN KEY (organizacao_id) REFERENCES organizacao(organizacao_id)');


	/* set the permissions to the database objects */
	$dbObjects = array('organizacao', 'area_status', 'centro_custo', 'localidade', 'funcionario', 'area', 'funcionario_status', 'telefone',	'substituicao', 'organizacao_organizacao_id_seq', 'area_status_area_status_id_seq', 'centro_custo_centro_custo_id_seq', 'localidade_localidade_id_seq', 'area_area_id_seq', 'funcionario_status_funcionario_status_id_seq', 'cargo', 'cargo_cargo_id_seq', 'funcionario_categoria', 'funcionario_categoria_funcionario_categoria_id_seq', 'telefone_telefone_id_seq', 'substituicao_substituicao_id_seq');

	foreach ($dbObjects as $dbObject)
	{
		$workflowDB->query("GRANT ALL ON TABLE $dbObject TO admin_workflow");
		$workflowDB->query("GRANT ALL ON TABLE $dbObject TO postgres");
		$workflowDB->query("GRANT SELECT ON TABLE $dbObject TO public");
	}
}

/* reconnect to the previous database */
$GLOBALS['phpgw']->ADOdb->connect($workflowHostInfo['host'].":".$workflowHostInfo['port'], $workflowHostInfo['user'], $workflowHostInfo['password'], $workflowHostInfo['dbname']);

?>
