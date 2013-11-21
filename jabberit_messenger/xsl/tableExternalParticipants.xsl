<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	
	<xsl:template match="/">
		<table id="tableExternalParticipantsJabberit" cellspacing="2" style="width:100%">
			<tr>
				<td align="left" class="row_on"><xsl:value-of select="$lang1" /></td>
				<td align="left" class="row_on" style="width:30% !important"><xsl:value-of select="$lang2" /></td>
			</tr>
			<xsl:for-each select="return/ou	">
				<tr id="{.}" style="width:40%" class="row_off">
					<td><xsl:value-of select="." /></td>
					<td><a href="javascript:constructScript.removePartExternal('{.}');"><xsl:value-of select="$lang2" /></a></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>

</xsl:stylesheet>
