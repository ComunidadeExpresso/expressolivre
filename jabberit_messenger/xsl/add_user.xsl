<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	<xsl:param name="lang3" />
	<xsl:param name="lang4" />
	<xsl:param name="lang5" />
	<xsl:param name="lang6" />
	<xsl:param name="lang7" />
	
	<xsl:template match="userinfo">
		
		<div style="margin:2px">
			<div class="search_user">
				<span style="width:130px;"><xsl:value-of select="$lang1" /> .: </span>
				<input type="text" size="30" onclick="this.select();" onkeypress="javascript:loadscript.keyPress(event, this);" style="margin: 0 10 0 0px;"/>
				<input type="image" src='../jabberit_messenger/templates/default/images/users.png' value="{$lang2}" onclick="javascript:loadscript.search(this);" />
			</div>
			<br/>
			<div class="add_organization_members">
				<span style="margin: 5px; width:auto;"><xsl:value-of select="$lang3"/> .: </span>
				<br style="clear:both"/>
				<div id="im_ldap_user"></div>
			</div>
		</div>

		<span id="im_status_add" style="color:#f00;"></span>
		<span id="__span_load_im" style="background-color:#cc4444;color:white;display:none;position:absolute;right:5px;top:26px">Carregando .....</span>
		<input id="im_jidUser" type="hidden" />
		<input id="im_jid" type="hidden" />
		<input id="im_uid" type="hidden" />

	</xsl:template>
	
	<xsl:template match="adduser">
		
		<div class="add_member_info">
			<span><xsl:value-of select="$lang4"/> : </span>
			<input id="im_name" type="text" size="39" maxlength="50" onclick="this.select();"/>
			<br/><br style="line-height:4px"/>
			<span><xsl:value-of select="$lang5"/> : </span>
			<input id="im_group" type="text" size="40" maxlength="50" selectboxoptions="" onclick="this.select();"/>
		</div>
		
		<div id="buttons_adduser" style="padding-top:30px; padding-bottom: 30px; margin-left: 83px;"/>
	
	</xsl:template>
	
</xsl:stylesheet>
