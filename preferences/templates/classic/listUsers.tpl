<html>
	<head>
		<title>{lang_Add_Participants}</title>
	</head>
	<body>
		<form name="formAddUser" method="POST">	
		 <center>	
		  <table cellspacing="3" cellpadding="3">														
		   <tr>
			 <td>
			  <div id="divAppboxHeader">{lang_Add_Participants}</div>
			  <div id="divAppbox">
			   <table border=0>
				<tr><td>{lang_Organization}:</td></tr>
				<tr><td><select name="select_organization" onchange="formAddUser.change_organization.value='True';formAddUser.submit()">.{combo_organization}.</select></td></tr>
				<tr><td>{lang_Sector}:</td></tr>
				<tr><td><select name="select_sector" onchange="formAddUser.submit()">.{combo_sector}.</select></td></tr>
				<input type="hidden" name="change_organization" value="False">
				<tr>
				 <td>
				  <select name="user_values" multiple style="width:250px" size="18" id="user_list_in">
				  {options}
				  </select>					
				 </td>
			    </tr>
				<tr><td>&nbsp;</td>	</tr>
				<tr>
				 <td>   			
				  <center>{lang_to_Search}:&nbsp;<input type="text" name="query" onkeyup="optionFinder(this)"></center>   			
				 </td>
				</tr>				
				<tr><td>&nbsp;</td>	</tr>
				<tr>
				 <td>
				  <center>
				   <input type="button" class="button" value="Adicionar e Retornar" onClick="javascript:adicionaLista()">
				   <input type="button" value="Cancelar" onClick="javascript:window.close()">
				  </center>
				 </td>
				</tr>		
			   </table>
			  </div>
			 </td>	
			</tr>
		   </table>
		  </center>				
		 </form>
	</body>
</html>
<script language="Javascript1.3">
//Variaveis Locais 
 var select = document.getElementById('user_list_in');
 var users = new Array();
 //Inicializacao			
 for(var i = 0; i < select.options.length; i++) {				
	 option = new Option(select.options[i].text,select.options[i].value);	 
	 users[i] = option;	 				
 }					
</script>