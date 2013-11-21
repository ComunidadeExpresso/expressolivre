
<html>
	<head>
		<title>{lang_Add_Participants}</title>
		<script src='js/listUsers.js' type='text/javascript'></script>
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
				<tr><td>{lang_Organization}:</td><td>{lang_Sector}:</td></tr>
				<tr><td><select name="select_organization" onchange="formAddUser.change_organization.value='True';formAddUser.submit()">.{combo_organization}.</select></td>
				<td><select name="select_sector" onchange="formAddUser.submit()">.{combo_sector}.</select></td></tr>
				<tr><td colspan="2"><input type="hidden" name="change_organization" value="False"></td></tr>
				<tr><td colspan="2"><center>{lang_to_Search}:&nbsp;<input type="text" name="search_users"></center></td></tr>
				<tr>
				 <td colspan="2">
				  <select name="user_values" multiple style="width:250px" size="18" id="user_list_in">
				  {options}
				  </select>					
				 </td>
			    </tr>
				<tr><td>&nbsp;</td>	</tr>
				<tr>
				 <td>   			
				  <!--center>{lang_to_Search}:&nbsp;<input type="text" name="query" onkeyup="optionFinder(this)"></center-->   			
				 </td>
				</tr>				
				<tr><td>&nbsp;</td>	</tr>
				<tr>
				 <td>
				  <center>
				   <input type="button" class="button" value="Adicionar" onClick="{addUser}">
				   <input type="button" value="Fechar" onClick="javascript:window.close()">
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
