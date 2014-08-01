<?php
if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail',
		'nonavbar'   => true,
		'noheader'   => true
	);
}
require_once '../header.inc.php';

	/**************************************************************************\
	* eGroupWare - ExpressoMail Preferences                                    *
	* http://www.expressolivre.org                                             *	
	* Modified by Alexandre Felipe Muller de Souza <amuller@celepar.pr.gov.br> *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
//include_once("fckeditor.php");
//include_once("class.functions.inc.php");
$type = isset($_GET['type']) ? $_GET['type']:$GLOBALS['type']; // FIX ME

//if ($type == 'user' || $type == ''){
global $prefs;
// 	create_select_box('Signature Type','',$default,'','','','onchange="javascript:changeType(this.value);"');
create_script('function exibir_ocultar()
{
    var type = ("'.$type.'" == "") ? "user" : "'.$type.'";
    var use_signature_digital_cripto = null;

    if (document.all)
    {
        // is_ie
        use_signature_digital_cripto = document.getElementsByName(type+"[use_signature_digital_cripto]")[1];
    }
    else
    {
        // not_ie
        use_signature_digital_cripto = document.getElementsByName(type+"[use_signature_digital_cripto]")[0];
    }

    var default_signature_digital_cripto = "'.$GLOBALS['phpgw_info']['default']['preferences']['expressoMail']['use_signature_digital_cripto'].'";

    if (use_signature_digital_cripto)
    {
        var element_signature_digital = document.getElementById(type+"[use_signature_digital]");
        var element_signature_cripto = document.getElementById(type+"[use_signature_cripto]");

        switch (use_signature_digital_cripto[use_signature_digital_cripto.selectedIndex].value){

            case "1":
                element_signature_digital.style.display="";
                element_signature_cripto.style.display="";
                break;
            case "0":
                element_signature_digital.style.display="none";
                element_signature_cripto.style.display="none";
                break;
            case "":
                if (default_signature_digital_cripto){
                    element_signature_digital.style.display="";
                    element_signature_cripto.style.display="";
                 }
                 else
                 {
                    element_signature_digital.style.display="none";
                    element_signature_cripto.style.display="none";
                 }

        }

    }

}
function validateSignature()
{
    var sigs_len = parseInt( document.getElementById("counter").value );

    var signatures = {}, types = {}, noSig = true;

    var default_signature = "", errors = false;

    for( var i = 0; i < sigs_len; i++ )
    {
	if( !document.getElementById( "_signature" + i ) ) continue;

	var key = encode64(document.getElementById( "title_signature" + i ).value);
	var edit = !!document.getElementById( "edit_signature" + i ).checked;

	var value =  edit ? CKEDITOR.instances["user_signature" + i ].getData() :
		            document.getElementById( "user_signature" + i ).value;
	
	if( !value )
	    continue;

	if( !key )
	{
		alert( "Titulo da assinatura nao pode ser em branco." );
		return( false );
	}

	var openTags = value.match(/<[a-z][^&>]*>/g);
	var closedTags = value.match(/<[/][^&>]*>/g);
	var oclosedTags = value.match(/[a-z]*[^&<>]* [/]>/g);

	if( ( openTags || [] ).length !== (( closedTags || [] ).length + ( oclosedTags || [] ).length ))
	{
	    errors = errors || [];
	    errors.push( key );
	}

	if( errors )
	    continue;

	if( document.getElementById( "default_signature" + i ).checked )
	    default_signature = key;

	signatures[key] = value;

	if( edit )
	    types[key] = edit;

	if( noSig )
	    noSig = false;
    }

    if( errors ){

	alert( "Há erros de html na(s) assinatura(s) \'" + errors.join("\',\'") + "\'.\\nRevise a informação inserida (somente no modo Texto Simples ou visulização do Código-Fonte, no modo Texto Rico).\\nPossivelmente você copiou e colou sua assinatura de outro software. Para evitar erros, recomendamos utilizar apenas o editor Texto Rico do Expresso." );
	return( false );
    }

    if( !default_signature ){
	
	if( !noSig )
	{
	    alert( "Favor selecionar uma assinatura padrao." );
	    return( false );
	}

	document.getElementById( "signature_default" ).value =  "";
	document.getElementById( "signature" ).value = "";
    }
    else
    {
	document.getElementById( "signature_default" ).value =  default_signature;
	document.getElementById( "signature" ).value = types[key] ? signatures[default_signature] :
								    signatures[default_signature].replace( /\\n/g, "<br />" );
    }
    if (document.getElementById( "signature" ).value != "")
    	document.getElementById( "signature" ).value = encode64(document.getElementById( "signature" ).value);
    document.getElementById( "signatures" ).value = toJSON( signatures );
    document.getElementById( "signature_types" ).value = toJSON( types );

    return( true );
}
function fromJSON( value )
{
    return (new Function( "return " + decode64( value )))();
}

function toJSON( value )
{
    var json = [];

    for( var key in value )
	json.push(  \'"\' + key + \'":"\' + escape( value[key] ) + \'"\' );

    return encode64( "{" + json.join( "," ) + "}" );
}

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function encode64(input) {
	var output = new StringMaker();
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	while (i < input.length) {
		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
			enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
			enc4 = 64;
		}

		output.append(keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4));
   }
   
   return output.toString();
}
var ua = navigator.userAgent.toLowerCase();
if (ua.indexOf(" chrome/") >= 0 || ua.indexOf(" firefox/") >= 0 || ua.indexOf(" gecko/") >= 0) {
    var StringMaker = function () {
        this.str = "";
        this.length = 0;
        this.append = function (s) {
            this.str += s;
            this.length += s.length;
        }
        this.prepend = function (s) {
            this.str = s + this.str;
            this.length += s.length;
        }
        this.toString = function () {
            return this.str;
        }
    }
} else {
    var StringMaker = function () {
        this.parts = [];
        this.length = 0;
        this.append = function (s) {
            this.parts.push(s);
            this.length += s.length;
        }
        this.prepend = function (s) {
            this.parts.unshift(s);
            this.length += s.length;
        }
        this.toString = function () {
            return this.parts.join("");
        }
    }
}
function decode64(input) {
	var output = new StringMaker();
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	while (i < input.length) {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output.append(String.fromCharCode(chr1));

		if (enc3 != 64) {
			output.append(String.fromCharCode(chr2));
		}
		if (enc4 != 64) {
			output.append(String.fromCharCode(chr3));
		}
	}

	return output.toString();
}
function normalizerSignature(values){

    var value = {};

    for( key in values ){

        value[isEncoded64(key) ? decode64(key) : key] = values[key];

    }

    return value;
}
/*Verifica se a string input esta em Base 64*/
function isEncoded64(input){
	var baseStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var encoded = true;
	if ( (input.length % 4) != 0)
		return false;
	for(var i=0; i<input.length; i++){
		if ( baseStr.indexOf(input[i]) < 0 ){
			encoded = false;
			break;
		}
	}
	return encoded;
}
function loadSignature()
{
    var types = fromJSON( document.getElementById( "signature_types" ).value );
    var signatures;
    if (document.getElementById( "signatures" ).value)
    {
        signatures = fromJSON(document.getElementById("signatures").value);
    } else if (document.getElementById( "signature" ).value) // Caso a assinatura esteja no formato da versão 2.2.10:
    {
        // TODO: Internazionalizar a string
        signatures = {};
        signatures["Assinatura padrão"] = document.getElementById( "signature" ).value;
        if (!document.getElementById("signature_default").value)
        {
            document.getElementById("signature_default").value = "Assinatura padrão";
        }
    }

    signatures = normalizerSignature(signatures);
    types = normalizerSignature(types);

    var old_signature = document.getElementById( "signature" ).value;
    var def = document.getElementById( "signature_default" ).value;
	def = isEncoded64(def) ? decode64(def) : def;

    var counter = 0, ids = [], def_signature = "", noSig = true;

    for( key in signatures )
    {
    	addSignature( !types || !types[key] );

	var value = unescape( signatures[key] );

	document.getElementById( "title_signature" + counter ).value = key;
	document.getElementById( "user_signature" + counter ).value = value;

	if( def === key )
	    def_signature = counter;

	if( noSig ) noSig = false;

	counter++;
    }

    if( def_signature !== "" )
    	document.getElementById( "default_signature" + def_signature ).checked = true;
    else if( noSig && old_signature )
    {
        var type_signature = document.getElementById("type_signature");
        if (type_signature)
        {
            addSignature( type_signature.value !== "html" );
            document.getElementById( "user_signature" + counter ).value = old_signature;
        }
    }
}
');
//}
$default = false;
create_check_box('Do you want to show common name instead of UID?','uid2cn',$default,
	'Do you want to show common name instead of UID?');
