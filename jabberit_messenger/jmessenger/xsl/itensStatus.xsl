<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="image_src" />

	<xsl:template match="/">
		<dl>
			<xsl:apply-templates select="." mode="option" />
		</dl>
		
	</xsl:template>

	<xsl:template match="option" mode="option">
		<dt style="cursor:pointer; padding:2px 2px 2px 15px; background: url('{$image_src}') no-repeat;" >
			<xsl:value-of select="item" />
		</dt>
	</xsl:template>

</xsl:stylesheet>
