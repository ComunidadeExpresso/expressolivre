<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="button_1" />
	<xsl:param name="button_2" />
	<xsl:param name="Note_This_sharing_will_take_action_on_all_of_your_folders_and_messages" />
	<xsl:param name="Organization" />
	<xsl:param name="Search_user" />
	<xsl:param name="Users" />
	<xsl:param name="Your_mailbox_is_shared_with" />
	<xsl:param name="Access_right" />
	<xsl:param name="Read"/>
	<xsl:param name="Exclusion"/>
	<xsl:param name="Write"/>
	<xsl:param name="Send"/>
	<xsl:param name="Save"/>
	
	<xsl:param name="hlp_msg_read_acl"/>
	<xsl:param name="hlp_msg_delmov_acl"/>
	<xsl:param name="hlp_msg_addcreate_acl"/>
	<xsl:param name="hlp_msg_sendlike_acl"/>
	<xsl:param name="hlp_msg_savelike_acl"/>
		
	<xsl:template match="sharedFolders">
		
		<div style="margin:5px; height: 390px !important;">
			<div style="margin-bottom:2px;">
				<div style="margin-top:5px;">
					<label style="margin-right:5px;">
						<xsl:value-of select="$Organization" />
					</label>
					
					<select id="em_combo_org" style="margin-right: 20px;" />
					
					<label style="color:red;">
						<xsl:value-of select="$Note_This_sharing_will_take_action_on_all_of_your_folders_and_messages" />
					</label>
					
				</div>
				
				<div style="margin-top:10px;">
					<label style="margin-right:5px;"><xsl:value-of select="$Search_user" /></label><br/> 
					<input id="em_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:sharemailbox.optionFinderTimeout(this, event);" />
					<span style="margin-left:2px; color:red;" id="em_span_searching" />
				</div>
			</div>
		
			<br clear="all"/>

			<div style="position:absolute; float:left;">
				<label style="font-size:8pt;"><xsl:value-of select="$Users"/></label><br/>
				<select id="em_select_available_users" size="13" style="width:250px; height:190px;" multiple="true"/>
			</div>
			
			<div style="position:absolute; left: 267px">
				<div style="margin-top:60px;">
					<input type="button" onclick="javascript:sharemailbox.add_user();" value="{$button_1}" class="button ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false" />
				</div>
				
				<div style="margin-top:10px;">
					<input type="button" onclick="javascript:sharemailbox.remove_user();" value="{$button_2}" class="button ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false" />
				</div>
			</div>
			
			<div style="position:absolute; left:330px;">
				<label style="font-size:8pt;"><xsl:value-of select="$Your_mailbox_is_shared_with"/></label>
				<br/>
				<select id="em_select_sharefolders_users" onclick="sharemailbox.getaclfromuser(this.value);" size="13" style="width:250px; height:190px;"/>
			</div>
			
		
			<div style="position:absolute; left:600px;">
				<label style="font-size:8pt;"><xsl:value-of select="$Access_right"/></label>
				<br/>
				<div style="border:1px solid #cecece;font-size: 8pt;">
					<div>
						<img title="{$hlp_msg_read_acl}" src="./templates/default/images/ajuda.jpg" /><label>-</label>
						<input id="em_input_readAcl" onClick="return sharemailbox.setaclfromuser();" type="checkbox" />
						<label><xsl:value-of select="$Read"/></label>
					</div>
					<div>
						<img title="{$hlp_msg_delmov_acl}" src="./templates/default/images/ajuda.jpg" /><label>-</label>
						<input id="em_input_deleteAcl" onClick="return sharemailbox.setaclfromuser();" type="checkbox"/>
						<label><xsl:value-of select="$Exclusion"/></label>
					</div>
					<div>
						<img title="{$hlp_msg_addcreate_acl}" src="./templates/default/images/ajuda.jpg" /><label>-</label>
						<input id="em_input_writeAcl" onClick="return sharemailbox.setaclfromuser();" type="checkbox" />
						<label><xsl:value-of select="$Write"/></label>
					</div>
					<div>
						<img title="{$hlp_msg_sendlike_acl}" src="./templates/default/images/ajuda.jpg" /><label>-</label>
						<input id="em_input_sendAcl" onClick="return sharemailbox.setaclfromuser();" type="checkbox"/>
						<label><xsl:value-of select="$Send"/></label>
					</div>
				</div>
			</div>
		</div>
		
	</xsl:template>
</xsl:stylesheet>