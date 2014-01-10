/*************************************************************
 * AJAX Spell Checker - Version 2.8
 * (C) 2005 - Garrison Locke
 * 
 * This spell checker is built in the style of the Gmail spell
 * checker.  It uses AJAX to communicate with the backend without
 * requiring the page be reloaded.  If you use this code, please
 * give me credit and a link to my site would be nice.
 * http://www.broken-notebook.com.
 *
 * Copyright (c) 2005, Garrison Locke
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice, 
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice, 
 *     this list of conditions and the following disclaimer in the documentation 
 *     and/or other materials provided with the distribution.
 *   * Neither the name of the http://www.broken-notebook.com nor the names of its 
 *     contributors may be used to endorse or promote products derived from this 
 *     software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
 * OF SUCH DAMAGE.
 *
 *************************************************************/

var cp;
var currObj; //the current spell checker being used
var spellingSuggestionsDiv;
var old_onclick;
var positionEditor;
var areaEditor;
var abaEditor;
 

cp = new cpaint();
cp.set_transfer_mode('post');
cp.set_response_type('text');



function beginSpellCheck(){

    
    position = "body_position_" + currentTab;
    area = "body_" + currentTab;
    aba = currentTab;
    spellingSuggestionsDiv = null;
    positionEditor = position;
    areaEditor = area;
    abaEditor = aba;

    new setupSpellCheckers();      
    

} // close BeginSpellCheck


/*************************************************************
 * function setupSpellCheckers()
 *
 * This function obtain the iframe of the page (that is the area editor)
 * and then adds a spellchecker to the iframe.
 *************************************************************/
function setupSpellCheckers()
{
    
    var ifr = document.getElementById("body_" + currentTab);
    var numSpellCheckers = abaEditor;

    if(ifr.contentWindow)
        ifr=ifr.contentWindow.document;
    else
        ifr=ifr.contentDocument;

    if (document.addEventListener) {
        ifr.addEventListener("click", checkClickLocation , true); //add the click event
        ifr.addEventListener("blur", checkClickLocation , true); //add the click event
    }
    else {
        ifr.attachEvent("onclick", checkClickLocation); //add the click event
        ifr.attachEvent("onblur", checkClickLocation); //add the click event
    }


    var texto = ifr.body.innerHTML;

    var tempWidth = "99%";
    var tempHeight = "300";

    eval('spellCheckers' + numSpellCheckers + '= new ajaxSpell("spellCheckers' + numSpellCheckers + '", tempWidth, tempHeight, "spell_checker/spell_checker.php", positionEditor, "Nome", areaEditor, "Titulo", texto);');

    
}; // end setInit

function setupSpellChecker(){
        

        currObj = this;
       
        currObj.config               = new Array();
        
        currObj.config['divId'] = "body_position_" + currentTab;
        currObj.config['width'] = "99%";
        currObj.config['id'] = "body_" + currentTab;

        spellContainer = document.createElement('DIV');
	spellContainer.id = currObj.config['divId'];
	spellContainer.className = 'spell_container';
	spellContainer.style.width = currObj.config['width'];

	
	//generate the div to hold the spell checker controls
	currObj.controlPanelDiv = document.createElement('DIV');
	currObj.controlPanelDiv.className = 'control_panel';
        currObj.controlPanelDiv.id = 'control_panel_' + currentTab;
        document.getElementById(currObj.config['divId']).appendChild(currObj.controlPanelDiv);

       	//the span that toggles between spell checking and editing
	currObj.actionSpan = document.createElement('SPAN');
	currObj.actionSpan.className = "action";
	currObj.actionSpan.id = "action";        
	
	currObj.controlPanelDiv.appendChild(currObj.actionSpan);

	//the span that lets the user know of the status of the spell checker
	currObj.statusSpan = document.createElement('SPAN');
	currObj.statusSpan.className = "status";
	currObj.statusSpan.id = "status";
	currObj.statusSpan.innerHTML = "";
        currObj.controlPanelDiv.style.display = "none";
	currObj.controlPanelDiv.appendChild(currObj.statusSpan);

        //currObj.controlPanelDiv.parentNode.insertBefore(currObj.controlPanelDiv, document.getElementById("viewsource_rt_checkbox"));
   
      

 }


/*************************************************************
 * ajaxSpell(varName, width, height, spellUrl, divId, name, id)
 *
 * This is the constructor that creates a new ajaxSpell object.
 * All of it is dynamically generated so the user doesn't have
 * to add a bunch of crap to their site.
 *
 * @param varName The name of the variable that the object is
 *                assigned to (must be unique and the same as the variable)
 * @param width The width of the spell checker
 * @param height The height of the spell checker
 * @param spellUrl The url of the spell_checker.php code
 * @param divId The id of the div that the spell checker is 
 *              contained in (must be unique)
 * @param name The name of the textarea form element
 * @param id The id of the spell checker textarea (must be unique)
 *************************************************************/
