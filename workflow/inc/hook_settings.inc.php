<?php
	/**************************************************************************\
	* eGroupWare - Preferences                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	
	// ui_userinterface preferences
	create_select_box('Starting page','startpage',array(
               'Tarefas Pendentes',
               'Processos',
               'Acompanhamento',
               'Aplicações Externas',
			   'Organograma'),
               'This is the first screen shown when you click on the workflow application icon');
	create_select_box('Inbox Sorting', 'inbox_sort', array(
	           'wf_act_started__ASC' => 'Data - Crescente',
	           'wf_act_started__DESC' => 'Data - Decrescente',
	           'wf_procname__ASC' => 'Processo - Crescente',
			   'wf_procname__DESC' => 'Processo - Decrescente',
			   'wf_priority__ASC' => 'Prioridade - Crescente',
			   'wf_priority__DESC' => 'Prioridade - Decrescente',
	           'wf_name__ASC' => 'Atividade - Crescente',
	           'wf_name__DESC' => 'Atividade - Decrescente',
	           'insname__ASC' => 'Identificador - Crescente',
	           'insname__DESC' => 'Identificador - Decrescente'),
			   'Sets the default sorting criteria for the inbox instances.');
	create_select_box('Number of items per page', 'ui_items_per_page', array(
				'5' => '5',
				'10' => '10',
				'15' => '15',
				'20' => '20',
				'25' => '25',
				'30' => '30',
				'40' => '40',
				'50' => '50',
				'100' => '100',
				'150' => '150',
				'200' => '200'),
				'Determines the number of items per page in the user interface.');
	create_select_box('Show activity complete page', 'show_activity_complete_page', array(
				'1' => 'Sim',
				'0' => 'Não'),
				'Determines if the activity complete page should be displayed after the completion of the activity');
	create_select_box('Use light interface', 'use_light_interface', array(
				'1' => 'Sim',
				'0' => 'Não'),
				'Determines if a lighter version of the interface should be used');
?>
