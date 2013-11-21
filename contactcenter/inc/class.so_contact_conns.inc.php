<?php
/*
 * Classe Storage Object - phpgw_cc_contact_conns
 * Baseado na classe so_main de Raphael Derosso Pereira
 * Autor: Luiz Carlos Viana Melo - Prognus
 * luiz@prognus.com.br
 */

	include_once("class.so_main.inc.php");
	
	class so_contact_conns extends so_main
	{
		
		function so_contact_conns ( $id = false)
		{
			$this->init();
			
			$this->main_fields = array(
				'id_connection' => array(
					'name'  => 'id_connection',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'id_contact' => array(
					'name'  => 'id_contact',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'id_typeof_contact_connection' => array(
					'name'  => 'id_typeof_contact_connection',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);

			$this->db_tables = array(
				'phpgw_cc_contact_conns' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_connection']),
						'foreign' => false
					),
					'fields' => & $this->main_fields
				)
			);
			
			if ($id)
			{
				if (!$this->checkout($id))
				{
					$this->reset_values();
					$this->state = 'new';
				}
			}
			else
			{
				$this->state = 'new';
			}
		}
	}

?>