function ajaxSpell(varName, width, height, spellUrl, divId, name, id, title, value)
{
        currObj = this;

	currObj.config               = new Array();         //the array of configuration options
	currObj.config['varName']    = varName;             //the name of the variable that this instance is stored in
	currObj.config['width']      = width;               //the width of the textarea
	currObj.config['height']     = height;              //the height of the textarea
	currObj.config['spellUrl']   = spellUrl;            //url to spell checker php code (spell_checker.php by default);
	currObj.config['divId']      = divId;               //the id of the div that the spell checker element is in
	currObj.config['name']       = name;                //what you want the form element's name to be
	currObj.config['id']         = id;                  //the unique id of the spell_checker textarea
	currObj.config['title']      = title;               //the title (specifies whether to use icons or not);
	currObj.config['value']      = value;               //the value of the text box when the page was loaded
        currObj.config['aba']      = abaEditor;

	//currObj.config['value']      = currObj.config['value'].replace(/<br *\/?>/gi, "\n"); // Comment from the original by Nathalie
	
	currObj.config['useIcons'] = false;
	
	if(currObj.config['title'] == "spellcheck_icons")
	{
		currObj.config['useIcons'] = true;
	}
	
	
        currObj.controlPanelDiv = document.getElementById('control_panel_' + currentTab);
        currObj.statusSpan = currObj.controlPanelDiv.childNodes[1];
        currObj.actionSpan = currObj.controlPanelDiv.childNodes[0] ;

	currObj.objToCheck              = document.getElementById(currObj.config['id']);      //the actual object we're spell checking
	currObj.spellingResultsDiv      = null;                                               // Auto-generated results div
		
	//prototypes for the ajaxSpell objects
	ajaxSpell.prototype.spellCheck           = spellCheck;
	ajaxSpell.prototype.spellCheck_cb        = spellCheck_cb;
	ajaxSpell.prototype.showSuggestions      = showSuggestions;
	ajaxSpell.prototype.showSuggestions_cb   = showSuggestions_cb;
	ajaxSpell.prototype.replaceWord          = replaceWord;
	ajaxSpell.prototype.switchText           = switchText;
	ajaxSpell.prototype.switchText_cb        = switchText_cb;
	ajaxSpell.prototype.resumeEditing        = resumeEditing;
	ajaxSpell.prototype.resetSpellChecker    = resetSpellChecker;
	ajaxSpell.prototype.resetAction          = resetAction;
        ajaxSpell.prototype.ignore               = ignore;


        //set the contentEditable to false for IE browsers
        var iframe= currObj.objToCheck;
        if(iframe.contentWindow)
            iframe=iframe.contentWindow.document;
        else
            iframe=iframe.contentDocument;

        var browser = checkBrowser();
        if (browser == "ie"){
            iframe.body.contentEditable="false" ;

        }

        
      
        
}; // end ajaxSpell


/*************************************************************
 * setCurrentObject
 *
 * This sets the current object to be the spell checker that
 * the user is currently using.
 *
 * @param obj The spell checker currently being used
 *************************************************************/
function setCurrentObject(obj)
{
	currObj  = obj;
        
        
}; // end setCurrentObject


/*************************************************************
 * showMenu
 *
 * This function is associated with the click event
 *  of all the span tags with correctd_word class.
 *
 *************************************************************/
function showMenu(){
    //var iframe= currObj.objToCheck;
    var iframe = document.getElementById("body_" + currentTab);
    if(iframe.contentWindow)
        iframe=iframe.contentWindow.document;
    else
        iframe=iframe.contentDocument;

    
    var browser = checkBrowser();
    if (browser == "ie"){        
        //iframe.body.contentEditable="true" ;
        //var ifr= currObj.objToCheck;
        var ifr= document.getElementById("body_" + currentTab);
        if(!e){var e = ifr.contentWindow.event;}
        if(!e.target){e.target = e.srcElement;}
        var evento = e.target.onclick.toString();
        
    }
    else {
        iframe.designMode = "off";
        iframe.designMode = "on";
        var evento = this.onclick.toString();
    }  
    evento = evento.replace("function onclick(event) {", "");
    evento = evento.replace("}", "");
    var array_func = evento.split(";");
    eval(array_func[0]);
    eval(array_func[1]);
    return false;         
  
//
}

/*************************************************************
 * replaceMenu
 *
 * This function is associated with the click event
 *  of all the div tags with suggestion class.
 *
 *************************************************************/
