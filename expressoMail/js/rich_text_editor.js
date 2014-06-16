function cRichTextEditor(){
    this.emwindow   = new Array;
    this.editor = "body_1";
    this.table = "";
    this.id = "1";
    this.saveFlag = 0;
    this.signatures = false;
    this.replyController = false; 
    this.newImageId = false;
    this.plain = new Array;
    this.editorReady = true;
}

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

var ua = navigator.userAgent.toLowerCase();
if (ua.indexOf(" chrome/") >= 0 || ua.indexOf(" firefox/") >= 0 || ua.indexOf(' gecko/') >= 0) {
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
            return this.parts.join('');
        }
    }
}

cRichTextEditor.prototype.fromJSON = function( value )
{
	if(!value)
		return '';
	return (new Function( "return " + this.decode64( value )))();
}

cRichTextEditor.prototype.decode64 = function(input) { 
 	if( typeof input === "undefined" ) return '';

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


cRichTextEditor.prototype.loadEditor = function(ID) {
	
        var parentDiv = document.getElementById("body_position_" + ID);
        var pObj = "body_" + ID;
        var textArea = document.createElement("TEXTAREA");
        textArea.id = pObj;
        textArea.style.width = '100%';
        parentDiv.appendChild(textArea);
        RichTextEditor.plain[ID] = false; 
        
        if(preferences.plain_text_editor == 1)
		{
			RichTextEditor.plain[ID] = true;  
			RichTextEditor.editorReady = true;
		}
        else 
			RichTextEditor.active(pObj);
}

cRichTextEditor.prototype.loadEditor2 = function(ID) {      
		var pObj = "body_" + ID;
        RichTextEditor.plain[ID] = false; 
        
        if(preferences.plain_text_editor == 1)
		{
			RichTextEditor.plain[ID] = true;  
			RichTextEditor.editorReady = true;
		}
        else 
			RichTextEditor.active(pObj);
}


cRichTextEditor.prototype.getSignaturesOptions = function() {
	
    if(RichTextEditor.signatures !== false)
        return RichTextEditor.signatures;
                
   	var signatures = RichTextEditor.normalizerSignature(this.fromJSON( preferences.signatures ));
	var signature_types = RichTextEditor.normalizerSignature(this.fromJSON( preferences.signature_types ));

	for( key in signatures )
	    if( !signature_types[key] )
		    signatures[key] = signatures[key].replace( /\n/g, "<br />" );

    RichTextEditor.signatures = signatures;
    return signatures;

}
cRichTextEditor.prototype.normalizerSignature = function(values) {

    var value = {};

    for (key in values){

        value[RichTextEditor.isEncoded64(key) ? RichTextEditor.decode64(key) : key] = values[key];
    }

    return value;

}

/*Verifica se a string input esta em Base64*/
cRichTextEditor.prototype.isEncoded64 = function(input){
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

cRichTextEditor.prototype.getSignatureDefault = function() {

    if(RichTextEditor.signatures === false){
        RichTextEditor.signatures = RichTextEditor.getSignaturesOptions();
        preferences.signature_default = preferences.signature;
    }
         
    if(!RichTextEditor.signatures || !preferences.signature_default)
    {
      preferences.use_signature = "0"; //Desabilita o uso da assinatura
      return '';
    }
	if (RichTextEditor.isEncoded64(preferences.signature_default))
		preferences.signature_default = RichTextEditor.decode64(preferences.signature_default);
    return unescape(preferences.signature_default);

}


cRichTextEditor.prototype.execPosInstance = function(inst) {
     if(RichTextEditor.editorReady === false)
     {
	var editor =  CKEDITOR.instances[inst]; 
	var id = inst.replace('body_','');
		var btnSave = $("#content_id_" + id + " .save");
		
		CKEDITOR.instances[inst].on('key', function(event){
			btnSave.button("enable");
		})
       
        // IM Module Enabled
	if( window.parent.loadscript && loadscript.autoStatusIM )
	{
		CKEDITOR.instances[inst].on('key', function(event){
			loadscript.autoStatusIM;
		});            	
	}

	if (preferences.auto_save_draft == 1)
	{
            autoSaveControl.status[id] = true;
            autoSaveControl.timer[id] = window.setInterval( "autoSave(\""+id+"\")" ,autosave_time);

            CKEDITOR.instances[inst].on('key', function(event){
                autoSaveControl.status[id] = false;
            })
        }
        
	$(".cke_editor").css("white-space", "normal");

    if(typeof(preferences.font_size_editor) !== 'undefined')
        $(editor.document.$.body).css("font-size",preferences.font_size_editor);
    if(typeof(preferences.font_family_editor) !== 'undefined')
        $(editor.document.$.body).css("font-family",preferences.font_family_editor);

    RichTextEditor.editorReady = true;
    }	
}

cRichTextEditor.prototype.setPlain = function (active,id){
      RichTextEditor.plain[id] = active;
	  var content = $("#content_id_"+id);
	  //var div = $("<div>").attr("display", "none");
      if(active === true)
      {
            CKEDITOR.instances['body_'+id].destroy();
            var height = document.body.scrollHeight;
            height -= 330;
            //Insere o texto sem formatação no textarea
            var text_body = remove_tags($('#body_'+id).val());
            $('#body_'+id).val(text_body);
            
            $('#body_'+id).keydown(function(event) {
                away = false;
                save_link = content.find(".save")[0];
                save_link.onclick = function onclick() {openTab.toPreserve[id] = true;save_msg(id);} ;
				$("#save_message_options_"+id).button({ disabled: false });
                //save_link.className = 'message_options';
            });
			$("[name=textplain_rt_checkbox_"+id+"]").button({ disabled: false });

            $('#body_'+id).on('keydown',function(){
            $("#content_id_"+currentTab+" .save").button("enable");
        });
      }   
      else{
          RichTextEditor.active('body_'+id, id);
          /*Insere somente quebras de linha para que o texto convertido não fique todo em uma linha só*/
          var text_body = $('#body_'+id).val().replace(/[\n]+/g, '<br />');
          $('#body_'+id).val(text_body);
      }
}

cRichTextEditor.prototype.getData = function (inst){  
    var id = inst.replace('body_','');
    
    if(RichTextEditor.plain[id] === true)
        return $('#'+inst).val();
    else
        return CKEDITOR.instances[inst].getData();
}
cRichTextEditor.prototype.setData = function (id,data){
    
	if(this.plain[id.replace('body_','')] === true)
		$('#'+id).val(data);
    else
        CKEDITOR.instances[id].setData(data);
}

cRichTextEditor.prototype.dataReady = function(id,reply)
{
	var content = $("#content_id_"+id);
	var input = content.find('.new-message-input.to:first');
	if (this.plain[id]){
		if (reply === 'forward')
			setTimeout(function(){input.focus();},400);
	}
}

cRichTextEditor.prototype.setInitData = function (id,data,dataType,recursion, callback)
{
	if(recursion === undefined){
		recursion = 1;
	}else{
		recursion++;    
	}
	if(this.plain[id] === true){               
		data =  data.replace( new RegExp('<pre>((.\n*)*)</pre>'),'$1');
		if($('#'+id) !== undefined){
			$('#'+id).val(data);
		}
		else{
			setTimeout(function() {RichTextEditor.setInitData(id,data,dataType,recursion); }, 500);
		}
	}  
	else{
		if( RichTextEditor.editorReady === true && CKEDITOR.instances['body_'+id] !== undefined ){
			var editor =   CKEDITOR.instances['body_'+id]; 
			var fontSize = '';
			var fontFamily = '';
			if(typeof(preferences.font_size_editor) !== 'undefined')
				fontSize = 'font-size:' + preferences.font_size_editor;
			if(fontSize != '') 
				fontFamily = ';'
			if(typeof(preferences.font_family_editor) !== 'undefined')
				fontFamily += 'font-family:' + preferences.font_family_editor + ';'; 
			var divBr = '<div style="'+fontSize+fontFamily+'"><br type="_moz"></div>';
			
			if(dataType == 'edit')
				editor.setData(data , null , false);
			else
				editor.setData(divBr+data , null , false);
			
			if(callback !== undefined)
				callback();
		}
		else if(recursion < 20){
			setTimeout(function() {RichTextEditor.setInitData(id,data,dataType,recursion); }, 500);
		}
	} 
}

cRichTextEditor.prototype.destroy = function(id)
{
        //Remove Instancia do editor
        if( CKEDITOR.instances[id] !== undefined )   
             CKEDITOR.remove(CKEDITOR.instances[id]);
}
cRichTextEditor.prototype.active = function(id, just_id)
{
   
   //Remove Instancia do editor caso ela exista
    if( CKEDITOR.instances[id] !== undefined )   
         CKEDITOR.remove(CKEDITOR.instances[id]);
     
    var height = document.body.scrollHeight;
     height -= 375;
     $('#'+id).ckeditor( 
		function() {
			RichTextEditor.execPosInstance(id)
		},
		{
			toolbar:'mail',
			height: height
		}
	); 
	//$("[name=textplain_rt_checkbox_"+just_id+"]").button({ disabled: false });
}
cRichTextEditor.prototype.focus = function(id)
{
    if(RichTextEditor.plain[id]  === true)
        $('#body_'+id).focus();
    else
        CKEDITOR.instances['body_'+id].focus(); 

}

cRichTextEditor.prototype.blur = function(id)
{
    if(RichTextEditor.plain[id]  === true)
        $('#body_'+id).blur();
    else{
	    var focusManager = new CKEDITOR.focusManager( CKEDITOR.instances['body_'+id] );
		if (focusManager)
			focusManager.blur();
	}
}

//Função reseta o atributo contentEditable para resolver bug de cursor ao trocar abas 
cRichTextEditor.prototype.setEditable = function(id) { 
        if( CKEDITOR.instances['body_'+ id] === undefined ) return;    
        var element = CKEDITOR.instances['body_'+ id].document.getBody(); 
        element.removeAttribute('contentEditable'); 
        element.setAttribute('contentEditable','true'); 
}
//Build the Object
RichTextEditor = new cRichTextEditor();
