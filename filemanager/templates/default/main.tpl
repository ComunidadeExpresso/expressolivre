<!-- BEGIN filemanager_header -->
{css}
{path}
<table id="main_table" width="100%" cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td id="folderscol" width="162px" height="100%" valign="top">
	<table id="folders_tbl" width="162px" border="0" cellspacing="0" cellpadding="0" border="0">
	<tbody>
		<tr>
			<td class='content-menu'>
			<div id="search_div" align="left" style="white-space:nowrap">
			<input type="text" id="em_message_search" size="16" maxlength="22" />
			<img style="vertical-align: bottom;" onclick="searchFile();" src="./filemanager/templates/default/images/search.gif">
			</div>
			<table border="0" cellspacing="0" cellpadding="0" border="0" style="width:100%">
			<tbody>
			<tr height="24">
			<td class='content-menu-td'>
			{new_button}
			</td>
		</tr>

		<tr height="24">
			<td class='content-menu-td'>
			{refresh_button}
			</td>
		</tr>

		<tr height="24">
			<td>
			{tools_button}
			</td>
		</tr>
	</tbody>
	</table>
		</td>
		</tr>
    		<tr>
			<td height="2px"></td>
		</tr>
		<tr>
                        <td class="image-menu" valign="top" style="padding:0px">
                                <div id="content_folders" class="menu-degrade" style="height:100%;width:170px;overflow:auto"></div>
                        </td>
                </tr>
	</tbody>
	</table>
<td>
<div id="formfm" name="formfm">
<div id="fmFileWindow">
<table cellspacing="0" cellpadding="2" width="100%">
<tbody>
<!-- END filemanager_header -->
</td>
</tr>
</tbody>
</table>
<!-- BEGIN filemanager_footer -->
</tbody></table>
</div>
<div id="fmMenu">
</div>
<div style="visibility: hidden" id="allMessages">{messages}</div>
</div>
{preferences}
{sec_key}
{script}
<!-- END filemanager_footer -->
