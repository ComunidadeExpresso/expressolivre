<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	<xsl:param name="lang3" />
	
	<xsl:template match="/">
		<table id="tableHiddenJabberit" cellspacing="2" style="width:100%">
			<tr style="width:60%">
				<td align="left" class="row_on"><xsl:value-of select="$lang1" /></td>
				<td align="left" class="row_on"><xsl:value-of select="$lang2" /></td>
				<td align="left" class="row_on"><xsl:value-of select="$lang3" /></td>					
			</tr>
			<xsl:for-each select="return/ou	">
				<tr id="{.}" style="width:40%" class="row_off">
					<td><xsl:value-of select="." /></td>
					<td><xsl:value-of select="@attr" /></td>
					<td><a href="javascript:constructScript.removeOrg('{.}');"><xsl:value-of select="$lang3" /></a></td>
				</tr>
			</xsl:for-each>
		</table>
		
	</xsl:template>

</xsl:stylesheet>
