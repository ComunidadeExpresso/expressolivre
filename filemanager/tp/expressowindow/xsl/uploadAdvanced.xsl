<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="iframe_height"/>
	<xsl:param name="iframe_src"/>
	<xsl:param name="iframe_width"/>
	
	<xsl:template match="upload_files_advanced">
	
		<iframe frameborder="0" width="{$iframe_width}" height="{$iframe_height}" src='{$iframe_src}'/>
		
	</xsl:template>
	
</xsl:stylesheet>	