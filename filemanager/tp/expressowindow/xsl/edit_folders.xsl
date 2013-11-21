<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang_new_folder" />
	<xsl:param name="lang_remove_folder" />
	<xsl:param name="onclick_new_folder"/>
	<xsl:param name="onclick_remove_folder"/>
	<xsl:param name="path_filemanager" />

	<xsl:template match="root">
	
		<div style="margin:5px;">
			
			<label><img style="width:18px; height:18px;" src="{$path_filemanager}templates/default/images/button_createdir.png" /> Editar Pastas - Criar e Remover </label>
			<br/>
			<select id="folders_box" size="10" style="width: 260px; height:100px;">
			
				<xsl:for-each select="folders/name">
					<xsl:sort select="."/>				
					<option value="{@value}"><xsl:value-of select="."/></option>
				</xsl:for-each>
				
			</select>
		</div>	
	
		<div style="margin: 5px;">
			<input type='button' onclick='{$onclick_new_folder}' value='{$lang_new_folder}' style="margin:3px" />
			<input type='button' onclick='{$onclick_remove_folder}' value='{$lang_remove_folder}' />
		</div>
		
	</xsl:template>

</xsl:stylesheet>