<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
/*
	Todas as classes para geraзгo de login deverгo implementar essa interface.
	O nome da classe deverб possuir o mesmo nome do arquivo retirando as palavras class, inc e php
	
	Ex: o arquivo class.login_algoritmo_prodeb.inc.php deverб possuir a classe login_algoritmo_prodeb.
	
	A nomenclatura do arquivo deverб respeitar o padrгo do expresso para arquivos de include, utilizando
	preferencialmente letras minъsculas e sem acentos.
*/
interface login {
	//Й necessбrio para gerar os logins o primeiro nome, o segundo e uma conexгo para o ldap da mбquina.
	public function generate_login($first_name,$last_name,$ldap_conn);
}


?>