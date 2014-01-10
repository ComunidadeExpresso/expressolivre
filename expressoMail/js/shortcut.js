/**
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/
 * Version : 2.01.A
 * By Binny V A
 * License : BSD
 */
shortcut = {
	'all_shortcuts':{},//All the shortcuts are stored in this array
    'disabled': false,
	'add': function(shortcut_combination,callback,opt) {
		//Provide a set of default options
		var default_options = {
			'type':'keydown',
			'propagate':false,
			'disable_in_input':false,
			'target':document,
			'keycode':false
		}
		if(!opt) opt = default_options;
		else {
			for(var dfo in default_options) {
				if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
			}
		}
		
		var ele = opt.target;
		if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
		var ths = this;
		shortcut_combination = shortcut_combination.toLowerCase();

		//The function to be called at keypress
		var func = function(e) {
			e = e || window.event;
			
			if(opt['disable_in_input']) { //Don't enable shortcut keys in Input, Textarea fields
				var element;
				if(e.target) element=e.target;
				else if(e.srcElement) element=e.srcElement;
				if(element.nodeType==3) element=element.parentNode;
                
				if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') return;
			}
          
            if(shortcut.disabled === true)
                return;


			//Find Which key is pressed
			if (e.keyCode) code = e.keyCode;
			else if (e.which) code = e.which;
			var character = String.fromCharCode(code).toLowerCase();
			
			if(code == 188) character=","; //If the user presses , when the type is onkeydown
			if(code == 190) character="."; //If the user presses , when the type is onkeydown
	
			var keys = shortcut_combination.split("+");
			//Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
			var kp = 0;
			
			//Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
			var shift_nums = {
				"`":"~",
				"1":"!",
				"2":"@",
				"3":"#",
				"4":"$",
				"5":"%",
				"6":"^",
				"7":"&",
				"8":"*",
				"9":"(",
				"0":")",
				"-":"_",
				"=":"+",
				";":":",
				"'":"\"",
				",":"<",
				".":">",
				"/":"?",
				"\\":"|"
			}
			//Special Keys - and their codes
			var special_keys = {
				'esc':27,
				'escape':27,
				'tab':9,
				'space':32,
				'return':13,
				'enter':13,
				'backspace':8,
	
				'scrolllock':145,
				'scroll_lock':145,
				'scroll':145,
				'capslock':20,
				'caps_lock':20,
				'caps':20,
				'numlock':144,
				'num_lock':144,
				'num':144,
				
				'pause':19,
				'break':19,
				
				'insert':45,
				'home':36,
				'delete':46,
				'end':35,
				
				'pageup':33,
				'page_up':33,
				'pu':33,
	
				'pagedown':34,
				'page_down':34,
				'pd':34,
	
				'left':37,
				'up':38,
				'right':39,
				'down':40,
	
				'f1':112,
				'f2':113,
				'f3':114,
				'f4':115,
				'f5':116,
				'f6':117,
				'f7':118,
				'f8':119,
				'f9':120,
				'f10':121,
				'f11':122,
				'f12':123
			}
	
			var modifiers = { 
				shift: {wanted:false, pressed:false},
				ctrl : {wanted:false, pressed:false},
				alt  : {wanted:false, pressed:false},
				meta : {wanted:false, pressed:false}	//Meta is Mac specific
			};
                        
			if(e.ctrlKey)	modifiers.ctrl.pressed = true;
			if(e.shiftKey)	modifiers.shift.pressed = true;
			if(e.altKey)	modifiers.alt.pressed = true;
			if(e.metaKey)   modifiers.meta.pressed = true;
                        
			for(var i=0; k=keys[i],i<keys.length; i++) {
				//Modifiers
				if(k == 'ctrl' || k == 'control') {
					kp++;
					modifiers.ctrl.wanted = true;

				} else if(k == 'shift') {
					kp++;
					modifiers.shift.wanted = true;

				} else if(k == 'alt') {
					kp++;
					modifiers.alt.wanted = true;
				} else if(k == 'meta') {
					kp++;
					modifiers.meta.wanted = true;
				} else if(k.length > 1) { //If it is a special key
					if(special_keys[k] == code) kp++;
					
				} else if(opt['keycode']) {
					if(opt['keycode'] == code) kp++;

				} else { //The special keys did not match
					if(character == k) kp++;
					else {
						if(shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
							character = shift_nums[character]; 
							if(character == k) kp++;
						}
					}
				}
			}

			if(kp == keys.length && 
						modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
						modifiers.shift.pressed == modifiers.shift.wanted &&
						modifiers.alt.pressed == modifiers.alt.wanted &&
						modifiers.meta.pressed == modifiers.meta.wanted) {
				callback(e);
	
				if(!opt['propagate']) { //Stop the event
					//e.cancelBubble is supported by IE - this will kill the bubbling process.
					if ( Element('border_id_0') && Element('border_id_0').className != 'menu-sel' ){
						return false;
					}
					e.cancelBubble = true;
					e.returnValue = false;
	
					
					//e.stopPropagation works in Firefox.
					if (e.stopPropagation) {
					if ( Element('border_id_0') && Element('border_id_0').className != 'menu-sel' ){
						return false;
					}
						
						e.stopPropagation();
						e.preventDefault();
					}
					return false;
				}
				
			}
			if (currentTab && currentTab == 0 && code == 13){
				e.stopPropagation();
				e.preventDefault();
			}
			if ((openTab.type[currentTab] == 2) && code == 13){
				e.preventDefault();
				e.stopPropagation();
			}		
		}
		this.all_shortcuts[shortcut_combination] = {
			'callback':func, 
			'target':ele, 
			'event': opt['type']
		};
		//Attach the function with the event
		if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
		else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
		else ele['on'+opt['type']] = func;
	},

	//Remove the shortcut - just specify the shortcut and I will remove the binding
	'remove':function(shortcut_combination) {
		shortcut_combination = shortcut_combination.toLowerCase();
		var binding = this.all_shortcuts[shortcut_combination];
		delete(this.all_shortcuts[shortcut_combination])
		if(!binding) return;
		var type = binding['event'];
		var ele = binding['target'];
		var callback = binding['callback'];

		if(ele.detachEvent) ele.detachEvent('on'+type, callback);
		else if(ele.removeEventListener) ele.removeEventListener(type, callback, false);
		else ele['on'+type] = false;
	}
}

