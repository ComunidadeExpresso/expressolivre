<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name = "jidFrom" />
	<xsl:param name = "jidTo" />
	<xsl:param name = "name_contact" />
	<xsl:param name = "selectBoxOptions" />
	
	<xsl:template match="new_user">
		
		<div style="margin:5px;">
			<label>Nome do Grupo .: </label>
			<input id="name_group_new_user_jabberit" type="text" size="30" maxlength="50" selectboxoptions="{$selectBoxOptions}" onclick="this.select();" />
			<br/>
			<br/>
			<br/>
			<label>Nome do Usuário .: </label>
			<input type="text" id="name_new_user_jabberit" size="30" maxlength="50" value="{$name_contact}" />
		</div>
		<br/>
		<br/>
		<div id="buttons_newuser" style="margin-left:20px;" />		
		<input type="hidden" id="jidFrom_new_user_jabberit" value="{$jidFrom}" />
		<input type="hidden" id="jidTo_new_user_jabberit" value="{$jidTo}" />
		
	</xsl:template>
	
</xsl:stylesheet>