create_check_box('Do you want to automatically display the message header?','show_head_msg_full',$default,'');
create_check_box('Do you want to display date in format numerical?','show_date_numerical',$default,''); 
$default = array(
	'25'	=> '25',
	'50'	=> '50',
	'75'	=> '75',
	'100'	=> '100'
);

create_select_box('What is the maximum number of messages per page?','max_email_per_page',$default,'This is the number of messages shown in your mailbox per page');

create_check_box('View the user name in the header of the messages printed?', 'show_name_print_messages', 'Displays the user name in the header print email');

create_check_box('Habilitar funcionalidade de notificar ao receber mensagens filtradas por remetente ?', 'use_alert_filter_criteria', '');
create_check_box('Habilitar sinalizadores e marcadores em mensagens', 'use_followupflags_and_labels', '');

//$default = 0;
create_check_box('Preview message text within subject column','preview_msg_subject','this exhibits a sample of message within the message subject column');

//$default = 0;
create_check_box('Preview message text within a tool-tip box','preview_msg_tip','this exhibits a sample of message within a tool-tip box');

create_check_box('View extended information about users','extended_info','This exhibits employeenumber and ou from LDAP in searchs');
create_check_box('Save deleted messages in trash folder?','save_deleted_msg','When delete message, send it automatically to trash folder');
$default = array(
	'1'    => lang('1 day'),
	'2'    => lang('2 days'),
	'3'    => lang('3 days'),
	'4'   => lang('4 days'),
	'5'   => lang('5 days')
);

