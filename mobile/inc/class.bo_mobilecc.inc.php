<?php
   class bo_mobilecc {
   		var $bo = null;	
		var $page_info = array (
				'actual_catalog' => false,
				'is_external_catalog' => false
			);
		
		function set_catalog($catalog) {
			if(strpos($catalog,'bo_global_ldap_catalog')===false) //Ldap?
				$this->bo= CreateObject("contactcenter.".$catalog);
			else {
				$exploded = explode("#",$catalog);
				$this->bo= CreateObject("contactcenter.bo_global_ldap_catalog",$exploded[1],$exploded[2],$exploded[3]);
				if($exploded[3]==1)
					$this->page_info['is_external_catalog'] = true;
				else {
					$this->page_info['is_external_catalog'] = false;
				}

			}
			$this->page_info['actual_catalog'] = $catalog;
		}
		
		
		/**
		 * Busca um nome a partir do catálogo. Se nenhum for setado utilizando a função set_catalog
		 * ou se não houverem resultados, retorna um array vazio.
		 * 
		 * Caso o catálogo seja grupos, a busca será por títulos, caso seja
		 * contatos pessoais, será por names_ordered, se for contato no ldap, será
		 * por cn.
		 * 
		 * @return array, com os ids relativos a busca
		 * @param $name string com o nome à ser buscado.
		 */
		public function find($name) {
			if(!$this->page_info["actual_catalog"]) //Essa função 
				return array();
			
			if($this->page_info['actual_catalog']=="bo_group_manager") { //parametros de busca para grupos
				$id = 'group.id_group';
				$what = array('group.title',$id);
				$search = "group.title";
			}
			else { //parametros de busca para pessoas
				$id = 'contact.id_contact';
				$search = "contact.names_ordered";
				$what = array('contact.names_ordered',$id);
			}

			if ((strpos($this->page_info['actual_catalog'],'bo_global_ldap_catalog')!==false) &&
					(!$this->page_info['is_external_catalog'])) { //Ldap do expresso, leva em conta alguns atributos do expresso

				array_push($what,'contact.object_class',
						'contact.account_visible',
						'contact.account_status'
						);
				
				$rules = array(
							0 => array(
								'field' => 'contact.object_class',
								'type'  => '=',
								'value' => 'phpgwAccount'
							),
							1 => array(
								'field' => 'contact.account_status',
								'type'  => 'iLIKE',
								'value' => '%'
							),
							2 => array(
								'field' => 'contact.account_visible',
								'type'  => '!=',
								'value' => '-1'
							),
							3 => array(
								'field' => 'contact.object_class',
								'type'  => '=',
								'value' => 'inetOrgPerson'
							)
						);
			}
			else{
				$rules = array();
			}

			
			
			if ($name != '') { //String em branco, não preciso adicionar essa regra
				array_push($rules,array(
								'field' => $search,
								'type' 	=> 'iLIKE',
								'value'	=> $name
							));		
			}
			$ids = $this->bo->find($what,$rules,array('order'=>$search),false,true);

			if(is_array($ids)) {
				foreach($ids as $id_r) { //Quero apenas os ids, como valores nas posições do array
					$retorno[] = $id_r[substr($id,strpos($id,".")+1)];
				}
			}
			else 
				$retorno = array();
			return $retorno;
		}
		
		function search($name,$max_results) {
			if(!$this->page_info["actual_catalog"])
				return array();
			
			$entries = array();
			$ids = $this->find($name);
			if(empty($ids))
				return array();
			$total_count_search = count($ids);
			$ids = array_slice($ids,0,$max_results,true);
			
			if($this->page_info['actual_catalog']!="bo_group_manager") { //Se não for grupo, tenho que ordenar as connections
				$entries = $this->bo->get_multiple_entries($ids,array("names_ordered"=>true,"uidnumber"=>true,"id_contact"=>true,"connections"=>true));			
				/**
				 * As entradas vindas de get_multiple_entries não vem com as connections
				 * ordenadas. Abaixo eu ordeno o array connections de cada entrada para ter
				 * sempre na frente os valores defaults, primeiro o default de email, depois
				 * o de telefone.
				 */
				foreach($entries as &$valor) {
					/* Sempre iniciar os arrays, pois pode interferir na 
					 * ordenação atual se tiverem valores antigos desnecessários 
					 * causando erro de tamanhos inconsistentes */
					$valor['catalog'] = $this->page_info['actual_catalog'];
					$default = array(); 
					$type = array();
					
					foreach($valor['connections'] as $key => $value) {
						$default[$key] = $value['connection_is_default'];
						$type[$key] = $value['id_type'];
					}
					array_multisort($default, SORT_DESC, $type, SORT_ASC, $valor['connections']);
				}//Fim da ordenação
			}
			else {
					$entries = $this->bo->get_multiple_entries($ids,array("id_group"=>true,"title"=>true,"short_name"=>true));
					foreach ($entries as &$group) {
						$group['catalog']=$this->page_info['actual_catalog'];
					}
				}
			if($total_count_search>count($ids))
				$entries["has_more"] = true;
			return $entries;
		}

		/**
		 * Lista todos os catalogos do contact center. Seu retorno é um array no seguinte formato:
		 * 
		 * [pos] - posição do array, incremental
		 * 	[catalog] - catalogo
		 * 	[label] - nome do catálogo
		 * 
		 * A posição catalog para catálogos ldap, possuem o seguinte padrão:
		 * 	catalogo#dn_base#is_external
		 * 
		 * @return 
		 */
		function get_all_catalogs() {
			$retorno = array();
			$bo_cc = CreateObject("contactcenter.bo_contactcenter");
			$branchs = $bo_cc->get_catalog_tree();
			
			foreach($branchs as $branch) { //Catalogos pessoais
				if($branch['class']!="bo_global_ldap_catalog" && $branch['class']!="bo_catalog_group_catalog") {//Pego apenas a estrutura dos ramos vindos do banco, pois as do ldap estão confusas e com dados aparentemente inconsistentes.
					
					$catalog = array("catalog"=>$branch['class'],"label"=>$branch['name']);
					$retorno[] = $catalog;
				}
			}
			
			$bo_ldap_manager = CreateObject("contactcenter.bo_ldap_manager");

			$branches = $bo_ldap_manager->get_all_ldap_sources();	//Ldaps expresso	
			if(is_array($branches)) {
				foreach($branches as $id=>$branch) { 
					$catalog = array('catalog'=>"bo_global_ldap_catalog#".
							$id."#".
							$branch['dn']."#".
							0,
							'label'=>$branch['name']);
					$retorno[] = $catalog;
				}
			}
			
			$branches = $bo_ldap_manager->get_external_ldap_sources();//Ldaps externos
			if(is_array($branches)) {
				foreach($branches as $id=>$branch) { 
					$catalog =array('catalog'=>"bo_global_ldap_catalog#".
							$id."#".
							$branch['dn']."#".
							1,
							'label'=>$branch['name']); 
				}
				$retorno[] = $catalog;
			}
			return $retorno;
		}
		
		function remove_multiple_entries($ids) {
			$errors = array();
			$return = array();
			if($this->page_info['actual_catalog']==='bo_group_manager')
				$soGroup = CreateObject('contactcenter.so_group');
			foreach($ids as $id) {
				if($this->page_info['actual_catalog']==='bo_group_manager') {
					$data = array ('id_group' => $id);
					$check = $soGroup->delete($data);
				}
				else
					$check = $this->bo->remove_single_entry($id);
				
				if(!$check)
					$errors[] = $id;
			}
			$return['success'] = empty($errors)?true:false;
			$return['errors_ids'] = $errors;
			return $return;
		}
		
 	  }
?>