function replaceMenu(){     
    var iframe= currObj.objToCheck;
    if(iframe.contentWindow)
        iframe=iframe.contentWindow.document;
    else
        iframe=iframe.contentDocument;
    var browser = checkBrowser();
    if (browser == "ie"){
        iframe.body.contentEditable="false" ;
        var ifr= currObj.objToCheck;
        if(!e){var e = ifr.contentWindow.event;}
        if(!e.target){e.target = e.srcElement;}
        var evento = e.target.onclick.toString();
    }
    else {
        iframe.designMode = "off";
        iframe.designMode = "on";
        var evento = this.onclick.toString();
    }
    evento = evento.replace("function onclick(event) {", "");
    evento = evento.replace("}", "");
    var array_func = evento.split(";");
    eval(array_func[0]);
    return false;
}



/*************************************************************
 * spellCheck_cb
 *
 * This is the callback function that the spellCheck php function
 * returns the spell checked data to.  It sets the results div
 * to contain the markedup misspelled data and changes the status
 * message.  It also sets the width and height of the results
 * div to match the element that's being checked.
 * If there are no misspellings then new_data is the empty 
 * string and the status is set to "No Misspellings Found".
 *
 * @param new_data The marked up misspelled data returned from php.
 *************************************************************/
function spellCheck_cb(new_data)
{

        
	var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
        else
            ifr=ifr.contentDocument;

        with(currObj);

	new_data = new_data.toString();       
	var isThereAMisspelling = new_data.charAt(0);
	new_data = new_data.substring(1);

       	if(currObj.spellingResultsDiv)
	{
            ifr.removeChild(currObj.spellingResultsDiv.id);
	}

        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
           ifr=ifr.contentWindow.document;
        else
           ifr=ifr.contentDocument;
        ifr.body.innerHTML =  "";
        var cssLink = ifr.createElement("link");
        cssLink.href = "spell_checker/css/spell_checker.css";
        cssLink .rel = "stylesheet";
        cssLink .type = "text/css";
        
        // this is not being used
        currObj.spellingResultsDiv =  ifr.createElement('span');
	currObj.spellingResultsDiv.className = 'edit_box';
        currObj.spellingResultsDiv.id = 'edit_box'; //Added the id property - By Nathalie
	currObj.spellingResultsDiv.style.width =  "99%";//the width of the textarea
	currObj.spellingResultsDiv.style.height = 300;  //   = height; //currObj.objToCheck.style.height;
     	currObj.spellingResultsDiv.innerHTML = new_data;
        currObj.spellingResultsDiv.border = 0;
        //currObj.spellingResultsDiv.style.display = "none";
      	//currObj.objToCheck.style.display = "none";
	currObj.statusSpan.innerHTML = "";  

        //add the new_data to iframe and the css style
        ifr.body.innerHTML = "";
        ifr.body.innerHTML = new_data;
        ifr.body.appendChild(cssLink);


        // Obtain all the span tags which have highlight className
        //    and add the eventListener for the click - This event shows the suggestions menu
        var nodeArray = ifr.getElementsByTagName("span");
        var totArray = nodeArray.length;
        for (var i = 0 ; i < totArray ; i++) {
            var node = nodeArray[i];
            if (node.className == "highlight") {
                if (document.addEventListener) {                                        
                    ifr.getElementsByTagName("span")[i].addEventListener("click", showMenu , true);
                    ifr.getElementsByTagName("span")[i].contentEditable="false" ;
                }
                else {
                    ifr.getElementsByTagName("span")[i].attachEvent("onclick", showMenu );
                    ifr.getElementsByTagName("span")[i].contentEditable="false" ;
                }

            }
        }


        currObj.statusSpan.innerHTML = "";

        
	if(currObj.config['useIcons'])
	{
		currObj.actionSpan.innerHTML = "<a class=\"resume_editing\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".resumeEditing();\"><img src=\"images/page_white_edit.png\" width=\"16\" height=\"16\" title=\"Continuar Editando\" alt=\"Continuar Editando\" border=\"0\" /></a>";
	}
	else
	{
		currObj.actionSpan.innerHTML = "<a class=\"resume_editing\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".resumeEditing();\">Continuar Editando </a>";
	}
		
	if(isThereAMisspelling != "1")
	{
		if(currObj.config['useIcons'])
		{
			currObj.statusSpan.innerHTML = "<img src=\"images/accept.png\" width=\"16\" height=\"16\" title=\"Nenhum Erro Encontrado\" alt=\"Nenhum Erro Encontrado\" border=\"0\" />";
		}
		else
		{
			currObj.statusSpan.innerHTML = "Nenhum Erro Encontrado";
		}
		currObj.objToCheck.disabled = false;
	}

       currObj.controlPanelDiv.style.display = "block";

}; // end spellCheck_cb


