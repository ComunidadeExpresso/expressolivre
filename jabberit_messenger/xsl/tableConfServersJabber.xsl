<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	<xsl:param name="lang3" />
	<xsl:param name="lang4" />
	
	<xsl:template match="/">
		<table id="tableConfServersJabber" cellspacing="2" style="width:100%">
			<tr style="width:60%">
				<td align="left" class="row_on" style="width:40%"><xsl:value-of select="$lang1" /></td>
				<td align="left" class="row_on" style="width:40% !important"><xsl:value-of select="$lang2" /></td>
				<td align="left" class="row_on" style="width:10% !important"><xsl:value-of select="$lang4" /></td>					
				<td align="left" class="row_on" style="width:10% !important"><xsl:value-of select="$lang3" /></td>				
			</tr>
			<xsl:for-each select="return/confServer">
				<tr id="{.}" style="width:40%" class="row_off">
					<td><xsl:value-of select="@ou" /></td>
					<td><xsl:value-of select="@serverName" /></td>
					<td><a href="javascript:constructScript.editHostsJ('{.}');"><xsl:value-of select="$lang4" /></a></td>
					<td><a href="javascript:constructScript.removeHostsJ('{.}');"><xsl:value-of select="$lang3" /></a></td>
				</tr>
			</xsl:for-each>
		</table>
		
	</xsl:template>

</xsl:stylesheet>