<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="checkList" />	
	<xsl:param name="checkIcons" />
	<xsl:param name="check_created" />
	<xsl:param name="check_createdby_id" />
	<xsl:param name="check_comment" />
	<xsl:param name="check_mime_type" />
	<xsl:param name="check_modified" />
	<xsl:param name="check_modifiedby_id" />
	<xsl:param name="check_owner" />
	<xsl:param name="check_size" />
	<xsl:param name="check_version" />
	<xsl:param name="lang_cancel" />
	<xsl:param name="lang_created_by" />			
	<xsl:param name="lang_created" />
	<xsl:param name="lang_comment" />
	<xsl:param name="lang_modified_by" />
	<xsl:param name="lang_modified" />
	<xsl:param name="lang_owner" />
	<xsl:param name="lang_save" />			
	<xsl:param name="lang_size" />
	<xsl:param name="lang_type" />
	<xsl:param name="lang_version" />
	<xsl:param name="lang_view_as_list" />
	<xsl:param name="lang_view_as_icons" />
	<xsl:param name="onclickCancel" />
	<xsl:param name="onclickSave" />
	
	<xsl:template match="view_config">

		<div id="menu_col_pref">
		
			<input name="prefView" value="viewList" type="radio">
				<xsl:if test="$checkList = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_view_as_list"/>
			
			<input name="prefView" value="viewIcons" type="radio">
				<xsl:if test="$checkIcons = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_view_as_icons"/><br/>
			
			<input value="mime_type" type="checkbox">
				<xsl:if test="$check_mime_type = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_type"/><br/>
			
			<input value="size" type="checkbox">
				<xsl:if test="$check_size = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_size"/><br/>
			
			<input value="created" type="checkbox">
				<xsl:if test="$check_created = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_created"/><br/>
			
			<input value="modified" type="checkbox">
				<xsl:if test="$check_modified = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_modified"/><br/>
			
			<input value="owner" type="checkbox">
				<xsl:if test="$check_owner = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_owner"/><br/>
			
			<input value="createdby_id" type="checkbox">
				<xsl:if test="$check_createdby_id = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_created_by"/><br/>
			
			<input value="modifiedby_id" type="checkbox">
				<xsl:if test="$check_modifiedby_id = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_modified_by"/><br/>
			
			<input value="comment" type="checkbox">
				<xsl:if test="$check_comment = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_comment"/><br/>
			
			<input value="version" type="checkbox">
				<xsl:if test="$check_version = 1">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			<xsl:value-of select="$lang_version"/><br/>
			 
		</div>
		
		<div style="margin-top:15px;">
			<input style="margin-left:5px;" value="{$lang_save}" onclick="{$onclickSave}" type="button" />
			<input style="margin-left:5px;" value="{$lang_cancel}" onclick="{$onclickCancel}" type="button" />
		</div>
		
	</xsl:template>
	
</xsl:stylesheet>