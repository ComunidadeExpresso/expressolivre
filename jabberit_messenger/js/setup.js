(function()
{
	// Envio para o PHP
	var _conn   = new JITConnector('jabberit_messenger/');
	var Xtools  = new xtools('jabberit_messenger/');	

	function addParticipantsExternals(pDocument)
	{
		var form = pDocument.forms[0];
		var organization = "";
		
		for(var i = 0; i < form.elements.length; i++)
		{
			if( form.elements[i].type == 'text' )
			{
				var nameElement = form.elements[i].name;
					nameElement = nameElement.substring(nameElement.indexOf('_') +1);
				
				if( form.elements[i].value != "" && ltrim(form.elements[i].value) != "" )
				{
						document.getElementById('label_' + nameElement).style.color = "black";
						organization = form.elements[i].value;
						form.elements[i].value = '';
				}
				else
				{
						document.getElementById('label_' + nameElement).style.color = "red";
						alert(jabberitGetLang('Define Organization!'));
						return false;
				}
			}
		}
		
		_conn.go("$this.db_im.setOrganization",
				function(data)
				{
					if ( data )
					{
						var _params = {
							'lang1': jabberitGetLang('Organization'),
							'lang2': jabberitGetLang('Delete')
						};
		
						var Table = document.getElementById('tableExternalParticipantsJabberit');
						Table.parentNode.innerHTML = Xtools.parse( data, 'tableExternalParticipants.xsl', _params);
					}
				},
				"organization=" + organization);
	}
	
	function editHostsJabber()
	{
		if( arguments.length > 0 )
		{
			_conn.go("$this.db_im.editHostJabber",
					 function(data)
					 {
							var fields = [
											['org','organizationLdapJabberit'], 
											['jabberName','hostNameJabberit'],
											['serverLdap','serverLdapJabberit'], 
											['contextLdap','contextLdapJabberit'],
											['user','userLdapJabberit'], 
											['password','passwordLdapJabberit']										
										 ];						 	

							data = data.split(';');

							for( var i = 0 ; i < data.length ; i++ )
							{
								var values = data[i].split(':');

								for( var j in fields )
									if( values[0] == fields[j][0] )
										document.getElementById(fields[j][1]).value = values[1];
							}
							
					 },"item="+arguments[0]);
		}
	}
	
	function getInputs(pDocument)
	{
		var form = pDocument.forms[0];
		var values = "";
		var field_blank = false;
		var anonymousBind;
		var selectedAnonymous = document.getElementById("JETTI_anonymous_bind_jabberit");
		
		// Anonymous Bind Element Select
		for( var i = 0; i < selectedAnonymous.length; i++ )
			if ( selectedAnonymous.options[i].selected == true)
				anonymousBind = eval(selectedAnonymous.options[i].value);
		
		for( var i = 0 ; i < form.elements.length; i++ )	
		{
			switch(form.elements[i].type)
			{
				case "text" :
					
						if( form.elements[i].id.toUpperCase() != "JETTI_PORT_2_JABBERIT" )
						{
							if( anonymousBind )
							{
								if( form.elements[i].id.toUpperCase() != "JETTI_USER_LDAP_JABBERIT" )
								{
									if( form.elements[i].value != "" )
									{
										values += form.elements[i].id.toUpperCase() + ";" + escape(form.elements[i].value) + "\n";
										document.getElementById(form.elements[i].id + "__label").style.color = "black";
									}
									else
									{
										field_blank = true;
										document.getElementById(form.elements[i].id + "__label").style.color = "red";
									}
								}
								else
								{
									form.elements[i].value = "";
									document.getElementById(form.elements[i].id + "__label").style.color = "black";
								}
							}
							else
							{
								if ( form.elements[i].value != "" )
								{
									values += form.elements[i].id.toUpperCase() + ";" + escape(form.elements[i].value) + "\n";
									document.getElementById(form.elements[i].id + "__label").style.color = "black";
								}
								else
								{
									field_blank = true;
									document.getElementById(form.elements[i].id + "__label").style.color = "red";
								}
	
							}
						}
						
						break;

				case "password"	:
						
						if( anonymousBind )
						{
							form.elements[i].value = "";
							document.getElementById(form.elements[i].id + "__label").style.color = "black";
						}
						else
						{
							if( form.elements[i].value != "" )
							{
								values += form.elements[i].id.toUpperCase() + ";" + escape(form.elements[i].value) + "\n";
								document.getElementById(form.elements[i].id + "__label").style.color = "black";
							}
							else
							{
								field_blank = true;
								document.getElementById(form.elements[i].id + "__label").style.color = "red";
							}
						}
						
						break;
			}
		}

		if( !field_blank )
		{
			values = values.substring(0,(values.length - 1 ));
			_conn.go("$this.fileDefine.ldapInternal", function(){ form.submit.click(); }, "val=" + values);
		}
		else
		{
			alert('Preencha os campos em vermelho !');
			return false;
		}
	}

	function ltrim(value)
	{
		var w_space = String.fromCharCode(32);
		var strTemp = "";
		var iTemp = 0;
		
		if(v_length < 1)
			return "";
	
		var v_length = value ? value.length : 0;
		
		while(iTemp < v_length)
		{
			if(value && value.charAt(iTemp) != w_space)
			{
				strTemp = value.substring(iTemp,v_length);
				break;
			}
			iTemp++;
		}	
		return strTemp;
	}

	function sendf()
	{
		var doc = ( arguments.length == 1) ? arguments[0] : false;
		var form = doc.forms[0];
		var flag = false;
		var nameForm = '';
		
		for( var i = 0; i < form.elements.length; i++ )
		{
			if( form.elements[i].type == 'hidden' )
			{
				flag = true;
				if( form.elements[i].name == 'HiddenJabberitExternals')
					nameForm = form.elements[i].name;
			}
		
		}
	
		if( flag )
		{
			if(flag && nameForm == 'HiddenJabberitExternals')
				addParticipantsExternals(doc);
		}	
		else
		{
			if( doc )
				return getInputs(doc);
		}
	}

	function setConfServerJabber()
	{
		var orgLdap		= document.getElementById('organizationLdapJabberit');
		var hostName	= document.getElementById('hostNameJabberit');
		var	serverLdap	= document.getElementById('serverLdapJabberit');
		var contextLdap = document.getElementById('contextLdapJabberit');
		var userLdap	= document.getElementById('userLdapJabberit');
		var pwdLdap		= document.getElementById('passwordLdapJabberit');	

		var ArrayConf = [
							['orgLdap', 'Informe uma organizacao !'],
							['hostName', 'Informe o nome do servidor Jabber !'],
							['serverLdap', 'Informe o nome do servidor Ldap !'],
							['contextLdap', 'Informe um contexto Ldap !'],
							['userLdap', ''],
							['pwdLdap', '']
						];

		for (var i in ArrayConf)
		{
			if( !ltrim(eval(ArrayConf[i][0]).value) && ltrim(ArrayConf[i][1]) )
			{
				alert(ArrayConf[i][1]);
				eval(ArrayConf[i][0]).focus();
				return false;				
			}	
		}

		_conn.go("$this.db_im.setHostJabber",
				 function(data)
				 {
					var _params = {
							'lang1': jabberitGetLang('Organization'),
							'lang2': jabberitGetLang('Servers Jabber'),
							'lang3': jabberitGetLang('Delete'),								
							'lang4': jabberitGetLang('Edit')							
					};

					var Table = document.getElementById('tableConfServersJabber');
					Table.parentNode.innerHTML = Xtools.parse( data, 'tableConfServersJabber.xsl', _params);
					
					for( var i in ArrayConf )
						eval(ArrayConf[i][0]).value = "";
				 },
				 "org="+orgLdap.value+"&jabberName="+hostName.value+
				 "&serverLdap="+serverLdap.value+"&contextLdap="+contextLdap.value+
				 "&user="+userLdap.value+"&password="+pwdLdap.value);
	}

	function setOrganizationsForGroups()
	{
		var elementSel = document.getElementById('organizations_ldap_jabberit');
		var Organization = "";
		
		for(var i = 0; i < elementSel.options.length; i++)
		{
			if( elementSel.options[i].selected == true && elementSel.options[i].value != "-1" )
				Organization = elementSel.options[i].value;
		}
		
		if( Organization )
		{
		
			var group = document.getElementById('nameGroup').value;
			var gidNumber = document.getElementById('gidNumber').value;
	
			_conn.go("$this.db_im.setOuGroupsLocked",
					 function(data)
					 {
	 					if ( data )
						{
							var _params = {
								'lang1': jabberitGetLang('Organization'),
								'lang2': jabberitGetLang('Delete')
							};
							
							var Table = document.getElementById('tableOrganizationsEnabledGroupsJabberit');
							Table.parentNode.innerHTML = Xtools.parse( data, 'tableOrganizationsEnabledGroupsJabberit.xsl', _params);
						}
					 }, 	
					 "group="+group+"&gidnumber="+gidNumber+"&ou="+Organization);
		}
	}

	function setParticipantsExternal()
	{
		if( arguments.length > 0 )
		{
			var element = arguments[0];
			var form = arguments[1].forms[0];
			var value = false;
			
			for(var i = 0; i < element.options.length; i++)
			{
				if( element.options[i].selected == true && element.options[i].value == 'true')
					value = true;
			}
			
			for(var i = 0; i < form.elements.length; i++)
			{
				switch(form.elements[i].type)
				{
					case 'text':
					case 'button':
						form.elements[i].disabled = false;			
						if(!value)
							form.elements[i].disabled = true;

						break;
				}
			}
			
			_conn.go("$this.db_im.setUseParticipantsExternal",
				function(data)
				{
					if(!data)
						alert('Error !');
				},
				"value=" + value);
		}
	}

	function removeHostsJabber()
	{
		if( arguments.length > 0 )
		{
			var idElement = arguments[0];
			var elementTableTr = document.getElementById(idElement);

			_conn.go("$this.db_im.removeHostsJabber",
				     function(data)
				     {
				     	data = eval(data);
				     	if( data )
							elementTableTr.parentNode.removeChild(elementTableTr);				     		
				     },
					 "item="+idElement);
		}
	}

	function removeOrgGroupsLocked()
	{
		if( arguments.length > 0 )
		{
			var element = document.getElementById(arguments[0]);
			var org = arguments[0];
			var group = document.getElementById('nameGroup').value;
			var gidNumber = document.getElementById('gidNumber').value;
			
			
			_conn.go("$this.db_im.removeOuGroupsLocked",
					function(data)
					{
						if( data )
							element.parentNode.removeChild(element);
					},
					"group="+group+"&gidnumber="+gidNumber+"&ou="+org);
		}
	}


	function removeOrgLdapAttributes()
	{
		if( arguments.length > 0 )
		{
			var element = document.getElementById(arguments[0]);
			var org = arguments[0];
			
			_conn.go("$this.db_im.removeAttributesLdap",
					function(data)
					{
						if( data )
							element.parentNode.removeChild(element);
					},
					"org=" + org);
		}
	}

	function removeParticipantsExternal()
	{
		if( arguments.length > 0 )
		{
			var element = document.getElementById(arguments[0]);
			var participantsExternal = arguments[0];
			
			_conn.go("$this.db_im.removeParticipantsExternal",
					function(data)
					{
						if( data )
							element.parentNode.removeChild(element);
					},
					"participants=" + participantsExternal);
		}
	}

	function constructScript(){}

	constructScript.prototype.editHostsJ				= editHostsJabber;
	constructScript.prototype.removeHostsJ				= removeHostsJabber;
	constructScript.prototype.removeOrg 				= removeOrgLdapAttributes;
	constructScript.prototype.removeOrgGroupsLocked 	= removeOrgGroupsLocked;
	constructScript.prototype.removePartExternal 		= removeParticipantsExternal;
	constructScript.prototype.setConfServerJabber		= setConfServerJabber;
	constructScript.prototype.setOrgFgroups 			= setOrganizationsForGroups;
	constructScript.prototype.setPartExternal 			= setParticipantsExternal;	
	constructScript.prototype.sendf 					= sendf;
	
	window.constructScript = new constructScript;
}
)();
