<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="rss/channel">
	<div id="divScrollMain_0" style="overflow-y: scroll; overflow-x: hidden; width: 99.3%;">
	<table id="table_box" class="table_box" cellspacing="0" cellpadding="0">
		<tbody id="tbody_box">
	<xsl:for-each select="item">
		<xsl:variable name="itens" select='position()' />
		<tr class="tr_msg_read">
			<xsl:attribute name="onclick">
				news_edit.read_item('<xsl:value-of select="$itens"/>');
			</xsl:attribute>
			<td width="1%" class="td_msg"><input type="checkbox" class="checkbox"/></td>
			<td width="1%" class="td_msg"/>
			<td width="1%" class="td_msg"><img title="Lida" src="templates/default/images/seen.gif"/></td>
			<td width="16%" class="td_msg"><span style="text-decoration: none;"><xsl:value-of select="owner"/></span></td>
			<td width="50%" class="td_msg"><xsl:value-of select="title"/></td>
			<td width="17%" align="center" class="td_msg" title="14/08/2010"><xsl:value-of select="pubDate"/></td>
			<td width="14%" nowrap="true" align="center" class="td_msg"><xsl:value-of select="string-length(description)"/></td>
		</tr>
	</xsl:for-each>
	</tbody>
	</table>
	</div>
</xsl:template>
</xsl:stylesheet>
