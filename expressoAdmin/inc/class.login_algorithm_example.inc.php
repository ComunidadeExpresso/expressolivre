<?php
	/********************************************************************************************\
	* Expresso Administraηγo																	*
	* by Diσgenes Ribiro Duarte (diogenes.duarte@gmail.com, diogenes.duarte@prodeb.ba.gov.br)  	*
	* ------------------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it under the terms	*
	*  of the GNU General Public License as published by the Free Software Foundation;			*
	*   either version 2 of the License, or (at your option) any later version.					*
	\********************************************************************************************/
	
	class login_algorithm_example implements login{
		var $xml_nomes;
		/*var $nomes_comuns;
		var $preposicoes;
		var $sobrenomes_especiais;*/
		
		function login_algorithm_example() {
			$this->xml_nomes = new DOMDocument();		
			$this->xml_nomes->load("inc/names.xml");// Carrega o xml antes de iniciar os nomes.
		}
		
		function generate_login($primeiro_nome,$segundo_nome,$conexao_ldap) {
			
			$primeiro_nome_exp = explode("#",$this->formata_frase(strtolower($primeiro_nome)));
			$segundo_nome_exp = explode("#",$this->formata_frase(strtolower($segundo_nome)));
			
			$login = "";
			
			//Tratar primeiro nome...
			$login.=$primeiro_nome_exp[0]; //A primeira parte do primeiro nome ι sempre inserida

			if(count($primeiro_nome_exp)>1) { //Se houver mais partes...
				if ( ($this->is_nome_comum($primeiro_nome_exp[0])) 
					&& (!$this->is_preposicao($primeiro_nome_exp[1]))) { //Se a primeira parte do nome for comum e a segunda NΓO for uma preposiηγo...
					$login.=$primeiro_nome_exp[1];
				}
			}
			
			//"." separa o primeiro do segundo nome
			$login.=".";
			
			//Tratar segundo nome...
			if( (!$this->is_sobrenome_especial($segundo_nome_exp[count($segundo_nome_exp)-1]))
					|| (count($segundo_nome_exp)==1) )
				$login.=$segundo_nome_exp[count($segundo_nome_exp)-1];
			else {
				$login.=$segundo_nome_exp[count($segundo_nome_exp)-2].
						$segundo_nome_exp[count($segundo_nome_exp)-1];
			}
			return $login;
		}
		
		private function is_nome_comum($nome) {
			$nomes = $this->xml_nomes->getElementsByTagName("nomes");
			foreach($nomes as $node) {
				if($node->getAttribute("tipo") == "nome") {
					foreach($node->getElementsByTagName("nome") as $subnode) {
						if($subnode->nodeValue == $nome)
							return true;
					}
				}
			}
			return false;
		}
		
		private function is_preposicao($preposicao) {
			$nomes = $this->xml_nomes->getElementsByTagName("nomes");
			foreach($nomes as $node) {
				if($node->getAttribute("tipo") == "preposicao") {
					foreach($node->getElementsByTagName("nome") as $subnode) {
						if($subnode->nodeValue == $preposicao)
							return true;
					}
				}
			}
			return false;
		}

		private function is_sobrenome_especial($sobrenome) {
			$nomes = $this->xml_nomes->getElementsByTagName("nomes");
			foreach($nomes as $node) {
				if($node->getAttribute("tipo") == "sobrenome") {
					foreach($node->getElementsByTagName("nome") as $subnode) {
						if($subnode->nodeValue == $sobrenome)
							return true;
					}
				}
			}
			return false;
		}
				
		//Retira acentos e caracteres especiais, e substitui espaηo em branco pelo caracter #.
		private function formata_frase($frase) {		
			$frase = preg_replace('/[^a-zA-Z0-9#.]/', '', 
			  strtr($frase, "αΰγβικνστυϊόηΑΐΓΒΙΚΝΣΤΥΪάΗ ", 
			  "aaaaeeiooouucAAAAEEIOOOUUC#"));
			return $frase;
		}
			
	}
?>