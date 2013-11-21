/* Source: Venus Project 
   http://www.jbanana.org/venus/  
	 License: LGPL-CC & Creative Commons */

function getJSObject(jsID, args0){
   var evento=getEvent(args0);
   var target=getDOMTarget(evento); 
   return getJSObjectFromTarget(jsID, target);
}

function getJSObjectFromTarget(jsID, target){
   var retorno = null;
   var tmp = target;
   while(retorno == null) {
      eval("retorno = tmp."+jsID);
      if((retorno==null||retorno==undefined) && tmp.getAttribute!=undefined ) retorno=tmp.getAttribute(jsID);
      tmp = tmp.parentNode;
   }
   if(retorno == null){
   	 tmp = tmp.offsetParent;
     eval("retorno = tmp."+jsID);
   }	
   return retorno;	
}


function getEvent(args0){
   var evento=args0;
   if(evento==null||evento==undefined) evento=event;
   return evento;
}

function getDOMTarget(evento){
   var target=evento.target;
   if(target == null || target==undefined) target=evento.srcElement;
   return target;
}

function getDOMTargetByEvent(args0){
   var evento=getEvent(args0);
   var target=getDOMTarget(evento);
   return target;
}
