var cp = new cpaint(); cp.set_transfer_mode('post'); cp.set_response_type('text'); var currObj; var spellingSuggestionsDiv = null; if(document.onclick)
{ var old_onclick = document.onclick; document.onclick = function(e)
{ checkClickLocation(e); old_onclick(e);}
}
else
{ document.onclick = checkClickLocation;}
if(window.onload)
{ var old_onload = window.onload; window.onload = function(e)
{ setupSpellCheckers(e); old_onload(e);}
}
else
{ window.onload = setupSpellCheckers;}
function setupSpellCheckers()
{ var textareas = document.getElementsByTagName('textarea'); var numSpellCheckers = 0; var tempSpellCheckers = Array(); for(var i=0; i < textareas.length; i++)
{ if(textareas[i].getAttribute("title") == "spellcheck" || textareas[i].getAttribute("title") == "spellcheck_icons")
{ tempSpellCheckers[numSpellCheckers] = textareas[i]; var tempWidth = tempSpellCheckers[numSpellCheckers].offsetWidth + 'px'; var tempHeight = tempSpellCheckers[numSpellCheckers].offsetHeight + 'px'; eval('spellCheckers' + numSpellCheckers + '= new ajaxSpell("spellCheckers' + numSpellCheckers + '", tempWidth, tempHeight, tempSpellCheckers[' + numSpellCheckers + '].getAttribute("accesskey"), "spellCheckDiv' + numSpellCheckers + '", tempSpellCheckers[' + numSpellCheckers + '].getAttribute("name"), tempSpellCheckers[' + numSpellCheckers + '].id, tempSpellCheckers[' + numSpellCheckers + '].title, tempSpellCheckers[' + numSpellCheckers + '].value);'); numSpellCheckers++;}
}
}; function ajaxSpell(varName, width, height, spellUrl, divId, name, id, title, value)
{ currObj = this; currObj.config = new Array(); currObj.config['varName'] = varName; currObj.config['width'] = width; currObj.config['height'] = height; currObj.config['spellUrl'] = spellUrl; currObj.config['divId'] = divId; currObj.config['name'] = name; currObj.config['id'] = id; currObj.config['title'] = title; currObj.config['value'] = value; currObj.config['value'] = currObj.config['value'].replace(/<br *\/?>/gi, "\n"); currObj.config['useIcons'] = false; if(currObj.config['title'] == "spellcheck_icons")
{ currObj.config['useIcons'] = true;}
spellContainer = document.createElement('DIV'); spellContainer.id = currObj.config['divId']; spellContainer.className = 'spell_container'; spellContainer.style.width = currObj.config['width']; oldElement = document.getElementById(currObj.config['id']); oldElement.parentNode.replaceChild(spellContainer, oldElement); currObj.controlPanelDiv = document.createElement('DIV'); currObj.controlPanelDiv.className = 'control_panel'; document.getElementById(currObj.config['divId']).appendChild(currObj.controlPanelDiv); currObj.actionSpan = document.createElement('SPAN'); currObj.actionSpan.className = "action"; currObj.actionSpan.id = "action"; if(currObj.config['useIcons'])
{ currObj.actionSpan.innerHTML = "<a class=\"check_spelling\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".spellCheck();\"><img src=\"images/spellcheck.png\" width=\"16\" height=\"16\" title=\"Check Spelling &amp; Preview\" alt=\"Check Spelling &amp; Preview\" border=\"0\" /></a>";}
else
{ currObj.actionSpan.innerHTML = "<a class=\"check_spelling\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".spellCheck();\">Check Spelling &amp; Preview</a>";}
currObj.controlPanelDiv.appendChild(currObj.actionSpan); currObj.statusSpan = document.createElement('SPAN'); currObj.statusSpan.className = "status"; currObj.statusSpan.id = "status"; currObj.statusSpan.innerHTML = ""; currObj.controlPanelDiv.appendChild(currObj.statusSpan); oldElement.value = currObj.config['value']; document.getElementById(currObj.config['divId']).appendChild(oldElement); currObj.objToCheck = document.getElementById(currObj.config['id']); currObj.spellingResultsDiv = null; ajaxSpell.prototype.spellCheck = spellCheck; ajaxSpell.prototype.spellCheck_cb = spellCheck_cb; ajaxSpell.prototype.showSuggestions = showSuggestions; ajaxSpell.prototype.showSuggestions_cb = showSuggestions_cb; ajaxSpell.prototype.replaceWord = replaceWord; ajaxSpell.prototype.switchText = switchText; ajaxSpell.prototype.switchText_cb = switchText_cb; ajaxSpell.prototype.resumeEditing = resumeEditing; ajaxSpell.prototype.resetSpellChecker = resetSpellChecker; ajaxSpell.prototype.resetAction = resetAction;}; function setCurrentObject(obj)
{ currObj = obj;}; function spellCheck_cb(new_data)
{ with(currObj); new_data = new_data.toString(); var isThereAMisspelling = new_data.charAt(0); new_data = new_data.substring(1); if(currObj.spellingResultsDiv)
{ currObj.spellingResultsDiv.parentNode.removeChild(spellingResultsDiv);}
currObj.spellingResultsDiv = document.createElement('DIV'); currObj.spellingResultsDiv.className = 'edit_box'; currObj.spellingResultsDiv.style.width = currObj.objToCheck.style.width; currObj.spellingResultsDiv.style.height = currObj.objToCheck.style.height; currObj.spellingResultsDiv.innerHTML = new_data; currObj.objToCheck.style.display = "none"; currObj.objToCheck.parentNode.insertBefore(currObj.spellingResultsDiv,currObj.objToCheck); currObj.statusSpan.innerHTML = ""; if(currObj.config['useIcons'])
{ currObj.actionSpan.innerHTML = "<a class=\"resume_editing\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".resumeEditing();\"><img src=\"images/page_white_edit.png\" width=\"16\" height=\"16\" title=\"Resume Editing\" alt=\"Resume Editing\" border=\"0\" /></a>";}
else
{ currObj.actionSpan.innerHTML = "<a class=\"resume_editing\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".resumeEditing();\">Resume Editing</a>";}
if(isThereAMisspelling != "1")
{ if(currObj.config['useIcons'])
{ currObj.statusSpan.innerHTML = "<img src=\"images/accept.png\" width=\"16\" height=\"16\" title=\"No Misspellings Found\" alt=\"No Misspellings Found\" border=\"0\" />";}
else
{ currObj.statusSpan.innerHTML = "No Misspellings Found";}
currObj.objToCheck.disabled = false;}
}; function spellCheck() { with(currObj); var query; if(currObj.spellingResultsDiv)
{ currObj.spellingResultsDiv.parentNode.removeChild(currObj.spellingResultsDiv); currObj.spellingResultsDiv = null;}
if(currObj.config['useIcons'])
{ currObj.actionSpan.innerHTML = "<img src=\"images/spellcheck.png\" width=\"16\" height=\"16\" title=\"Check Spelling &amp; Preview\" alt=\"Check Spelling &amp; Preview\" border=\"0\" />";}
else
{ currObj.actionSpan.innerHTML = "<a class=\"check_spelling\">Check Spelling &amp; Preview</a>";}
if(currObj.config['useIcons'])
{ currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Checking...\" alt=\"Checking...\" border=\"0\" />";}
else
{ currObj.statusSpan.innerHTML = "Checking...";}
query = currObj.objToCheck.value; query = query.replace(/\r?\n/gi, "<br />"); cp.call(currObj.config['spellUrl'], 'spellCheck', spellCheck_cb, query, currObj.config['varName']);}; function addWord(id)
{ var wordToAdd = document.getElementById(id).innerHTML; with(currObj); if(spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); spellingSuggestionsDiv = null;}
if(currObj.config['useIcons'])
{ currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Adding Word...\" alt=\"Adding Word...\" border=\"0\" />";}
else
{ currObj.statusSpan.innerHTML = "Adding Word...";}
cp.call(currObj.config['spellUrl'], 'addWord', addWord_cb, wordToAdd);}; function addWord_cb(returnedData)
{ alert(returnedData); with(currObj); currObj.statusSpan.innerHTML = ""; resumeEditing(); spellCheck();}; function checkClickLocation(e)
{ if(spellingSuggestionsDiv)
{ if(spellingSuggestionsDiv.ignoreNextClick){ spellingSuggestionsDiv.ignoreNextClick = false;}
else
{ var theTarget = getTarget(e); if(theTarget != spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); spellingSuggestionsDiv = null;}
}
}
return true;}; function getTarget(e)
{ var value; if(checkBrowser() == "ie")
{ value = window.event.srcElement;}
else
{ value = e.target;}
return value;}; function checkBrowser()
{ var theAgent = navigator.userAgent.toLowerCase(); if(theAgent.indexOf("msie") != -1)
{ if(theAgent.indexOf("opera") != -1)
{ return "opera";}
else
{ return "ie";}
}
else if(theAgent.indexOf("netscape") != -1)
{ return "netscape";}
else if(theAgent.indexOf("firefox") != -1)
{ return "firefox";}
else if(theAgent.indexOf("mozilla/5.0") != -1)
{ return "mozilla";}
else if(theAgent.indexOf("\/") != -1)
{ if(theAgent.substr(0,theAgent.indexOf('\/')) != 'mozilla')
{ return navigator.userAgent.substr(0,theAgent.indexOf('\/'));}
else
{ return "netscape";}
}
else if(theAgent.indexOf(' ') != -1)
{ return navigator.userAgent.substr(0,theAgent.indexOf(' '));}
else
{ return navigator.userAgent;}
}; function showSuggestions_cb(new_data)
{ with(currObj); spellingSuggestionsDiv.innerHTML = new_data; spellingSuggestionsDiv.style.display = 'block'; currObj.statusSpan.innerHTML = "";}; function showSuggestions(word, id)
{ with(currObj); if(currObj.config['useIcons'])
{ currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Searching...\" alt=\"Searching...\" border=\"0\" />";}
else
{ currObj.statusSpan.innerHTML = "Searching...";}
var x = findPosXById(id); var y = findPosYById(id); var scrollPos = 0; if(checkBrowser() != "ie")
{ scrollPos = currObj.spellingResultsDiv.scrollTop;}
if(spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv);}
spellingSuggestionsDiv = document.createElement('DIV'); spellingSuggestionsDiv.style.display = "none"; spellingSuggestionsDiv.className = 'suggestion_box'; spellingSuggestionsDiv.style.position = 'absolute'; spellingSuggestionsDiv.style.left = x + 'px'; spellingSuggestionsDiv.style.top = (y+16-scrollPos) + 'px'; spellingSuggestionsDiv.ignoreNextClick = true; document.body.appendChild(spellingSuggestionsDiv); cp.call(currObj.config['spellUrl'], 'showSuggestions', showSuggestions_cb, word, id);}; function replaceWord(id, newWord)
{ document.getElementById(id).innerHTML = trim(newWord); if(spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); spellingSuggestionsDiv = null;}
document.getElementById(id).className = "corrected_word";}; function switchText()
{ with(currObj); var text = currObj.spellingResultsDiv.innerHTML; text = text.replace(/<br *\/?>/gi, "~~~"); text = '*' + text; cp.call(currObj.config['spellUrl'], 'switchText', switchText_cb, text);}; function switchText_cb(new_string)
{ with(currObj); new_string = new_string.replace(/~~~/gi, "\n"); new_string = new_string.substr(1); currObj.objToCheck.style.display = "none"; currObj.objToCheck.value = new_string; currObj.objToCheck.disabled = false; if(currObj.spellingResultsDiv)
{ currObj.spellingResultsDiv.parentNode.removeChild(currObj.spellingResultsDiv); currObj.spellingResultsDiv = null;}
currObj.objToCheck.style.display = "block"; currObj.resetAction();}; function resumeEditing()
{ with(currObj); if(currObj.config['useIcons'])
{ currObj.actionSpan.innerHTML = "<a class=\"resume_editing\"><img src=\"images/page_white_edit.png\" width=\"16\" height=\"16\" title=\"Resume Editing\" alt=\"Resume Editing\" border=\"0\" /></a>";}
else
{ currObj.actionSpan.innerHTML = "<a class=\"resume_editing\">Resume Editing</a>";}
if(currObj.config['useIcons'])
{ currObj.statusSpan.innerHTML = "<img src=\"images/working.gif\" width=\"16\" height=\"16\" title=\"Working...\" alt=\"Working...\" border=\"0\" />";}
else
{ currObj.statusSpan.innerHTML = "Working...";}
if(spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); spellingSuggestionsDiv = null;}
currObj.switchText();}; function resetAction()
{ with(currObj); if(currObj.config['useIcons'])
{ currObj.actionSpan.innerHTML = "<a class=\"check_spelling\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".spellCheck();\"><img src=\"images/spellcheck.png\" width=\"16\" height=\"16\" title=\"Check Spelling &amp; Preview\" alt=\"Check Spelling &amp; Preview\" border=\"0\" /></a>";}
else
{ currObj.actionSpan.innerHTML = "<a class=\"check_spelling\" onclick=\"setCurrentObject(" + currObj.config['varName'] + "); " + currObj.config['varName'] + ".spellCheck();\">Check Spelling &amp; Preview</a>";}
currObj.statusSpan.innerHTML = "";}; function resetSpellChecker()
{ with(currObj); currObj.resetAction(); currObj.objToCheck.value = ""; currObj.objToCheck.style.display = "block"; currObj.objToCheck.disabled = false; if(currObj.spellingResultsDiv)
{ currObj.spellingResultsDiv.parentNode.removeChild(currObj.spellingResultsDiv); currObj.spellingResultsDiv = null;}
if(spellingSuggestionsDiv)
{ spellingSuggestionsDiv.parentNode.removeChild(spellingSuggestionsDiv); spellingSuggestionsDiv = null;}
currObj.statusSpan.style.display = "none";}; function findPosXById(object)
{ var curleft = 0; var obj = document.getElementById(object); if(obj.offsetParent)
{ while(obj.offsetParent)
{ curleft += obj.offsetLeft - obj.scrollLeft; obj = obj.offsetParent;}
}
else if(obj.x)
{ curleft += obj.x;}
return curleft;}; function findPosYById(object)
{ var curtop = 0;var curtop = 0; var obj = document.getElementById(object); if(obj.offsetParent)
{ while(obj.offsetParent)
{ curtop += obj.offsetTop - obj.scrollTop; obj = obj.offsetParent;}
}
else if(obj.y)
{ curtop += obj.y;}
return curtop;}; function trim(s)
{ while(s.substring(0,1) == ' ')
{ s = s.substring(1,s.length);}
while(s.substring(s.length-1,s.length) == ' ')
{ s = s.substring(0,s.length-1);}
return s;}; 