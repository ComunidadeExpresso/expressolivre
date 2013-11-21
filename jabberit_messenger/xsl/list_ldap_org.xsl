<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:template match="organizations">
		<xsl:for-each select="ou">
			<xsl:sort select="." case-order="upper-first" />
			<span><xsl:value-of select="." /></span><br/>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>
