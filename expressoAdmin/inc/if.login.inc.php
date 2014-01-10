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
	Todas as classes para gera��o de login dever�o implementar essa interface.
	O nome da classe dever� possuir o mesmo nome do arquivo retirando as palavras class, inc e php
	
	Ex: o arquivo class.login_algoritmo_prodeb.inc.php dever� possuir a classe login_algoritmo_prodeb.
	
	A nomenclatura do arquivo dever� respeitar o padr�o do expresso para arquivos de include, utilizando
	preferencialmente letras min�sculas e sem acentos.
*/
interface login {
	//� necess�rio para gerar os logins o primeiro nome, o segundo e uma conex�o para o ldap da m�quina.
	public function generate_login($first_name,$last_name,$ldap_conn);
}


?>