/*************************************************************
 * spellCheck()
 *
 * The spellCheck javascript function sends the text entered by
 * the user in the text box to php to be spell checked.  It also
 * sets the status message to "Checking..." because it's currently
 * checking the spelling.
 *************************************************************/
function spellCheck() {


    if (document.getElementById("control_panel_" + currentTab).style.display == "block" ){
            alert("Corretor Executando. Clique em Continuar Editando.");
            return false ;
    }

//adicionado Paula
            languageId =  document.getElementById("selectLanguage");
            languageId = languageId[document.getElementById("selectLanguage").selectedIndex].value;

            with(currObj);
            var query;

            currObj.controlPanelDiv.style.display = "block";

            //disable the view source HTMl during the corretion
            var position = "body_position_" + currentTab;
            var posicao = document.getElementById(position);
            var nodeArray = posicao.getElementsByTagName("input");
            var totArray = nodeArray.length;            
            for (var i = 0 ; i < totArray ; i++) {
                var node = nodeArray[i];
                if (node.id == "viewsource_rt_checkbox") {
                    node.disabled = true;
                }
            }              

            if(currObj.spellingResultsDiv)
            {
                    currObj.spellingResultsDiv.parentNode.removeChild(currObj.spellingResultsDiv);
                    currObj.spellingResultsDiv = null;
            }

           
            // Obtain the HTML content from iframe (edit area)
             var ifr= currObj.objToCheck;
             if(ifr.contentWindow)
                ifr=ifr.contentWindow.document;
             else
                ifr=ifr.contentDocument;
             var texto = ifr.body.innerHTML;

            query = texto;
            //query = currObj.objToCheck.value;

            //query = query.replace(/\r?\n/gi, "<br />"); //  Commented from the original by Nathalie
            query = query.replace(/\r?\n/gi, " ");  // replace \n with " " - By Nathalie
            query = query.replace(/\t/gi, "     ");
            
            
            var browser = trim(checkBrowser());

            cp.call(currObj.config['spellUrl'], 'spellCheck', spellCheck_cb, query, currObj.config['varName'], languageId, browser);
 
}; // end spellcheck



/*************************************************************
 * addWord
 *
 * The addWord function adds a word to the custom dictionary
 * file.
 *
 * @param id The id of the span that contains the word to be added
 *************************************************************/
function addWord(id)
{
        var wordToAdd = document.getElementById(id).innerHTML;
	
	with(currObj);
	
	if(spellingSuggestionsDiv)
	{
		spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
		spellingSuggestionsDiv = null;
	}

        currObj.controlPanelDiv.style.display = "block";

	if(currObj.config['useIcons'])
	{
		currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Adding Word...\" alt=\"Adding Word...\" border=\"0\" />";
	}
	else
	{
		currObj.statusSpan.innerHTML = "Adding Word...";
	}
	
	cp.call(currObj.config['spellUrl'], 'addWord', addWord_cb, wordToAdd);

}; // end addWord

/*************************************************************
 * addWord_cb
 *
 * The addWord_cb function is a callback function that
 * PHP's addWord function returns to.  It recieves the
 * return status of the add to word to personal dictionary call.
 * It hides the status item.
 *
 * @param returnedData The return code from PHP.
 *************************************************************/
function addWord_cb(returnedData)
{
	with(currObj);
	currObj.statusSpan.innerHTML = "";
	resumeEditing();
	spellCheck();
}; // end addWord_cb



/*************************************************************
 * checkClickLocation(e)
 *
 * This function is called by the event listener when the user
 * clicks on anything.  It is used to close the suggestion div
 * if the user clicks anywhere that's not inside the suggestion
 * div.  It just checks to see if the name of what the user clicks
 * on is not "suggestions" then hides the div if it's not.
 *
 * @param e The event, in this case the user clicking somewhere on
 *          the page.
 *************************************************************/
function checkClickLocation(e)
{

   
    var browser = checkBrowser();

    if(spellingSuggestionsDiv)
	{
		// Bah.  There's got to be a better way to deal with this, but the click
		// on a word to get suggestions starts up a race condition between
		// showing and hiding the suggestion box, so we'll ignore the first
		// click. Problem with IE browser
                //alert(spellingSuggestionsDiv.ignoreNextClick);
		if(spellingSuggestionsDiv.ignoreNextClick && browser == "ie"){
                       spellingSuggestionsDiv.ignoreNextClick = false;
		}
		else
		{
                      
                      
		       var theTarget = getTarget(e);
                       //alert(theTarget.id);
			
			if(theTarget != spellingSuggestionsDiv && theTarget.className != "highlight")
			{
				if (spellingSuggestionsDiv){

                                    spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
                                    spellingSuggestionsDiv = null;
                                }
                               
                                if (browser == "ie"){
                                    //var iframe= currObj.objToCheck;
                                    var iframe= document.getElementById("body_" + currentTab);
                                    
                                    if(iframe.contentWindow)
                                        iframe=iframe.contentWindow.document;
                                    else
                                        iframe=iframe.contentDocument;
                                    iframe.body.contentEditable="false" ;
                                }
                         }
                }
        }
	//alert("Fim checkClickLocation");
	return true; // Allow other handlers to continue.
}; //end checkClickLocation