$arquived_messages = array(true => lang("Copy"), false => lang("Move"));

//Desbilitado limpeza de lixeira por request. Ticket #3253
//create_select_box('Delete trash messages after how many days?','delete_trash_messages_after_n_days',$default,'Delete automatically the messages in trash folder in how many days');
create_check_box('Would you like to use local messages?','use_local_messages','Enabling this options you will be able to store messages in your local computer');
create_select_box('Desired action to archive messages to local folders','keep_archived_messages',$arquived_messages,'After store email in your local computer delete it from server');
create_check_box('Automaticaly create Default local folders?','auto_create_local','Enable this option if you want to automaticaly create the Inbox, Draft, Trash and Sent folders');
create_check_box('Show previous message, after delete actual message?','delete_and_show_previous_message','Enable this option if you want to read the next message everytime you delete a message');
create_check_box('Do you wanna receive an alert for new messages?','alert_new_msg','Everytime you receive new messages you will be informed');


create_check_box('Wish you receive notifications for: "New messages", "Filter criteria", "Event alerts"?','notifications','Everytime you receive new messages you will be informed');



create_check_box('Show default view on main screen?','mainscreen_showmail','Show unread messages in your home page');
create_check_box('Do you want to use remove attachments function?','remove_attachments_function','It allow you to remove attachments from messages');
create_check_box('Do you want to use important flag in email editor?','use_important_flag','It allow you to send emails with important flag, but you can receive unwanted messages with important flag');
//create_check_box('Do you want to use SpellChecker in email editor?','use_SpellChecker','It allow you to check the spelling of your emails');
//Use user folders from email

require_once('class.imap_functions.inc.php');
$boemailadmin = CreateObject('emailadmin.bo');
$emailadmin_profile = $boemailadmin->getProfileList();
$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
$e_server = $_SESSION['phpgw_info']['expressomail']['email_server'];
$imap = CreateObject('expressoMail.imap_functions');

if ($type != "" && $type != "user"){
	
	$trash = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'];
	$drafts = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'];
	$spam = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'];
	$sent = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'];
	$delimiter = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'];
	$default = Array(
		'INBOX' =>      lang('INBOX'), 
		'INBOX' . $imap->imap_delimiter . $drafts => lang($drafts),
		'INBOX' . $imap->imap_delimiter . $spam => lang($spam),
		'INBOX' . $imap->imap_delimiter . $trash => lang($trash),  
		'INBOX' . $imap->imap_delimiter . $sent => lang($sent)
	);
}
else
{
$save_in_folder_selected = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'];

// Load Special Folders (Sent, Trash, Draft, Spam) from EmailAdmin (if exists, else get_lang)
$specialFolders = array ("Trash" => lang("Trash"), "Drafts" => lang("Drafts"), "Spam" => lang("Spam"), "Sent" => lang("Sent"));

foreach ($specialFolders as $key => $value){
	if($e_server['imapDefault'.$key.'Folder'])
		$specialFolders[$key] = $e_server['imapDefault'.$key.'Folder'];
}
unset($default);
$default[-1] = lang('Select on send');
	
	foreach($imap -> get_folders_list(array('noSharedFolders' => true)) as $id => $folder){
		if(!is_numeric($id))
			continue;
		else{
			// Translate INBOX (root folder)
			if (strtolower($folder['folder_name']) == "inbox")
				$folder['folder_name'] = lang("Inbox");
			// Translate Special Folders
			elseif (($keyFolder = array_search($folder['folder_name'], $specialFolders)) !== false)
				$folder['folder_name'] = lang($keyFolder);
			// Identation for subfolders
			$folder_id = explode($e_server['imapDelimiter'],$folder['folder_id']);       
			$level = count($folder_id);
			$ident = '';
			for($i = 2; $level > 2 && $i < $level;++$i)
				$ident .= ' - ';
			
			$default[$folder['folder_id']] = $ident.$folder['folder_name'];
		}		
	}

}
create_select_box('Save sent messages in folder','save_in_folder',$default,'Save automatically sent messages in selected folder');
create_check_box('Show TO: in place of FROM: only in Automatic SEND folder','from_to_sent','Show TO: in place of FROM: only in Automatic SEND folder');

