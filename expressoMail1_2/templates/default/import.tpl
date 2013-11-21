
<center>

<table width="50%" border="0" cellspacing="2" cellpadding="2">

<form method="POST" action="{save_action}" enctype="multipart/form-data">
    <tr bgcolor="{th_bg}">
        <td colspan="2" align="center">{lang_import}</td>
    </tr>
    <tr bgcolor="{tr_color1}">
        <td>{lang_file}:</td>
        <td align="center">
			<input type="file" name="arquivo">
        </td>
    </tr>
    <tr bgcolor="{th_bg}">
    	<td colspan="2" >
    		<table width="100%" border="0" cellspacing="2" cellpadding="2">
    			<tr>
        			<td align="left">
        				<input type="button" name="cancel" value="{lang_cancel}" onClick="javascript:document.location.href = '../expressoMail1_2/index.php'">
        			</td>
        			<td align="right">
        				<input type="submit" name="submit" value="{lang_save}">
        			</td>        			
        		</tr>
        </td>        
    </tr>
</table>
</center>