/*************************************************************
 * getTarget
 *
 * The get target function gets the correct target of the event.
 * This function is required because IE handles the events in
 * a different (wrong) manner than the rest of the browsers.
 *
 * @param e The target, in this case the user clicking somewhere on
 *     the page.
 *
 *************************************************************/
function getTarget(e)
{
        
	var value;
	if(checkBrowser() == "ie")
	{
            if(!e){var e = ifr.contentWindow.event;}
            if(!e.target){value = e.srcElement;}
            
	}
	else
	{
            value = e.target;
	}
	return value;
}; //end getTarget


/*************************************************************
 * checkBrowser()
 *
 * The checkBrowser function simply checks to see what browser
 * the user is using and returns a string containing the browser
 * type.
 *
 * @return string The browser type
 *************************************************************/
function checkBrowser()
{
        var theAgent = navigator.userAgent.toLowerCase();
	if(theAgent.indexOf("msie") != -1)
	{
		if(theAgent.indexOf("opera") != -1)
		{
			return "opera";
		}
		else
		{
			return "ie";
		}
	}
	else if(theAgent.indexOf("netscape") != -1)
	{
		return "netscape";
	}
	else if(theAgent.indexOf("firefox") != -1)
	{
		return "firefox";
	}
	else if(theAgent.indexOf("mozilla/5.0") != -1)
	{
		return "mozilla";
	}
	else if(theAgent.indexOf("\/") != -1)
	{
		if(theAgent.substr(0,theAgent.indexOf('\/')) != 'mozilla')
		{
			return navigator.userAgent.substr(0,theAgent.indexOf('\/'));
		}
		else
		{
			return "netscape";
		} 
	}
	else if(theAgent.indexOf(' ') != -1)
	{
		return navigator.userAgent.substr(0,theAgent.indexOf(' '));
	}
	else
	{ 
		return navigator.userAgent;
	}
}; // end checkBrowser


/*************************************************************
 * showSuggestions_cb
 *
 * The showSuggestions_cb function is a callback function that
 * php's showSuggestions function returns to.  It sets the 
 * suggestions table to contain the new data and then displays
 * the suggestions div.  It also adds the event listener - click -
 * for all the suggestions tags and sets te property to be not editable.
 *
 * @param new_data The suggestions table returned from php.
 *************************************************************/
function showSuggestions_cb(new_data)
{
	
        with(currObj);

        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
           ifr=ifr.contentWindow.document;
        else
           ifr=ifr.contentDocument;
        
	spellingSuggestionsDiv.innerHTML = new_data;
	spellingSuggestionsDiv.style.display = 'block';
	currObj.statusSpan.innerHTML = "";         

        var nodeArray = ifr.getElementsByTagName("div");
        var totArray = nodeArray.length;
        for (var i = 0 ; i < totArray ; i++) {
            var node = nodeArray[i];
            if (node.className == "suggestion" || node.className == "ignore" ) {
                 if (document.addEventListener){
                    ifr.getElementsByTagName("div")[i].addEventListener("click", replaceMenu , true);
                    ifr.getElementsByTagName("div")[i].contentEditable="false" ;
                 }
                 else {
                    ifr.getElementsByTagName("div")[i].attachEvent("onclick", replaceMenu);
                    ifr.getElementsByTagName("div")[i].contentEditable="false" ;
                 }
                 

            }
        }


}; //end showSuggestions_cb


/*************************************************************
 * showSuggestions
 *
 * The showSuggestions function calls the showSuggestions php
 * function to get suggestions for the misspelled word that the
 * user has clicked on.  It sets the status to "Searching...",
 * hides the suggestions div, finds the x and y position of the
 * span containing the misspelled word that user clicked on so 
 * the div can be displayed in the correct location, and then
 * calls the showSuggestions php function with the misspelled word
 * and the id of the span containing it.
 * This function is only executed when the word is in red (the word id misspelled).
 *
 * @param word The misspelled word that the user clicked on
 * @param id The id of the span that contains the misspelled word
 *************************************************************/
