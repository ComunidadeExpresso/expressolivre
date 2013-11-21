<!--

	eGroupWare - Contact Center - Quick Add Plugin Interface Template
	Copyright (C) 2004 - Raphael Derosso Pereira
	(raphaelpereira@users.sourceforge.net)

	This file is licensed under the terms of th GNU GPL
	version 2 or above
	
-->
{cc_api}

<div align="center"  id="ccAddGroupContent" style=" visibility: hidden;">
	<input id="ccAGnFields" type="hidden" value="{ccAGnFields}" />
	<input id="ccAGTitle" type="hidden" value="{ccAGTitle}" />
	<input id="ccAGWinHeightIE" type="hidden" value="{ccAGWinHeightIE}" />
	<input id="ccAGWinHeightMO" type="hidden" value="{ccAGWinHeightMO}" />
	<input id="group_id" type="hidden">
	
	<table border="0"  cellpadding="0" cellspacing="0" width="60%">
		<tr height="30">
			<td  width="17%" valign="center" align="right" nowrap>{txt_name}:</td>
			<td  colspan="4" valign="center" align="left">&nbsp;&nbsp;{title}</td>
		</tr>
			
	
        <tr height="30">
                <td   valign="center" align="right" nowrap><div class="fonte" style="margin: 0 0 0 5px;">Fonte:</div></td>
                <td  valign="center" align="left" >
                    &nbsp;<select id="ccAGSourceSelect" name="ccAGSourceSelect" onchange="ccAddGroup.setCatalog()">{ccAGSourceSelectContent}</select>
                </td>
                <td  valign="center" align="right">
                    <input id="ccAGSearchTerm" name="ccAGSearchTerm" type="text" size="24" maxlength="50" />
                </td>
                <td id="ccAGSearchButton" valign="center" align="left">
                    <input id="ccAGSearch" title="{ccAGSearch}" type="button" onclick="ccAddGroup.search();" value="{ccAGSearch}" />
                </td>
            </tr>
        </table>

        <br />
        <br />
	<table border="0" cellpadding="0" cellspacing="0" width="95%">
		<tr>
			<td  align="right">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr height="30" class="th">
						<td  align="center">{txt_personal_contacts}</td>					
					</tr>
					<tr height="30" class="th">
						<td  align="right"><span id='span_contact_list'/></td>
					</tr>
				</table>
			</td>
			<td id="buttons" width="140" align="center">
				<button type="button" onClick="ccAddGroup.addUser()" ><img src="../phpgwapi/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{txt_add}</button>
				<br><br>
				<button type="button" onClick="ccAddGroup.remUser()"><img src="../phpgwapi/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{txt_rem}</button>
			</td>
			<td  align="left">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr height="30" class="th">
						<td  align="center">{txt_contacts_in_list}</td>					
					</tr>
					<tr height="30" class="th">
						<td  align="left"><select  id="contact_in_list" multiple name="contact_in_list[]" style="width:280px" size="10"></select></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center" colspan="3">
					<br>						
					<input title="{ccAGSave}" type="button" onclick="ccAddGroup.send();" value="{ccAGSave}" />
					<input title="{ccAGClear}" type="button" onclick="ccAddGroup.clear();" value="{ccAGClear}" />
					<input title="{ccAGCancel}" type="button" onclick="ccAddGroup.clear(); ccAddGroup.window.close();" value="{ccAGCancel}"/>						
			</td>
		</tr>
	</table>					
</div>
<script type="text/javascript" src="{ccAGFile}"></script>
