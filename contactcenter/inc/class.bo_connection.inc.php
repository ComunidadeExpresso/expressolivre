<?php
/*
 * Classe responsavel pelas conexoes.
 * Baseado na classe so_main de Raphael Derosso Pereira
 * Autor: Luiz Carlos Viana Melo - Prognus
 * luiz@prognus.com.br
 */

	class bo_connection
	{
		/*!
			@function get_connection_type_by_conn_id
			@abstract This function returns the type of a connection.
			@author Luiz Carlos Viana Melo
			
		 */
		function get_connection_type_by_conn_id($id_connection)
		{
			$so_conns = CreateObject('contactcenter.so_contact_conns', $id_connection);
			$types = $so_conns->get_field('id_typeof_contact_connection');
			return $types[0];
		}
		
		function get_contact_id_by_connection($id_connection)
		{
			$so_conns = CreateObject('contactcenter.so_contact_conns', $id_connection);
			$id_contact = $so_conns->get_field('id_contact');
			return $id_contact;
		}
	}
?>