function showSuggestions(word, id)
{
        
         var ifr= currObj.objToCheck;
         if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
         else
            ifr=ifr.contentDocument;

         if (ifr.getElementById(id).className == "highlight"){ //show the suggestion box only if the words are in red

   
            languageId =  document.getElementById("selectLanguage");
            languageId = languageId[document.getElementById("selectLanguage").selectedIndex].value;


            with(currObj);
            if(currObj.config['useIcons'])
            {
                    currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Procurando...\" alt=\"Procurando...\" border=\"0\" />";
            }
            else
            {
                    currObj.statusSpan.innerHTML = "Procurando...";
            }

            var x = findPosXById(id);
            var y = findPosYById(id);

            var scrollPos = 0;
            if(checkBrowser() != "ie")
            {
                    //scrollPos = ifr.getElementById(currObj.spellingResultsDiv.id).scrollTop;
                    scrollPos = ifr.getElementsByTagName("body")[0].scrollTop;
            }

            if(spellingSuggestionsDiv)
            {
                    spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
            }
            spellingSuggestionsDiv = ifr.createElement('div');
            spellingSuggestionsDiv.style.display = "none";
            spellingSuggestionsDiv.className = 'suggestion_box';
            spellingSuggestionsDiv.style.position = 'absolute';
            spellingSuggestionsDiv.style.left = x + 'px';
            //spellingSuggestionsDiv.style.top = (y+16-scrollPos) + 'px'; //Removed the scrollPos because of the iframe - not necessary
            spellingSuggestionsDiv.style.top = (y +16) + 'px';
            spellingSuggestionsDiv.id = 'suggestion_box'; 
            spellingSuggestionsDiv.contentEditable="false" ;


            // Bah. There's got to be a better way to deal with this, but the click
            // on a word to get suggestions starts up a race condition between
            // showing and hiding the suggestion box, so we'll ignore the first
            // click.
            spellingSuggestionsDiv.ignoreNextClick = true;


            //document.body.appendChild(spellingSuggestionsDiv);
            ifr.body.appendChild(spellingSuggestionsDiv);


            cp.call(currObj.config['spellUrl'], 'showSuggestions', showSuggestions_cb, word, id, languageId);

            //alert("Fim do showSuggestions");
        } // end if - show the suggestion box only if the words are in red

}; // end showSuggestions



/*************************************************************
 * replaceWord
 *
 * The replaceWord function replaces the innerHTML of all the span tags
 * that contains the old word with the new word that the user selects
 * from the suggestion div.  It hides the suggestions div and changes the color of
 * the previously misspelled word to green to let the user know
 * it has been changed.  It then calls the switchText php function
 * with the innerHTML of the div to update the text of the text box.
 *
 * @param id The id of the element to be checked
 * @param newword The word the user selected from the suggestions div
 *                to replace the misspelled word.
 *************************************************************/
function replaceWord(id, newWord)
{


    var ifr= currObj.objToCheck;
    if(ifr.contentWindow)
        ifr=ifr.contentWindow.document;
    else
        ifr=ifr.contentDocument;

    var valorNo = trim(ifr.getElementById(id).innerHTML);

        
    // Obtain all span tags which have highlight className and contais the old word
    var nodeArray = ifr.getElementsByTagName("span");
    var totArray = nodeArray.length;
    var nodeRemove = new Array(totArray);
    var j = -1;
    for (var i = 0 ; i < totArray ; i++) {
        var node = nodeArray[i];
        if (node.className == "highlight" && trim(node.innerHTML) == valorNo ) {
            j++;
            nodeRemove[j] = node.id;
        }
    }

    //Replace the class of the span tags with highlight and the innerHTML with the new_word
    for (var i = 0 ; i <= j ; i++) {
        ifr.getElementById(nodeRemove[i]).innerHTML = newWord;
        ifr.getElementById(nodeRemove[i]).className = "corrected_word";
    }    


    if(spellingSuggestionsDiv)
    {
        spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
        spellingSuggestionsDiv = null;
    }   

    return false;           


}; // end replaceWord


/*************************************************************
 * ignore
 *
 * The ignore function removes the span tags and mantain
 * the original word. So, the word comes back to the original, without
 * css. 
 *
 * @param id The id of the element to be checked
 * @param word The original word.
 *************************************************************/

function ignore(id, word){

 
    var ifr= currObj.objToCheck;
    if(ifr.contentWindow)
        ifr=ifr.contentWindow.document;
    else
        ifr=ifr.contentDocument;

    var valorNo = trim(word);
    if(spellingSuggestionsDiv)
    {

        spellingSuggestionsDiv.style.display = 'none';
        spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
        spellingSuggestionsDiv = null;
    }

    var fake = ifr.createTextNode(valorNo);

    var parent =  ifr.getElementById(id).parentNode;


    parent.replaceChild(fake, ifr.getElementById(id));

    return false;


};



