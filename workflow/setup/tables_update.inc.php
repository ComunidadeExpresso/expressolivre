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

	function extractDatabaseParameters()
	{
		/* extract the database connection parameters */

		$workflowHostInfo = array();
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
			foreach ($hostInfo as $parameter)
			{
				$currentParameter = explode('=', $parameter);
				$workflowHostInfo[$currentParameter[0]] = isset($currentParameter[1]) ? $currentParameter[1] : "";
			}
		}
		if (($workflowHostInfo['password']{0} == "'") && ($workflowHostInfo['password']{strlen($workflowHostInfo['password'])-1} == "'"))
			$workflowHostInfo['password'] = substr($workflowHostInfo['password'], 1, strlen($workflowHostInfo['password']) - 2);

		return $workflowHostInfo;
	}

	$test[] = '2.0.000';
	function workflow_upgrade2_0_000()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.0.001';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.0.001';
	function workflow_upgrade2_0_001()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.0.002';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.0.002';
	function workflow_upgrade2_0_002()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.0.003';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.0.003';
	function workflow_upgrade2_0_003()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.0.004';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.0.004';
	function workflow_upgrade2_0_004()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.1.000';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.1.000';
	function workflow_upgrade2_1_000()
	{
		if (!$GLOBALS['phpgw_setup']->oProc->m_bDeltaOnly)
		{
			/* updating log level */
			$values = array('0', 'workflow', 'log_level');
			$GLOBALS['phpgw']->ADOdb->query('UPDATE phpgw_config SET config_value=? WHERE config_app=? AND config_name=?', $values);

			$workflowHostInfo = extractDatabaseParameters();

			/* connect to workflow database */
			$workflowDB = $GLOBALS['phpgw']->ADOdb;
			if ($workflowDB->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], 'workflow'))
			{
				/* creating table substitution */
				$workflowDB->query('CREATE TABLE substituicao (substituicao_id serial NOT NULL, area_id integer NOT NULL, funcionario_id integer NOT NULL, data_inicio date NOT NULL, data_fim date NOT NULL)');

				$workflowDB->query('ALTER TABLE ONLY public.substituicao ADD CONSTRAINT substituicao_pkey PRIMARY KEY (substituicao_id)');
				$workflowDB->query('ALTER TABLE ONLY public.substituicao ADD CONSTRAINT "$1" FOREIGN KEY (area_id) REFERENCES area(area_id)');
				$workflowDB->query('ALTER TABLE ONLY public.substituicao ADD CONSTRAINT "$2" FOREIGN KEY (funcionario_id) REFERENCES funcionario(funcionario_id)');

				/* granting privilegies */
				$workflowDB->query("GRANT ALL ON TABLE public.substituicao TO admin_workflow");
				$workflowDB->query("GRANT ALL ON TABLE public.substituicao TO postgres");
				$workflowDB->query("GRANT SELECT ON TABLE public.substituicao TO public");
				$workflowDB->query("GRANT ALL ON TABLE public.substituicao_substituicao_id_seq TO admin_workflow");
				$workflowDB->query("GRANT ALL ON TABLE public.substituicao_substituicao_id_seq TO postgres");
				$workflowDB->query("GRANT SELECT ON TABLE public.substituicao_substituicao_id_seq TO public");

				/* migrating records */
				$result = $workflowDB->query('SELECT area_id, substituto_funcionario_id FROM area WHERE substituto_funcionario_id IS NOT NULL');
				if ($result)
					while ($row = $result->fetchRow()) {
						$values = array($row['area_id'], $row['substituto_funcionario_id']);
						$workflowDB->query('INSERT INTO substituicao (area_id, funcionario_id, data_inicio, data_fim) VALUES (?, ?, CURRENT_DATE, CURRENT_DATE+integer \'7\')', $values);
					}


				/* erasing old atributes */
				$workflowDB->query("ALTER TABLE area DROP COLUMN substituto_funcionario_id");
			}

			/* reconnect to the previous database */
			$GLOBALS['phpgw']->ADOdb->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], $workflowHostInfo['dbname']);

			/* removing primary key of egw_wf_interinstance_relations */
			$GLOBALS['phpgw']->ADOdb->query('ALTER TABLE egw_wf_interinstance_relations DROP CONSTRAINT egw_wf_interinstance_relations_pkey');

			/* removing wf_parent_activity_id column from egw_wf_interinstance_relations table */
			$GLOBALS['phpgw']->ADOdb->query('ALTER TABLE egw_wf_interinstance_relations DROP COLUMN wf_parent_activity_id');

			/* adding primary key without the column removed */
			$GLOBALS['phpgw']->ADOdb->query('ALTER TABLE egw_wf_interinstance_relations ADD CONSTRAINT egw_wf_interinstance_relations_pkey PRIMARY KEY (wf_parent_instance_id, wf_child_instance_id)');

			/* Update the organogram level of administration off all users from 0 to 1. */
			$GLOBALS['phpgw']->ADOdb->query('UPDATE egw_wf_admin_access set nivel = 1 WHERE tipo = \'ORG\' and nivel = 0');
		}

		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.2.000';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.2.000';
	function workflow_upgrade2_2_000()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.2.1';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.2.1';
	function workflow_upgrade2_2_1()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.2.6';
	return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.2.6';
	function workflow_upgrade2_2_6()
	{
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.3.0';
	return $GLOBALS['setup_info']['workflow']['currentver'];
	}
	$test[] = '2.3.0';
	function workflow_upgrade2_3_0()
	{
		#updating the current version

		$GLOBALS['phpgw']->ADOdb->query("INSERT into phpgw_lang values ('en','workflow','Reports','Reports') ");

		$GLOBALS['phpgw']->ADOdb->query("INSERT into phpgw_lang values ('pt-br','workflow','Reports','Relatórios') ");

		$workflowHostInfo = extractDatabaseParameters();

			/* connect to workflow database */
			$workflowDB = $GLOBALS['phpgw']->ADOdb;
			if ($workflowDB->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], 'workflow'))
			{
				/* creating table substitution */
				$workflowDB->query('CREATE SCHEMA listagem AUTHORIZATION postgres; GRANT ALL ON SCHEMA listagem TO postgres; GRANT ALL ON SCHEMA listagem TO admin_workflow;');

			$workflowDB->query("CREATE TABLE listagem.listagem
							(
							  lstoid serial NOT NULL,
							  lstversao integer,
							  lstidlistagem text NOT NULL,
							  lstdescricao text,
							  lstnome text NOT NULL,
							  lsttitulo text NOT NULL,
							  lstsql text,
							  lstexibe_header boolean,
							  lstexibe_totalizadores boolean,
							  lstexibe_subtotais boolean,
							  lstexibe_qtdregistros boolean,
							  lstexibe_checkbox boolean,
							  lstexibe_csv boolean,
							  lstexibe_legendatopo boolean,
							  lstexibe_legendarodape boolean,
							  lstexibe_titagrupamento boolean,
							  lstexibe_agrupamento_alfabetico boolean,
							  lstagrupamento_campo text,
							  lstagrupamento_titulo text,
							  lstmsg_totalizador text DEFAULT 'TOTAL:'::text,
							  lstmsg_subtotalizador text DEFAULT 'SUB-TOTAL:'::text,
							  lstmsg_registrosencontrados text DEFAULT 'registro(s) encontrado(s).'::text,
							  lstmsg_nenhumresultado text DEFAULT 'Nenhum resultado encontrado.'::text,
							  lstexclusao timestamp without time zone,
							  lstexibe_resultados boolean DEFAULT true,
							  CONSTRAINT lstoid_pkey PRIMARY KEY (lstoid)
							)
							WITHOUT OIDS;
							ALTER TABLE listagem.listagem OWNER TO postgres;
							GRANT ALL ON TABLE listagem.listagem TO postgres;
							GRANT ALL ON TABLE listagem.listagem TO admin_workflow;");


			$workflowDB->query("CREATE TABLE listagem.listagem_coluna
							(
							  lslcoid serial NOT NULL,
							  lslclstoid integer NOT NULL,
							  lslcidcoluna text NOT NULL,
							  lslcordem integer,
							  lslctipo text,
							  lslctitulo text,
							  lslchtml text,
							  lslcalign character varying(10),
							  lslcwidth character varying(10),
							  lslcnowrap boolean,
							  lslcvisivel boolean,
							  lslcexibe_csv boolean,
							  lslccalculada boolean,
							  lslccheckbox boolean,
							  lslclink text,
							  lslclink_blank boolean,
							  lslclink_condicao text DEFAULT '1'::text,
							  lslctotalizador_condicao text DEFAULT '1'::text,
							  lslcsubtotalizador_condicao text DEFAULT '1'::text,
							  lslccheckbox_condicao text DEFAULT '1'::text,
							  CONSTRAINT lslcoid_pkey PRIMARY KEY (lslcoid),
							  CONSTRAINT listagem_coluna_lslclstoid_fkey FOREIGN KEY (lslclstoid)
							      REFERENCES listagem.listagem (lstoid) MATCH SIMPLE
							      ON UPDATE NO ACTION ON DELETE NO ACTION
							)
							WITHOUT OIDS;
							ALTER TABLE listagem.listagem_coluna OWNER TO postgres;
							GRANT ALL ON TABLE listagem.listagem_coluna TO postgres;
							GRANT ALL ON TABLE listagem.listagem_coluna TO admin_workflow;");


			$workflowDB->query("CREATE TABLE listagem.listagem_indicador
								(
								  lsioid serial NOT NULL,
								  lsilstoid integer NOT NULL,
								  lsiidindicador text,
								  lsitipo character(1),
								  lsiimagem text,
								  lsilegenda text,
								  lsilegenda_csv text,
								  lsicondicao text DEFAULT '1'::text,
								  CONSTRAINT lsioid_pkey PRIMARY KEY (lsioid),
								  CONSTRAINT listagem_indicador_lsilstoid_fkey FOREIGN KEY (lsilstoid)
								      REFERENCES listagem.listagem (lstoid) MATCH SIMPLE
								      ON UPDATE NO ACTION ON DELETE NO ACTION
								)
								WITHOUT OIDS;
								ALTER TABLE listagem.listagem_indicador OWNER TO postgres;
								GRANT ALL ON TABLE listagem.listagem_indicador TO postgres;
								GRANT ALL ON TABLE listagem.listagem_indicador TO admin_workflow;");


			$workflowDB->query("CREATE TABLE listagem.listagem_parametro
								(
								  lspoid serial NOT NULL,
								  lsplstoid integer NOT NULL,
								  lspidparametro text,
								  lsptitulo text,
								  lsptipo text,
								  lspvalor_padrao text,
								  lspobrigatorio boolean,
								  CONSTRAINT lspoid_pkey PRIMARY KEY (lspoid),
								  CONSTRAINT listagem_parametro_lsplstoid_fkey FOREIGN KEY (lsplstoid)
								      REFERENCES listagem.listagem (lstoid) MATCH SIMPLE
								      ON UPDATE NO ACTION ON DELETE NO ACTION
								)
								WITHOUT OIDS;
								ALTER TABLE listagem.listagem_parametro OWNER TO postgres;
								GRANT ALL ON TABLE listagem.listagem_parametro TO postgres;
								GRANT ALL ON TABLE listagem.listagem_parametro TO admin_workflow;");




			$workflowDB->query("CREATE SEQUENCE listagem.listagem_coluna_lslcoid_seq
				  INCREMENT 1
				  MINVALUE 1
				  MAXVALUE 9223372036854775807
				  START 1
				  CACHE 1;
				ALTER TABLE listagem.listagem_coluna_lslcoid_seq OWNER TO postgres;


				CREATE SEQUENCE listagem.listagem_indicador_lsioid_seq
				  INCREMENT 1
				  MINVALUE 1
				  MAXVALUE 9223372036854775807
				  START 1
				  CACHE 1;
				ALTER TABLE listagem.listagem_indicador_lsioid_seq OWNER TO postgres;


				CREATE SEQUENCE listagem.listagem_lstoid_seq
				  INCREMENT 1
				  MINVALUE 1
				  MAXVALUE 9223372036854775807
				  START 1
				  CACHE 1;
				ALTER TABLE listagem.listagem_lstoid_seq OWNER TO postgres;


				CREATE SEQUENCE listagem.listagem_parametro_lspoid_seq
				  INCREMENT 1
				  MINVALUE 1
				  MAXVALUE 9223372036854775807
				  START 1
				  CACHE 1;
				ALTER TABLE listagem.listagem_parametro_lspoid_seq OWNER TO postgres;");


			}

		/* reconnect to the previous database */
		$GLOBALS['phpgw']->ADOdb->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], $workflowHostInfo['dbname']);
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.4.0';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}

	$test[] = '2.4.0';
	function workflow_upgrade2_4_0()
	{

		$workflowHostInfo = extractDatabaseParameters();

		/* connect to workflow database */
		$workflowDB = $GLOBALS['phpgw']->ADOdb;
		if ($workflowDB->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], 'workflow'))
		{
			$workflowDB->query('ALTER TABLE ONLY public.funcionario ADD COLUMN funcao CHARACTER VARYING(200)');
			$workflowDB->query('ALTER TABLE ONLY public.funcionario ADD COLUMN data_admissao DATE');
			$workflowDB->query('ALTER TABLE ONLY public.funcionario ADD COLUMN apelido CHARACTER VARYING(20)');
			$workflowDB->query('ALTER TABLE ONLY public.localidade ADD COLUMN externa CHARACTER VARYING(1)');
		}

		/* reconnect to the previous database */
		$GLOBALS['phpgw']->ADOdb->connect($workflowHostInfo['host'], $workflowHostInfo['user'], $workflowHostInfo['password'], $workflowHostInfo['dbname']);
		$GLOBALS['setup_info']['workflow']['currentver'] = '2.4.1';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}

    $test[] = '2.4.1';
    function workflow_upgrade2_4_1()
    {
        $GLOBALS['setup_info']['workflow']['currentver'] = '2.4.2';
        return $GLOBALS['setup_info']['workflow']['currentver'];
    }

    $test[] = '2.4.2';
    function workflow_upgrade2_4_2()
    {
        $GLOBALS['setup_info']['workflow']['currentver'] = '2.5.0';
        return $GLOBALS['setup_info']['workflow']['currentver'];
    }

    $test[] = '2.5.0';
    function workflow_upgrade2_5_0()
    {
        $GLOBALS['setup_info']['workflow']['currentver'] = '2.5.1';
        return $GLOBALS['setup_info']['workflow']['currentver'];
    }


?>
