function CardContact()
{
	$("#info_card_cc").css({
		'width' 	: '244px',
		'height'	: '134px',
		'backgroundImage' : 'url(./templates/default/images/card.gif)'
	});

	this.delayCard 	= null;
	this.contact	= null;
}

CardContact.prototype.begin = function()
{
	if( arguments.length > 0 )
	{
		var element 		= $(arguments[0]);
		var email			= arguments[1];
		var trustedDomain 	= false;

		var handlerShowCardContact = function( data )
		{
			InfoContact.contact = data;
			InfoContact.showCard( data, element );
		}

		if( email.match(/<([^<]+)>+$/) )
		{
			email = email.match(/<([^<]+)>+$/)[1];

			// If "preferences.notification_domains" was setted, then verify if "mail" has a trusted domain.
			if( preferences.notification_domains && $.trim(preferences.notification_domains) != "" )
			{
				var domains = preferences.notification_domains.split(',');

				for( var i in domains )
					if( email.toString().match($.trim(domains[i])) && !trustedDomain )
						trustedDomain = true;
			}	
			
			if( trustedDomain )
			{
				var searchContact = true;

				for( var i in InfoContact.contact )
				{
					if( $.trim(i) === $.trim('email') && $.trim(InfoContact.contact[i]) === $.trim(email) )
						searchContact = false;
				}	

				if( searchContact )
					cExecute("$this.ldap_functions.getUserByEmail&email="+email, handlerShowCardContact);
				else
					handlerShowCardContact( InfoContact.contact );
			}
		}
		else
		{
			handlerShowCardContact( email );
		}
	}
}

CardContact.prototype.connectVoip = function (phoneUser, typePhone)
{
	var handler_connectVoip = function(data)
	{
		if(!data)
		{
			alert(get_lang("Error contacting VoIP server."));
		}
		else
		{
			alert(get_lang("Requesting a VoIP call")+":\n"+data);
		}
	}
	cExecute ("$this.functions.callVoipConnect&to="+phoneUser+"&typePhone="+typePhone, handler_connectVoip);
}

CardContact.prototype.sendMail = function( name , email )
{
	$("msg_number").val('"'+name+'" <'+email+'>'); 

	// Send New Message
	new_message_to(email);
}

CardContact.prototype.showCard = function( data, element )
{
	var position = $(element).position();
	var top = ( (parseInt($(window).height()) - parseInt(position.top)) < 150 ) ? ( parseInt(position.top) - 130 ) : parseInt(position.top);

	$("#info_card_cc").css({'display' : 'block','position' : 'absolute', 'z-index' : 'auto' });
	$("#info_card_cc").html('');
	$("#info_card_cc").html(new EJS( {url: 'templates/default/contact_card.ejs'} ).render( { 'data': data } ) );
	$("#info_card_cc").on("mouseover", function()
	{ 
		if( InfoContact.delayCard ) { clearTimeout( InfoContact.delayCard ); }	
	}).on("mouseout", function()
	{ 
		if( InfoContact.delayCard ) { clearTimeout( InfoContact.delayCard ); }	
		InfoContact.delayCard = setTimeout(function(){ $("#info_card_cc").fadeOut() }, 50);		
	});

	$("#info_card_cc").css({'display' : 'block','position' : 'absolute', 'z-index' : '1000', 'top' : top, 'left' : parseInt(position.left) + 60 });

	$(element).on("mousemove", function()
	{
		if( InfoContact.delayCard ) { clearTimeout( InfoContact.delayCard ); }	
		InfoContact.delayCard = setTimeout(function(){ $("#info_card_cc").fadeOut() }, 1000);		
	});
}

CardContact.prototype.hide = function()
{
	$("#info_card_cc").css({'display' : 'none' });
	if( InfoContact.delayCard ) { clearTimeout( InfoContact.delayCard ); }	
}

var InfoContact = new CardContact();