/*************************************************************
 * switchText
 *
 * The switchText function is a funtion is called when the user
 * clicks on resume editing (or submits the form).  It calls the
 * php function to switchText and uncomments the html and replaces
 * breaks and everything.  Here all the breaks that the user has
 * typed are replaced with %u2026.  Firefox does this goofy thing
 * where it cleans up the display of your html, which adds in \n's
 * where you don't want them.  So I replace the user-entered returns
 * with something unique so that I can rip out all the breaks that
 * the browser might add and we don't want.
 *************************************************************/
function switchText()
{       
        with(currObj);
        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
        else
            ifr=ifr.contentDocument;
     
        // Obtain all span tags which have highlight className or corrected_word className
        var nodeArray = ifr.getElementsByTagName("span");
        var totArray = nodeArray.length;
        var nodeRemove = new Array(totArray);
        var j = -1;

        //Saves in a array the span with className highlight or corrected_word when there are no more styles.
        for (var i = 0 ; i < totArray ; i++) {
            var node = nodeArray[i];
            //alert(node.style.fontFamily + node.style.fontWeight + node.style.fontStyle + node.style.textDecoration);
            if ((node.className == "highlight" || node.className == "corrected_word") && node.style.fontFamily=="" && node.style.fontWeight=="" && node.style.fontStyle=="" && node.style.textDecoration=="" ) {
                j++;
                nodeRemove[j] = node.id;                
            }
            else if (node.className == "highlight" || node.className == "corrected_word"){
                node.className = null;                
                node.onclick = null;
                node.contenteditable="true";
                node.id = null;
                if (document.addEventListener) {
                    node.removeEventListener("click", showMenu , true); //add the click event
                }
                else {
                    node.detachEvent("onclick", showMenu); //add the click event
                    node.detachEvent("onclick", checkClickLocation); //add the click event
                }
                


            }
        }

        //Remove span tags which have highlight className or corrected_word className
        for (var i = 0 ; i <= j ; i++) {
            if(ifr.getElementById(nodeRemove[i])){
                var valorNo = ifr.getElementById(nodeRemove[i]).innerHTML;
                var fake = ifr.createTextNode(valorNo);
                var parent =  ifr.getElementById(nodeRemove[i]).parentNode;
                parent.replaceChild(fake, ifr.getElementById(nodeRemove[i]));
            }
        }

	var text = ifr.body.innerHTML;

        text = text.replace(/&nbsp;/gi, "%u2026"); // Replace &nbsp; with the code %u2026
        text = text.replace(/\n/gi, ""); // remove \n
        text = text.replace(/\r/gi, ""); // remove \r 
        text = '*' + text;

        cp.call(currObj.config['spellUrl'], 'switchText', switchText_cb, text);
        
}; // end switchText


/*************************************************************
 * switchText_cb
 *
 * The switchText_cb function is a call back funtion that the
 * switchText php function returns to.  I replace all the %u2026's
 * with returns.  It then replaces the text in the text box with 
 * the corrected text fromt he div.
 *
 * @param new_string The corrected text from the div.
 *
 *************************************************************/
function switchText_cb(new_string)
{
    with(currObj);
    new_string = new_string.replace(/%u2026/gi, "&nbsp;"); // Replace the code %u2026 with &nbsp;
    new_string = new_string.replace(/~~~/gi, "\n");

    //replace href# for href - this was applied in spell_checher.php to block the redirection. Now, needs to be undone.
    new_string = new_string.replace(/href=\"#/gi, "href=\"");
    new_string = new_string.replace(/href='#/gi, "href='");   

    // Remove the prefixed asterisk that was added in switchText().
    new_string = new_string.substr(1);

    currObj.objToCheck.style.display = "none";
    var ifr= currObj.objToCheck;
    if(ifr.contentWindow)
        ifr=ifr.contentWindow.document;
    else
        ifr=ifr.contentDocument;
    var texto = ifr.body.innerHTML;

    ifr.body.innerHTML = "";
    ifr.body.innerHTML = new_string;
    currObj.spellingResultsDiv = null;

    currObj.objToCheck.disabled = false;


    currObj.objToCheck.style.display = "block";
    currObj.resetAction();
}; // end switchText_cb

 
/*************************************************************
 * resumeEditing
 *
 * The resumeEditing function is called when the user is in the
 * correction mode and wants to return to the editing mode.  It
 * hides the results div and the suggestions div, then enables
 * the text box and unhides the text box.  It also calls
 * resetAction() to reset the status message.
 *************************************************************/
function resumeEditing()
{
        
        with(currObj);

        currObj.controlPanelDiv.style.display = "block";
	if(currObj.config['useIcons'])
	{
		currObj.actionSpan.innerHTML = "<a class=\"resume_editing\"><img src=\"images/page_white_edit.png\" width=\"16\" height=\"16\" title=\"Continuar Editando\" alt=\"Continuar Editando\" border=\"0\" /></a>";
	}
	else
	{
		currObj.actionSpan.innerHTML = "<a class=\"resume_editing\">Continuar Editando</a>";
	}
	if(currObj.config['useIcons'])
	{
		currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Carregando...\" alt=\"Carregando...\" border=\"0\" />";
	}
	else
	{
		currObj.statusSpan.innerHTML = "Carregando...";
	}
       
	
	if(spellingSuggestionsDiv)
	{
		
                spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); 
		spellingSuggestionsDiv = null;
	}

        //set the contentEditable to true for IE browsers to continue editing
        var iframe= currObj.objToCheck;
        if(iframe.contentWindow)
        iframe=iframe.contentWindow.document;
        else
        iframe=iframe.contentDocument;

        var browser = checkBrowser();
        if (browser == "ie"){
            iframe.body.contentEditable="true" ;
        
        }


        //enable the view source HTMl after the corretion
        var position = "body_position_" + currentTab;
        var posicao = document.getElementById(position);
        var nodeArray = posicao.getElementsByTagName("input");
        var totArray = nodeArray.length;
        for (var i = 0 ; i < totArray ; i++) {
            var node = nodeArray[i];
            if (node.id == "viewsource_rt_checkbox") {
                node.disabled = false;

            }
        }


	
	currObj.switchText();
}; // end resumeEditing


