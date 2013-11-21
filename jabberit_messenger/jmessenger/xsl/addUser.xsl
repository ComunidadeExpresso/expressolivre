<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang_group" />
	<xsl:param name="lang_load" />
	<xsl:param name="lang_name_contact" />
	<xsl:param name="lang_result" />
	<xsl:param name="path" />
	<xsl:param name="group" />
	<xsl:param name="jid" />
	<xsl:param name="name" />
	<xsl:param name="selectBoxOptions" />

	<xsl:template match="userinfo">

		<div style="margin:2px">
			<div style="margin:5px 0px 5px 5px;">
				<span><xsl:value-of select="$lang_name_contact" /> .: </span> 
				<input id="search_user_jabber" type="text" size="30" onclick="this.select();" onkeypress="loadscript.keyPressSearch(event, this);" />
				<button style="position:relative; top:5px; left:10px;" onclick="loadscript.searchUser()"><img src="{$path}templates/default/images/users.png"/></button>
			</div>

			<div class="add_organization_members" style="margin:7px 0px 5px 5px;">
				<span style="width:auto;"><xsl:value-of select="$lang_result"/> .: </span>
				<br style="clear:both"/>
				<div id="list_users_ldap_im"></div>
			</div>
		</div>

		<span id="im_status_add" style="color:#f00;"></span>
		<span id="span_searching_im" style="background-color:#cc4444;color:white;display:none;position:absolute;right:5px;top:26px;padding:2px;">
			<xsl:value-of select="$lang_load" /> ...
		</span>
		
	</xsl:template>
	
	<xsl:template match="adduser">

		<div id="photo_user_ldap_jabber" style="margin: 5px; position:relative ;float:left;" />
		<div class="add_member_info" style="margin-bottom:40px !important;">
			<form>
				<span> <xsl:value-of select="$lang_name_contact"/> : </span>
				<input id="user_name_jabberIM" type="text" size="25" maxlength="50" value="{$name}" onclick="this.select();" />
				<br/><br/>
				<span><xsl:value-of select="$lang_group"/> : </span>
				<input id="user_group_jabberIM" type="text" size="25" maxlength="50" value="{$group}" selectboxoptions="{$selectBoxOptions}" onclick="this.select();" />
				<input id="user_jid_jabberIM" type="hidden" value="{$jid}" />
			</form>
		</div>
		
		<div id="buttons_adduser" style="float:left;margin-left: 50px;" />
		
	</xsl:template>
	
</xsl:stylesheet>