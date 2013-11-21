<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>

	<xsl:param name="path_jabberit" />

	<xsl:template match="listRooms">
		
		<div style="margin:5px; height:200px; overflow-y: auto">
			
			<xsl:for-each select="room">
				<xsl:sort select="@nameRoom"/>
				<div onclick="loadscript.joinRoom('{jidRoom}','{@nameRoom}');" style="cursor:pointer;padding :5px 0px 5px 45px; background:url({$path_jabberit}templates/default/images/conference.png) no-repeat center left; border-bottom:1px dashed #cecece; margin:6px 1px;">
					Nome da Sala : <xsl:value-of select="@nameRoom"/> <br/>
					<xsl:choose>
						<xsl:when test="description != ''">
							Descrição : <xsl:value-of select="description"/> <br/>
						</xsl:when>
						<xsl:otherwise>
							Descrição : &lt; Sem Descrição &gt; <br/>
						</xsl:otherwise>
					</xsl:choose>					
					Ocupantes : <xsl:value-of select="occupants"/> <br/>
					<xsl:choose>
						<xsl:when test="password = 'true'">
							Password : <span style="color:red; font-weight: bold;"><blink>Com Senha</blink></span><br/>
						</xsl:when>
						<xsl:otherwise>
							Password : Sem Senha <br/>
						</xsl:otherwise>
					</xsl:choose>					
				</div>
			</xsl:for-each>
			
		</div>
		
		<div id="buttons_addChatRoom" style="float:left; margin:10px 0px 0px 10px;" />
		
	</xsl:template>


</xsl:stylesheet>