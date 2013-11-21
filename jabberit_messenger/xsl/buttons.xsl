<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="lang1" />
	<xsl:param name="lang2" />
	<xsl:param name="onclickClose" />
	<xsl:param name="onclickSubmit" />
	
	<xsl:template match="buttons_main">
		<table cellspacing="2">
			<tr>
				<td>
					<table cellspacing="0" class="x-btn  x-btn-noicon" style="width: 75px;">
						<tbody class="x-btn-small x-btn-icon-small-left">
						<tr>
							<td class="x-btn-tl"><i> </i></td>
							<td class="x-btn-tc"></td>
							<td class="x-btn-tr"><i> </i></td>
						</tr>
						<tr>
							<td class="x-btn-ml"><i> </i></td>
							<td class="x-btn-mc" onclick="{$onclickSubmit};">
								<em unselectable="on">
									<button type="button" class="x-btn-text"><xsl:value-of select="$lang1"/></button>
								</em>
							</td>
							<td class="x-btn-mr"><i> </i></td>
						</tr>
						<tr>
							<td class="x-btn-bl"><i> </i></td>
							<td class="x-btn-bc"></td>
							<td class="x-btn-br"><i> </i></td>
						</tr>
						</tbody>
					</table>
				</td>
				<td>
					<table cellspacing="0" class="x-btn  x-btn-noicon" style="width: 75px;">
						<tbody class="x-btn-small x-btn-icon-small-left">
							<tr>
								<td class="x-btn-tl"><i> </i></td>
								<td class="x-btn-tc"></td>
								<td class="x-btn-tr"><i> </i></td>
							</tr>
							<tr>
								<td class="x-btn-ml"><i> </i></td>
								<td class="x-btn-mc"  onclick="{$onclickClose};">
									<em unselectable="on">
										<button type="button" class="x-btn-text"><xsl:value-of select="$lang2"/></button>
									</em>
								</td>
								<td class="x-btn-mr"><i> </i></td>
							</tr>
							<tr>
								<td class="x-btn-bl"><i> </i></td>
								<td class="x-btn-bc"></td>
								<td class="x-btn-br"><i> </i></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>