/*************************************************************
 * resetAction
 *
 * The resetAction function just resets the status message to
 * the default action of "Check Spelling".
 *************************************************************/
function resetAction()
{


	with(currObj);	
        currObj.actionSpan.innerHTML = "";
	currObj.statusSpan.innerHTML = "";
        currObj.controlPanelDiv.style.display = "none";

}; // end resetAction


/*************************************************************
 * resetSpellChecker
 *
 * The resetSpellChecker function resets the entire spell checker
 * to the defaults.
 *************************************************************/
function resetSpellChecker()
{
        	
        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
        else
            ifr=ifr.contentDocument;


        with(currObj);
	currObj.resetAction();
	
	currObj.objToCheck.value = "";
	currObj.objToCheck.style.display = "block";
	currObj.objToCheck.disabled = false;
	
	if(currObj.spellingResultsDiv)
	{
		ifr.removeChild(currObj.spellingResultsDiv); 
		currObj.spellingResultsDiv = null;
	}
	if(spellingSuggestionsDiv)
	{
		spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);
		spellingSuggestionsDiv = null;
	}
	currObj.statusSpan.style.display = "none";
	
}; // end resetSpellChecker


/*************************************************************
 * findPosX
 *
 * The findPosX function just finds the X offset of the top left
 * corner of the object id it's given.
 *
 * @param object The id of the object that you want to find the 
 *               upper left X coordinate of.
 * @return int The X coordinate of the object
 *************************************************************/
    function findPosXById(object)
{
                
        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
        else
            ifr=ifr.contentDocument;
        var obj = ifr.getElementById(object);


        var curleft = 0;
	
	if(obj.offsetParent)
	{
		while(obj.offsetParent)
		{
			curleft += obj.offsetLeft - obj.scrollLeft;
			obj = obj.offsetParent;
		}
	}
	else if(obj.x)
	{
		curleft += obj.x;
	}
	return curleft;
}; // end findPosX


/*************************************************************
 * findPosY
 *
 * The findPosY function just finds the Y offset of the top left
 * corner of the object id it's given.
 *
 * @param object The id of the object that you want to find the 
 *               upper left Y coordinate of.
 * @return int The Y coordinate of the object
 *************************************************************/
function findPosYById(object)
{      
        var ifr= currObj.objToCheck;
        if(ifr.contentWindow)
            ifr=ifr.contentWindow.document;
        else
            ifr=ifr.contentDocument;
        var obj = ifr.getElementById(object);
    
	var curtop = 0;var curtop = 0;
	if(obj.offsetParent)
	{
		while(obj.offsetParent)
		{
			curtop += obj.offsetTop - obj.scrollTop;
			obj = obj.offsetParent;
		}
	}
	else if(obj.y)
	{
		curtop += obj.y;
	}
	return curtop;
}; // end findPosY


/*************************************************************
 * trim
 *
 * Trims white space from a string.
 *
 * @param s The string you want to trim.
 * @return string The trimmed string.
 *************************************************************/
function trim(s)
{
        
	while(s.substring(0,1) == ' ')
	{
    	s = s.substring(1,s.length);
	}
	while(s.substring(s.length-1,s.length) == ' ')
	{
    	s = s.substring(0,s.length-1);
	}
	return s;
}; // end trim