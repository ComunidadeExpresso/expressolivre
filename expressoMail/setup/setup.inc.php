<?php

/* * ********************************************************************************\
 * Expresso Administração                 										   *
 * by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  *
 * ---------------------------------------------------------------------------------*
 *  This program is free software; you can redistribute it and/or modify it         *
 *  under the terms of the GNU General Public License as published by the           *
 *  Free Software Foundation; either version 2 of the License, or (at your          *
 *  option) any later version.											           *
  \********************************************************************************* */

$setup_info['expressoMail']['name'] 		= 'expressoMail';
$setup_info['expressoMail']['title'] 		= 'Expresso Mail';
$setup_info['expressoMail']['version'] 		= '2.5.2';
$setup_info['expressoMail']['app_order']	= 2;
$setup_info['expressoMail']['tables'][]		= 'phpgw_certificados';

$setup_info['expressoMail']['tables'][] = 'expressomail_attachment';
$setup_info['expressoMail']['tables'][] = 'expressomail_label';
$setup_info['expressoMail']['tables'][] = 'expressomail_message_followupflag';
$setup_info['expressoMail']['tables'][] = 'expressomail_followupflag';
$setup_info['expressoMail']['tables'][] = 'expressomail_dynamic_contact';


$setup_info['expressoMail']['enable'] = 1;

$setup_info['expressoMail']['author'] 			= 'Jo&atilde;o Alfredo Knopik Junior (joao.alfredo@gmail.com / jakjr@celepar.pr.gov.br)<br />' .
												  'Nilton Emílio Bührer Neto (nilton.neto@gmail.com / niltonneto@celepar.pr.gov.br)';

$setup_info['expressoMail']['maintainer'] 		= 'Empresa ou Instituição onde o seu Expresso está instalado.';
$setup_info['expressoMail']['maintainer_email'] = '';
$setup_info['expressoMail']['license'] 			= 'GPL';
$setup_info['expressoMail']['description'] 		= 'Módulo de Email, usando metodologia AJAX';

/* The hooks this app includes, needed for hooks registration */
$setup_info['expressoMail']['hooks'][] = 'preferences';
$setup_info['expressoMail']['hooks'][] = 'admin';
$setup_info['expressoMail']['hooks'][] = 'home';
$setup_info['expressoMail']['hooks'][] = 'settings';
$setup_info['expressoMail']['hooks'][] = 'config_validate';

/* Dependencies for this app to work */
$setup_info['expressoMail']['depends'][] = array(
    'appname' => 'phpgwapi',
    'versions' => Array('2.5.1.1')
);

?>