/* ExpressMail Functions */

var shift_up_count = 0;
var shift_down_count = 0;
var selMessageShortcut = "";

shortcut.add("N",function(e)
{
	// avoids problem related at ticket #1011
	e.preventDefault();
	var search_in_focus = false;
	var search_win = document.getElementById( 'QuickCatalogSearch_window_QuickCatalogSearch' );
	if ( search_win && search_win.style.visibility != 'hidden' )
		search_in_focus = true;

	if ( ! search_in_focus )
		new_message("new","null");
},{'disable_in_input':true});

shortcut.add("Esc",function(){
	var window_closed = false;
        var search_win = document.getElementById( 'window_QuickCatalogSearch' );
        
	if ($('.ZebraDialog.custom-zebra-filter').css('visibility') != 'visible')
		delete_border(get_msg_id(), 'false');
 },{'disable_in_input':false});

shortcut.add("I",function(){print_all();},{'disable_in_input':true});
shortcut.add("E",function(e){ if(e.preventDefault) e.preventDefault(); else event.returnValue = false; setTimeout(function(){exec_msg_action('forward');},50);},{'disable_in_input':true});
shortcut.add("R",function(e){ if(e.preventDefault) e.preventDefault(); else event.returnValue = false; setTimeout(function(){exec_msg_action('reply');},50);},{'disable_in_input':true});
shortcut.add("T",function(e){ if(e.preventDefault) e.preventDefault(); else event.returnValue = false; setTimeout(function(){var msg_id = get_msg_id(); if(msg_id) new_message("reply_to_all_with_history",msg_id);},50);},{'disable_in_input':true});
shortcut.add("O",function(e){ if(e.preventDefault) e.preventDefault(); else event.returnValue = false; show_head_option();},{'disable_in_input':true});
shortcut.add("M",function(e){ if(e.preventDefault) e.preventDefault(); else event.returnValue = false; show_address_full();},{'disable_in_input':true});

shortcut.add("Delete",function(e){
	if(currentTab == 0){
		proxy_mensagens.delete_msgs(current_folder, get_selected_messages(), 'null');
	}else{
		if (e.target.className.indexOf("box") == -1)
			proxy_mensagens.delete_msgs(openTab.imapBox[currentTab], currentTab.substring(0, selMessageShortcut.indexOf("_r")), 'null');
	}
}
,{'disable_in_input':true});

