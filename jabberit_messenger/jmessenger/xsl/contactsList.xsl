<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	
	<xsl:param name="idUser"/>
	<xsl:param name="full_name" />
	<xsl:param name="help_expresso" />
	<xsl:param name="path_jabberit" />
	<xsl:param name="zIndex_" />
	
	<xsl:template match="contacts_list">
		
		<fieldset style="margin:2px; border:1px dotted #000000; height: 103px;">
			<div id="{$idUser}__photo" style="position: absolute; left: 5px; top: 5px; width:60px; height:80px; background-image:url('{$path_jabberit}templates/default/images/photo.png');"/>

			<div style="position: absolute; left: 80px; top: 8px;"> <xsl:value-of select="$full_name"/> </div>

			<div style="position: absolute; top: 25px; left: 80px;">
				<button style="width:35px;" alt="Adicionar Contatos" title="Adicionar Contatos" onclick="loadscript.addContact();">
					<img src="{$path_jabberit}templates/default/images/users.png"/>
				</button>
				
				<img style="height:15px; margin-left:10px;cursor:pointer;" alt="Minhas Preferências" title="Minhas Preferências" src="{$path_jabberit}templates/default/images/preferences.png" onclick="loadscript.preferences();"/>
				
				<img style="height:16px; margin-left:10px;cursor:pointer;" alt="Help" title="Help" src="{$path_jabberit}templates/default/images/help.png" onclick="javascript:openWindow(480,510,'{$help_expresso}');" />
				
			</div>
			
			<div id="notification_new_users_jabber" style="display:none; position:absolute; top: 25px; left: 165px;">
				<img style="margin-left:15px;cursor:pointer;" alt="Novos Contatos" title="Novos Contatos" src="{$path_jabberit}templates/default/images/alert_2.png" onclick="loadscript.windowNotificationNewUsers();" />
			</div>

			<div style="position: absolute; left: 80px; top: 53px; cursor: pointer;" onclick="loadscript.setPresence(this);">
				<div id="statusJabberImg" style="background: url('{$path_jabberit}templates/default/images/available.gif'); margin-left: 13px;width:15px; height:15px;"></div>
				<div id="statusJabberText" style="margin-top: -13px; margin-left: 30px;"> Disponível </div>
				<div style="background-image: url('{$path_jabberit}templates/default/images/arrow_down.gif'); margin-top: -13px; margin-left: 0px; width:15px; height:15px;"/>
			</div>
			
			<div id="JabberIMRosterLoadingGif" style="position:absolute; left:136px; top:118px; display:block;">
					<img src='{$path_jabberit}templates/default/images/loading.gif' style="width:20px; height:20px;"/>
					<span style="color:red;"> Carregando...!!</span>
			</div>
			
			<div id="JabberIMStatusMessage" style="position:absolute; left:5px; top:90px; display:block; font:7pt !important;">
				<label style="cursor:pointer;" onclick="loadscript.setMessageStatus(this);">( Digite aqui sua mensagem de Status )</label>
			</div>

		</fieldset>
				
		<div id="JabberIMRoster" style="margin-top:2px; overflow-y:auto; height: 255px; z-index:{$zIndex_};"></div>
		 
	</xsl:template>
	
</xsl:stylesheet>