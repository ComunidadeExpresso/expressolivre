<?php
	/******************************************************\
	 * Im - Instant Messenger										*
	 * Alexandre Correia												*
	 * Rodrigo Souza													*
	\******************************************************/
	
	$setup_info['jabberit_messenger']['name']      	= 'jabberit_messenger';
	$setup_info['jabberit_messenger']['title']     	= 'Expresso Messenger';
	$setup_info['jabberit_messenger']['version']   	= '2.5.2';
	$setup_info['jabberit_messenger']['app_order'] 	= 9;
	$setup_info['jabberit_messenger']['enable']    	= 1;
	
	/* Conf Table */
	
	$setup_info['jabberit_messenger']['author'] 		  = 'http://jeti-im.org/';
	$setup_info['jabberit_messenger']['maintainer']	 	  = 'Alexandre Correia <br/> Rodrigo Souza';
	$setup_info['jabberit_messenger']['maintainer_email'] = 'Os mesmos';

	$setup_info['jabberit_messenger']['license']  	 = 'GPL';
	$setup_info['jabberit_messenger']['description'] = 'Módulo de Mensagens Instantâneas com o Serviço Jabber';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['jabberit_messenger']['hooks'][] = 'admin';
		
	/* Dependencies for this app to work */
	$setup_info['jabberit_messenger']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('2.5.1.1')
	);
?>