shortcut.add("Ctrl+Up",function(){exec_msg_action('previous');/*select_msg('null', 'up');*/},{'disable_in_input':true});
shortcut.add("Ctrl+Down",function(){exec_msg_action('next');/*select_msg('null', 'down');*/},{'disable_in_input':true});

if (is_ie || is_webkit)
{
//**********************
shortcut.add('up', function(e)
{
    if(currentTab == 0){
        $(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
        if($(".current_selected_shortcut_msg").prev().parents("#tbody_box").length)
            $(".current_selected_shortcut_msg").blur().removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
        $(".current_selected_shortcut_msg").addClass("selected_shortcut_msg").focus();
    }
},{'disable_in_input':true});


shortcut.add('down', function(e)
{
    if(currentTab == 0){
        $(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
        if($(".current_selected_shortcut_msg").next().parents("#tbody_box").length)
            $(".current_selected_shortcut_msg").blur().removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
        $(".current_selected_shortcut_msg").addClass("selected_shortcut_msg").focus();
    }
},{'disable_in_input':true});

shortcut.add('space', function(e)
{
    if(currentTab == 0){
        var allchecked = true;
        $.each( $(".selected_shortcut_msg"), function(index, value){
            if($(value).find(":checkbox").attr("checked") == undefined){
                allchecked = false;
            }
        });
        if(allchecked){
            $(".selected_shortcut_msg").removeClass("selected_msg").find('input[type="checkbox"]').removeAttr("checked");
        }else{
            //$(".current_selected_shortcut_msg").addClass("selected_msg").find('input[type="checkbox"]').attr("checked", true);
            $(".selected_shortcut_msg").addClass("selected_msg").find('input[type="checkbox"]').attr("checked", true);
        }
        $.each( $(".selected_shortcut_msg"), function(index, value){
            updateSelectedMsgs($(value).find(":checkbox").is(':checked'),$(value).attr("id"));
        });
        $(".current_selected_shortcut_msg").focus();
    }
},{'disable_in_input':true});

//****************

shortcut.add("Shift+down",function()
{    
    if(currentTab == 0){            
        //$(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
        if($(".current_selected_shortcut_msg").next().parents("#tbody_box").length)
            if($(".current_selected_shortcut_msg").next().hasClass("selected_shortcut_msg"))
                $(".current_selected_shortcut_msg").blur().removeClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
            else
                $(".current_selected_shortcut_msg").blur().addClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
        $(".current_selected_shortcut_msg").focus();
    }
},{'disable_in_input':true, 'propagate':false});

shortcut.add("Shift+up",function(){
    if(currentTab == 0){            
        //$(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
        if($(".current_selected_shortcut_msg").prev().parents("#tbody_box").length)
            if($(".current_selected_shortcut_msg").prev().hasClass("selected_shortcut_msg"))
                $(".current_selected_shortcut_msg").blur().removeClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
            else
                $(".current_selected_shortcut_msg").blur().addClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
        $(".current_selected_shortcut_msg").focus();
    }
},{'disable_in_input':true, 'propagate':false});
}
else
{
    shortcut.add("Up",function(){
        if (currentTab == 0){
	        $(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
	        if($(".current_selected_shortcut_msg").prev().parents("#tbody_box").length)
	            $(".current_selected_shortcut_msg").blur().removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
	        $(".current_selected_shortcut_msg").focus();
    	}
    },{'disable_in_input':true});

    shortcut.add("Down",function(){
        if (currentTab == 0){
	        $(".selected_shortcut_msg").removeClass("selected_shortcut_msg");
	        if($(".current_selected_shortcut_msg").next().parents("#tbody_box").length)
	            $(".current_selected_shortcut_msg").blur().removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
	        $(".current_selected_shortcut_msg").focus();
    	}
    },{'disable_in_input':true});

    shortcut.add("Shift+down",function(){
        if(currentTab == 0){            
            if($(".current_selected_shortcut_msg").next().parents("#tbody_box").length)
                if($(".current_selected_shortcut_msg").next().hasClass("selected_shortcut_msg"))
                    $(".current_selected_shortcut_msg").blur().removeClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
                else
                    $(".current_selected_shortcut_msg").blur().addClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").next().addClass("current_selected_shortcut_msg selected_shortcut_msg");
            $(".current_selected_shortcut_msg").focus();
        }
    },{'type':'keypress','disable_in_input':true, 'propagate':false});

    shortcut.add("Shift+up",function(){
        if(currentTab == 0){            
            if($(".current_selected_shortcut_msg").prev().parents("#tbody_box").length)
                if($(".current_selected_shortcut_msg").prev().hasClass("selected_shortcut_msg"))
                    $(".current_selected_shortcut_msg").blur().removeClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
                else
                    $(".current_selected_shortcut_msg").blur().addClass("selected_shortcut_msg").removeClass("current_selected_shortcut_msg").prev().addClass("current_selected_shortcut_msg selected_shortcut_msg");
            $(".current_selected_shortcut_msg").focus();
        }
    },{'type':'keypress', 'disable_in_input':true, 'propagate':false});
    shortcut.add('Space', function(e)
    {
        if(currentTab == 0){
            var allchecked = true;
            $.each( $(".selected_shortcut_msg"), function(index, value){
                if($(value).find(":checkbox").attr("checked") == undefined){
                    allchecked = false;
                }
            });
            if(allchecked){
                
                $(".selected_shortcut_msg").removeClass("selected_msg").find('input[type="checkbox"]').removeAttr("checked");
            }else{
                //$(".current_selected_shortcut_msg").addClass("selected_msg").find('input[type="checkbox"]').attr("checked", true);
                $(".selected_shortcut_msg").addClass("selected_msg").find('input[type="checkbox"]').attr("checked", true);
            }

            $.each( $(".selected_shortcut_msg"), function(index, value){
                updateSelectedMsgs($(value).find(":checkbox").is(':checked'),$(value).attr("id"));
            });
        }
    },{'disable_in_input':true});
}

shortcut.add("return",function(){
    if ( Element('border_id_0').className==='menu-sel' )
    {
        all_messages = Element('tbody_box').childNodes;
        for (var i=0; i < all_messages.length; i++)
        {
            if ( exist_className(all_messages[i], 'selected_shortcut_msg') )
            {
                Element("td_from_" + all_messages[i].id).onclick();
                return;
            }
        }
    }
},{'disable_in_input':true});

shortcut.add("f9",function(){
    Element("em_refresh_button").onclick();
    return;
},{'disable_in_input':false});

function exec_msg_action(action)
{
    var msg_id = get_msg_id();
    if (msg_id)
    {
        var msg_id = 'msg_opt_' + action + '_' + msg_id;
        try {Element(msg_id).onclick();}
    catch(e){/*alert(e);*/}
}
return;
}

function show_head_option()
{
    var msg_id = get_msg_id();
    if (msg_id) {
        var msg_id = 'option_hide_more_' + msg_id;
        try {Element(msg_id).onclick();}
    catch(e){/*alert(e);*/}
}
return;
}

function show_address_full()
{
    var toaddress = Element('div_toaddress_' + get_msg_id());	
    var ccaddress = Element('div_ccaddress_' + get_msg_id());

    if(toaddress &&  '' == toaddress.style.display) {
        show_div_address_full(get_msg_id(),'to');
    }
    else {
        if(toaddress)
            toaddress.style.display = '';
        var toaddress_full = Element('div_toaddress_full_' + get_msg_id());
        if(toaddress_full)
            toaddress_full.style.display = 'none';
    }		
    if(ccaddress &&  '' == ccaddress.style.display) {
        show_div_address_full(get_msg_id(),'cc');
    }
    else {
        if(ccaddress)
            ccaddress.style.display = '';
        var ccaddress_full = Element('div_ccaddress_full_' + get_msg_id());
        if(ccaddress_full)
            ccaddress_full.style.display = 'none';
    }
    return;
}

function get_msg_id()
{
    children = Element('border_tr').childNodes;

    for (var i=0; i<children.length; i++)
    {
        if ( (children[i].nodeName==='TD') && (children[i].className==='menu-sel') && children[i].id != 'border_id_0')
        {
            var border_selected = children[i];
            var msg_id = border_selected.id.replace("border_id_","");
            return msg_id;
        }
    }
    return false;
}

function select_msg(msg_number, keyboard_action, force_msg_selection)
{
/*
** Se caso for limpado toda a caixa de email,
** e adicionado um novo atalho de selecao.
** main.js on function refrash and line 629.
*/
$("#table_box").find("tr").attr("tabindex", -1);
$("#table_box").find("tr").css("outline", "none");

if(keyboard_action == "reload_msg"){
    if( $("#tbody_box .current_selected_shortcut_msg").length == 0 ){
        $("#tbody_box tr:first").addClass("current_selected_shortcut_msg selected_shortcut_msg");	
    }
}

shift_up_count = 0;
shift_down_count = 0;

if (msg_number != 'null') {

    if(Element(msg_number)){
        unselect_all_msgs();
        $("#tbody_box tr").removeClass("current_selected_shortcut_msg selected_shortcut_msg");
        $("#"+msg_number).addClass('current_selected_shortcut_msg selected_shortcut_msg');
    }

} else {
    var scrollMain = Element('divScrollMain_0');
    var selection_size = parseInt(preferences.line_height) + 10; 

    if( keyboard_action == 'down') {

        if(!Element("chk_box_select_all_messages").checked){

            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){

                if($(this).hasClass("selected_shortcut_msg") && $(this).next().length){
                    $(this).removeClass("selected_shortcut_msg current_selected_shortcut_msg");
                    $(this).next().addClass("selected_shortcut_msg current_selected_shortcut_msg");
                    return false;
                }
            });

        } else {

            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){

                if($(this).hasClass("current_selected") && $(this).next().length){
                    $(this).removeClass("current_selected");
                    $(this).removeClass("selected_shortcut_msg");
                    $(this).next().addClass("current_selected");
                    $(this).next().addClass("selected_shortcut_msg");
                    return false;
                }

            });
            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){
                if(!$(this).hasClass("current_selected"))
                    $(this).removeClass("selected_shortcut_msg");
            });
        }

    } else if( keyboard_action == 'up') {

        if(!Element("chk_box_select_all_messages").checked){

            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){

                if($(this).hasClass("selected_shortcut_msg") && $(this).prev().length){
                	$(this).removeClass("selected_shortcut_msg current_selected_shortcut_msg");
                    $(this).prev().addClass("selected_shortcut_msg current_selected_shortcut_msg");
                    return false;
                }

            });

        } else {

            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){

                if($(this).hasClass("current_selected") && $(this).prev().length){
                    $(this).removeClass("current_selected");
                    $(this).removeClass("selected_shortcut_msg");
                    $(this).prev().addClass("current_selected");
                    $(this).prev().addClass("selected_shortcut_msg");
                    return false;
                }

            });
            $("#divScrollMain_0").find("#tbody_box").find("tr").each(function(){
                if(!$(this).hasClass("current_selected"))
                    $(this).removeClass("selected_shortcut_msg");
            });

        }

    }
    return true;
}
}