$default =  array(
    '50'    => '50',
    '100'   => '100',
    '150'   => '150',
    '200'   => '200',
    '300'   => '300',
    '400'   => '400',
    '65536' => lang('unlimited')
);

create_select_box('What is the maximum number of results in an e-mail search?','search_result_number',$default,'');

$default =  array( 
	// Não é possivel colocar um 0 como valor, pois ele troca para 4 como padrão! então coloquei um x, e tratei isso la no javascriot .. então x = 0
    'x'		=> lang('unlimited'),
    '1' 	=> '1',
    '2'     => '2',
    '3'     => '3',
    '4'     => '4',
    '5'     => '5'
);

create_select_box('What is the minimum number of characters in searching contacts?','search_characters_number',$default,'what is the minimum number of characters in searching contacts');

	$default = array( 
	'20' => lang('normal'),
	'30' => lang('medium'),
	'40' => lang('big')
	);


create_select_box('What is the height of the lines in the list of messages?','line_height',$default,'');
create_check_box('Increases th maximum size of show messages?','max_msg_size','Increases the maximum size of show emails from 100kb to 1mb');
create_check_box('Use dynamic contacts?','use_dynamic_contacts','Store your\'s most used contacts');
create_check_box('Use shortcuts?','use_shortcuts','n key (Open new message)<br />ESC key (Close tab)<br />i key (print)<br />e key (forward)<br />r key (reply)<br />DELETE key (delete the current message)<br />Ctrl + up (go to previous message)<br />Ctrl + down (go to next message)<br />Shift + up or down (select multiple messages)<br />F9  key (search at catalog)<br />');
create_check_box('Auto save draft','auto_save_draft','When you are away from computer it saves automatically the message you are writing');
create_check_box('Send messages with return recipient option by default','return_recipient_deafault','With this option every new email will get the return recipient option marked');

unset($default);
$functions = new functions();
$zones = $functions->getTimezones();
$default = array(sprintf("%s", array_search('America/Sao_Paulo', $zones)) => 'America/Sao_Paulo');
create_select_box('What is your timezone?', 'timezone', $zones, 'The Timezone you\'re in.', $default);

$default =  array(
    '1' => lang('contacts'),
    '2' => lang('email')
);

create_select_box('Where should the quick search be performed by default?','quick_search_default',$default,'It is where the keyword should be searched when the user executes a quick search.');

$default =  array(
	'65536' => lang('unlimited'),
	'640' => '640',
	'768' => '768',
	'800' => '800',
	'1024' => '1024',
	'1080' => '1080'
);

create_select_box('What is the maximum size of embedded images?','image_size',$default,'When user send an email with image in body message, it changes the size');
create_check_box('Use plain text editor as standard ?','plain_text_editor','');


$default = array( 
 		    'global'     => lang("Global catalog"), 
 		    'personal'   => lang("Personal catalog"), 
 		    'all' => lang("All catalogs") 
);

create_select_box('The dynamic search will use the catalog','catalog_search',$default,'Seleciona o catálogo que será usado para fazer a busca');
$default = false;
create_check_box('Display default fields on the quick search screen?','default_fields_quick_search', $default, 'Mostrar o nome, email e telefone do contato por padrão');


