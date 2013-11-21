<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="username" />	
	<xsl:param name="password" />
	
	<xsl:template match="login_page">
		
		<center>
			<div id="trophyimlogin">
				<form name="cred">
					<table style="border:0px">
						<tr>
							<td align="right">Usuário:</td>
							<td><input type="text" id="trophyimjid" value="{$username}" /></td>
						</tr>
						<tr>
							<td align="right">Password:</td>
							<td><input type="password" id="trophyimpass" value="{$password}" /></td>
						</tr>
					</table>
					<br/>
					<table>
						<tr>
							<td colspan="2" align="center">
								<input type="button" id="trophyimconnect" value="connect" onclick="TrophyIM.login()"/>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</center>
		 
	</xsl:template>
	
</xsl:stylesheet>