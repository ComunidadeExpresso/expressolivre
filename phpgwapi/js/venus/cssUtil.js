/* Source: Venus Project 
   http://www.jbanana.org/venus/  
	 License: LGPL-CC & Creative Commons */

var _ENABLE_HOVER_FUNCTION_TR = null;
var _DISABLE_HOVER_FUNCTION_TR = null;

var _ENABLE_HOVER_FUNCTION_TD = null;
var _DISABLE_HOVER_FUNCTION_TD = null;

function getEnableHoverRow() {
   if(_ENABLE_HOVER_FUNCTION_TR == null) {
      var fn = "var target = getDOMTargetByEvent(arguments[0]);";
      fn += "if(target.nodeName!='TD') return;";
      fn += "var DOM = target.parentNode;";
      fn += "DOM.oldClassName = DOM.className;";
      fn += "target.parentNode.className = getJSObjectFromTarget('hoverClassName', target);";
      _ENABLE_HOVER_FUNCTION_TR = new Function(fn);
   }
   return _ENABLE_HOVER_FUNCTION_TR;
}

function getDisableHoverRow() {
   if(_DISABLE_HOVER_FUNCTION_TR == null) {
      var fn = "var target = getDOMTargetByEvent(arguments[0]);";
      fn += "if(target.nodeName!='TD') return;";
      fn += "var oldClassName=getJSObjectFromTarget('oldClassName',target);";
      fn += "if(oldClassName!=undefined || target.parentNode.oldClassName!=null) target.parentNode.className=oldClassName;";
      fn += "else target.className='';";
      _DISABLE_HOVER_FUNCTION_TR = new Function(fn);
   }
   return _DISABLE_HOVER_FUNCTION_TR;
}
function getEnableHoverCell() {
   if(_ENABLE_HOVER_FUNCTION_TD == null) {
      var fn = "var target = getDOMTargetByEvent(arguments[0]);";
      fn += "if(target.nodeName!='TD') return;";
      fn += "target.oldClassName = target.className;";
      fn += "target.className = getJSObjectFromTarget('hoverClassName', target);";
      _ENABLE_HOVER_FUNCTION_TD = new Function(fn);
   }
   return _ENABLE_HOVER_FUNCTION_TD;
}

function getDisableHoverCell() {
   if(_DISABLE_HOVER_FUNCTION_TD == null) {
      var fn = "var target = getDOMTargetByEvent(arguments[0]);";
      fn += "if(target.nodeName!='TD') return;";
      fn += "var oldClassName=getJSObjectFromTarget('oldClassName', target);";
      fn += "if(oldClassName!=undefined || target.oldClassName!=null) target.className=oldClassName;";
      fn += "else target.className='';";
      _DISABLE_HOVER_FUNCTION_TD = new Function(fn);
   }
   return _DISABLE_HOVER_FUNCTION_TD;
}