if($GLOBALS['phpgw_info']['server']['use_assinar_criptografar'])
{
    create_check_box('Enable digitally sign/cipher the message?','use_signature_digital_cripto','','',True,'onchange="javascript:exibir_ocultar();"');
    if ($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'])
    {
        create_check_box('Always sign message digitally?','use_signature_digital','');
        create_check_box('Always cipher message digitally?','use_signature_cripto','');
    }
    else
    {
        create_check_box('Always sign message digitally?','use_signature_digital','','',True,'',False);
        create_check_box('Always cipher message digitally?','use_signature_cripto','','',True,'',False);
    }
}

create_check_box('Would you like to have a read receipt option to read messages?','confirm_read_message','');
create_check_box('Would you like to activate the alert for message attachment?','alert_message_attachment','');

$default = array(
	'text' => lang('simple text'),
	'html' => lang('rich text')
);
create_check_box('Auto close the first tab on reaching the maximum number of tabs?','auto_close_first_tab','');
create_check_box('Insert signature automatically in new messages?','use_signature','');

$default = array('0' => lang('nested in the same tab of the main message'), '1' => lang('in your own tab, one for each attached message'));
create_select_box('Nested messages are shown','nested_messages_are_shown', $default,'How to show nested messages');

$default = array(
    'Arial'     => 'Arial',
    'Verdana'   => 'Verdana',
    'Times new roman'   => 'Times New Roman',
    'Tahoma'   => 'Tahoma',
);
create_select_box( 'Default font editor' , 'font_family_editor' , $default );

$default = array(
    '8pt'     => '8',
    '9pt'     => '9',
    '10pt'    => '10',
    '11pt'    => '11',
    '12pt'    => '12',
    '14pt'    => '14',
    '16pt'    => '16',
    '18pt'    => '18',
    '20pt'    => '20',
    '22pt'    => '22',
    '24pt'    => '24',
    '26pt'    => '26',
    '28pt'    => '28',
    '36pt'    => '36',
    '48pt'    => '48',
    '72pt'    => '72',
);

create_select_box( 'Default font size editor' , 'font_size_editor' , $default );

if ($type == 'user' || $type == ''){
	$vars = $GLOBALS['phpgw']->preferences->user['expressoMail'];


	create_html_code("signature","<script src='../prototype/plugins/jquery/jquery.min.js' language='javascript'></script>
 		            <script src='../prototype/library/ckeditor/ckeditor.js' language='javascript'></script> 
 		            <script src='../prototype/library/ckeditor/adapters/jquery.js' language='javascript'></script>
            
	<input type='hidden' id='counter' value='0'>
	<input type='hidden' id='signatures' name='user[signatures]' value='". $vars['signatures']."'>
	<input type='hidden' id='signature_default' name='user[signature_default]' value='".$vars['signature_default']."'>
	<input type='hidden' id='signature' name='user[signature]' value='","' >
	<input type='hidden' id='signature_types' name='user[signature_types]' value='".$vars['signature_types']."'>

	<div id='_signature' name='signature' style='display: none;'>
	<div id='options_signature'>
	<input id='edit_signature' type='checkbox' name='isEditor' onclick='changeType( this, this.id.replace( /[^0-9]*/gi, \"\" ) );' checked='checked'><label for='isEditor'>" . lang("Text editor") . "</label>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp; " . lang("Title subscription") . "&nbsp;<input type='text' id='title_signature'>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;<input type='radio' id='default_signature' name='signature_default'><label>" . lang("Use by default") . "</label> &nbsp;|&nbsp; <a href='#' onclick='javascript: return removeSignature( this );' id='remove_signature'> ". lang("Remove"). "</a> 
	</div><br/>
	<div id='text_signature'>
	<textarea rows='10' cols='65' id='user_signature' class='editor'></textarea></div>
	</div>

		<script language='javascript'>
 
	$(document).ready(function(){
 
	    loadSignature();
 
	});
  
	function changeType(obj, target){         
 		            if(obj.checked === true) 
 		               $('#user_signature' + target).ckeditor(  { toolbar:'signature'  }); 
 		            else 
 		                CKEDITOR.instances['user_signature' + target ].destroy(); 
    }

// var counter = 0;

function addSignature( simple )
{
	var sig = document.getElementById('_signature').cloneNode( true );
	var counter = document.getElementById( 'counter' ).value;
   
	sig.innerHTML = sig.innerHTML.replace( /_signature/g, '_signature' + counter );

	sig.id = '_signature' + counter;
	sig.style.display = '';

	$( '#add_signature' ).before( sig );

	if( !simple )
		$('#user_signature' + counter).ckeditor(  { toolbar:'signature'  });
	else
	    document.getElementById('edit_signature' + counter ).checked = '';

	document.getElementById( 'counter' ).value = ++counter;
}

function removeSignature( el )
{
    counter = el.id.replace( /[^0-9]*/gi, \"\" );

    el = document.getElementById( '_signature' + counter );
    el.parentNode.removeChild( el );

    return( false );
}
</script>
<input id='add_signature' type='button' onclick='addSignature();' value='" . lang("Add Subscription") . "'>
");
}
?>
