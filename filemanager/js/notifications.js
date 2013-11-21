(function()
{

	function notifications(){}
	
	function AddEmail()
	{
		var emailFrom	= document.getElementById('filemanager_add_email_from');	
		var emailTo		= document.getElementById('filemanager_add_email_to');

		var _AddEmail = function( data )
		{
			if( data !== "False")
			{
				var _data = data.split(",");
				var _tableElement = document.getElementById("table_email_notifications");
				_tableElement.innerHTML = "<tr class='th'><td width='80%'>"+get_lang("Email")+"</td>" +
										  "<td width='20%' align='center'>"+get_lang("Delete")+"</td></tr>";

				for( var i = 0; i < _data.length ; i++ )
				{
					var _tr = document.createElement("tr");
					
					var _td1 = document.createElement("td");
						_td1.appendChild( document.createTextNode( _data[i] ) );
						_tr.appendChild( _td1 );
					
					var _td2			= document.createElement("td");
						_td2.align 		= "center";
						_td2.innerHTML 	= '<a href="javascript:void();" onclick="notify.deleteEmail(\'' + _data[i] + '\',this);">' + get_lang("Delete") + '</a>';
						_tr.appendChild( _td2 );
						
					_tableElement.appendChild( _tr );	
				}
				
				emailTo.value = "";
				emailTo.focus();
			}	
		}
		
		if( ( emailFrom.value = trim(emailFrom.value) ) != "")
		{
			if( ( emailTo.value = trim(emailTo.value) ) != "" )
			{
				cExecute_("./index.php?menuaction=filemanager.notifications.AddEmail&emailFrom="+emailFrom.value+"&emailTo="+emailTo.value, _AddEmail);
			}
			else
				alert( get_lang("It is necessary to inform the user that will receive email notification") + "!");
		}
		else
		{
			alert( get_lang("It is necessary to inform the user that sends mail file") + "!");
		}
	}
	
	function DeleteEmail()
	{
		if( arguments.length > 0 )
		{
			var _emailFrom 	= trim( document.getElementById('filemanager_add_email_from').value );
			var _emailTo	= arguments[0];
			var _tr	= arguments[1].parentNode.parentNode;
			
			var _DeleteEmail = function(data)
			{
				if( data !== "False" )
					_tr.parentNode.removeChild( _tr );
			};
			
			cExecute_("./index.php?menuaction=filemanager.notifications.DeleteEmail&emailFrom="+_emailFrom+"&emailTo="+_emailTo, _DeleteEmail);
		}
	}
	
	function DeleteEmailUser()
	{
		if( arguments.length > 0 )
		{
			var _id = trim( arguments[0] );
			var _tr	= arguments[1].parentNode.parentNode;
			
			var _DeleteEmailUser = function(data)
			{
				if( data !== "False" )
					_tr.parentNode.removeChild( _tr );
			};
			
			cExecute_("./index.php?menuaction=filemanager.notifications.DeleteEmailUser&filemanagerId="+_id, _DeleteEmailUser);
		}
	}

	function trim(inputString)
	{
	   if ( typeof inputString != "string" )
		   return inputString;

	   var retValue = inputString;
	   var ch = retValue.substring(0, 1);
	   
	   while (ch == " ")
	   {
		  retValue = retValue.substring(1, retValue.length);
		  ch = retValue.substring(0, 1);
	   }
	   
	   ch = retValue.substring(retValue.length-1, retValue.length);
	   
	   while (ch == " ")
	   {
		  retValue = retValue.substring(0, retValue.length-1);
		  ch = retValue.substring(retValue.length-1, retValue.length);
	   }
	   
	   while (retValue.indexOf("  ") != -1) 
	   {
		  retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length);
	   }
	   return retValue;
	}

	notifications.prototype.addEmail		= AddEmail;
	notifications.prototype.deleteEmail		= DeleteEmail;
	notifications.prototype.deleteEmailUser	= DeleteEmailUser;
	
	window.notify	=	new notifications;
	
})();