function select_bottom_msg()
{
    all_messages = Element('tbody_box').childNodes;

    if ( exist_className(all_messages[all_messages.length-1], 'selected_shortcut_msg') )
        return;

    for (var i=all_messages.length-1; i >=0; i--)
    {
        if ( (exist_className(all_messages[i], 'selected_shortcut_msg')) && (i+1 <= all_messages.length-1) )
        {
            shift_down_count++;
            add_className(all_messages[i+1], 'selected_msg');
            break;
        }
    }
}

function select_top_msg()
{
    all_messages = Element('tbody_box').childNodes;

    if ( exist_className(all_messages[0], 'selected_shortcut_msg') )
        return;

    for (var i=0; i <=all_messages.length-1; i++)
    {
        if ( exist_className(all_messages[i], 'selected_shortcut_msg') )
        {
            shift_up_count++;
            add_className(all_messages[i-1], 'selected_msg');
            break;
        }
    }
}

function unselect_bottom_msg()
{
    all_messages = Element('tbody_box').childNodes;
    for (var i=all_messages.length-1; i >=0; i--)
    {
        if ( exist_className(all_messages[i], 'selected_shortcut_msg') )
        {
            shift_down_count--;
            remove_className(all_messages[i], 'selected_msg');
            break;
        }
    }
}

function unselect_top_msg()
{
    all_messages = Element('tbody_box').childNodes;
    for (var i=0; i <=all_messages.length-1; i++)
    {
        if ( exist_className(all_messages[i], 'selected_shortcut_msg') )
        {
            shift_up_count--;
            remove_className(all_messages[i], 'selected_msg');
            break;
        }
    }
}

function unselect_all_msgs()
{
    all_messages = Element('tbody_box').childNodes;
    for (var i=0; i <=all_messages.length-1; i++)
    {
        remove_className(all_messages[i], 'selected_msg');
    }
}
