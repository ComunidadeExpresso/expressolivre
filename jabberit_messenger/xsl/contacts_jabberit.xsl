<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="path" />
	<xsl:param name="width" />

	<xsl:template match="contacts_jabberit">
	
		<iframe id="iframe_applet_jabberit" style="position:fixed;" src="{$path}client.php" frameborder="0" width="{$width}" height="400px"></iframe>
		
	</xsl:template>

</xsl:stylesheet>
