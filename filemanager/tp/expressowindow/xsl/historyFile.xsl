<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="lang_created" />
	<xsl:param name="lang_history" />
	<xsl:param name="lang_operation" />
	<xsl:param name="lang_version" />
	<xsl:param name="lang_who" />
	<xsl:param name="height" />
	<xsl:param name="path_filemanager" />
	<xsl:param name="width" />
	
	<xsl:template match="file">
		
		<div style="font-size:10pt; margin: 10px 6px 10px 6px; border-bottom:1px solid #000;">
			<img src="{$path_filemanager}templates/default/images/button_info.png" style="margin-right: 5px;"/>
			<label style="font-size:12px;font-weight:bold;"> <xsl:value-of select="$lang_history" /></label>
		</div>
		
		<div style='margin:6px; width:{($width)-20}px ; height:{($height)-80}px; overflow-y: auto; text-align: left;'>
			<xsl:for-each select="info">
				<div style="margin-bottom:10px;">
					<label style="font-weight:bold; margin-right:5px"><xsl:value-of select="$lang_created" />.:</label>
					<xsl:value-of select="created" />
					<br/>
					<label style="font-weight:bold; margin-right:5px"><xsl:value-of select="$lang_version" />.:</label>			
					<xsl:value-of select="version" />
					<br/>
					<label style="font-weight:bold; margin-right:5px"><xsl:value-of select="$lang_who" />.:</label>
					<xsl:value-of select="who" />
					<br/>
					<label style="font-weight:bold; margin-right:5px"><xsl:value-of select="$lang_operation" />.: </label>
					<xsl:value-of select="operation" />
				</div>		

			</xsl:for-each>
		</div>

	</xsl:template>
	
</xsl:stylesheet>