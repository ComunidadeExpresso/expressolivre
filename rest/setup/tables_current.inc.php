<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

//Verifica se tem permissão na pasta de configuração antes de começar a instalação do modulo.
require_once dirname(__FILE__) . '/../../prototype/api/config.php';
use prototype\api\Config as Config;

if( Config::writeIniFile(array() , dirname(__FILE__) . '/../../prototype/config/REST.ini', true) === false )
{
	echo "<div style='color:red;font-size:14px'>Permission failure when trying to write in folder \"/prototype/config/\". Grant write permission in folder and try again. </div>";
	die(); //Mata o restante da execução.
}
///

	$phpgw_baseline = array(
		
		'rest_access_token' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'oauth_token' => array('type' => 'varchar','precision' => '40', 'nullable' => false),
				'client_id' => array('type' => 'varchar', 'precision' => '40','nullable' => false),
				'user_id' => array('type' => 'int', 'precision' => '16','nullable' => false),
				'expires' => array('type' => 'int', 'varchar' => '8','nullable' => false),
				'scope' => array('type' => 'varchar', 'varchar' => '255','nullable' => True),
				'refresh_token' => array('type' => 'varchar', 'precision' => '40','nullable' => false)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		
		'rest_auth_code' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'redirect_uri' => array('type' => 'varchar','precision' => '255', 'nullable' => true),
				'client_id' => array('type' => 'varchar', 'precision' => '40','nullable' => false),
				'user_id' => array('type' => 'int', 'precision' => '16','nullable' => false),
				'expires' => array('type' => 'int', 'varchar' => '8','nullable' => false),
				'scope' => array('type' => 'varchar', 'varchar' => '255','nullable' => True),
				'refresh_token' => array('type' => 'varchar', 'precision' => '40','nullable' => false)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
	
		'rest_client' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'redirect_uri' => array('type' => 'varchar','precision' => '255', 'nullable' => true),
				'client_id' => array('type' => 'varchar', 'precision' => '40','nullable' => false),
				'client_secret' => array('type' => 'varchar', 'precision' => '40','nullable' => false)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'rest_refresh_token' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'refresh_token' => array('type' => 'varchar','precision' => '40', 'nullable' => false),
				'client_id' => array('type' => 'varchar', 'precision' => '40','nullable' => false),
				'user_id' => array('type' => 'int', 'precision' => '16','nullable' => false),
				'expires' => array('type' => 'int', 'varchar' => '8','nullable' => false),
				'scope' => array('type' => 'varchar', 'varchar' => '255','nullable' => True),
				'refresh_token' => array('type' => 'varchar', 'precision' => '40','nullable' => false)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

	);
?>
