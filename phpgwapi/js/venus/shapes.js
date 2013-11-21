/* Source: Venus Project 
   http://www.jbanana.org/venus/  
	 License: LGPL-CC & Creative Commons */

//global vars
var _DEFAULT_PX_CLASS = "PX";

//prototypes
var _PROTOTYPE_TRIANGLE_UP = null;
var _PROTOTYPE_TRIANGLE_DOWN = null;
var _PROTOTYPE_TRIANGLE_LEFT = null;
var _PROTOTYPE_TRIANGLE_RIGHT = null;
var _PROTOTYPE_TRIANGLE_END = null;
var _PROTOTYPE_TRIANGLE_INI = null;
var _PROTOTYPE_ARC_Q1 = null;
var _PROTOTYPE_ARC_Q2 = null;
var _PROTOTYPE_ARC_Q3 = null;
var _PROTOTYPE_ARC_Q4 = null;
var _PROTOTYPE_STAR = null;
var _PROTOTYPE_X = null;
var _PROTOTYPE_SQUARE = null;
var _PROTOTYPE_UNDERLINE = null;
var _PROTOTYPE_PIN = null;
var _PROTOTYPE_FILTER = null;
var _PROTOTYPE_POSITIVE = null;
var _PROTOTYPE_NEGATIVE = null;

//public functions *****************************
function vMirror(DOMtable) {
   return _vMirror(DOMtable);
}

function bMirror(DOMtable) {
   return _bMirror(DOMtable);
}

function hMirror(DOMtable) {
   return _hMirror(DOMtable);
}

function rotate(DOMtable) {
   return _rotate(DOMtable);
}

function addUnderLine(DOMtable, className) {
   return _addUnderLine(DOMtable, className);
}

function getNegativeShape(type, className) {
   var nArg = getNegativeShape.arguments.length;
   if(nArg == 1) className = _DEFAULT_PX_CLASS;
   var tmp = getShape(type, className);
   if(tmp == null)
   return null;
   return _negative(tmp, className);
}

function getShape(type, className) {
   var nArg = getShape.arguments.length;
   if(nArg == 1) {
      className = _DEFAULT_PX_CLASS;
      var prototype = eval("_PROTOTYPE_" + type);
      if(prototype == null) {
         eval("_PROTOTYPE_" + type + " = _new" + type + "('" + className + "');");
         eval("prototype = _PROTOTYPE_" + type);
      }
      return prototype.cloneNode(true);
   }
   else if(nArg == 2) {
      eval("_PROTOTYPE_" + type + " = _new" + type + "('" + className + "');");
      eval("prototype = _PROTOTYPE_" + type);
      return prototype.cloneNode(true);
   }
   return null;
}
//objects ***************************************

function Corner() {
   //atributos
   this.DOMtable = document.createElement("table");
   this.DOMtbody = document.createElement("tbody");
   this.DOMq0 = document.createElement("td");
   this.DOMq1 = document.createElement("td");
   this.DOMq2 = document.createElement("td");
   this.DOMq3 = document.createElement("td");
   this.DOMq4 = document.createElement("td");
   this.DOMtop = document.createElement("td");
   this.DOMdown = document.createElement("td");
   this.DOMtr1 = null;
   this.DOMtr2 = null;
   this.DOMtr3 = null;
   this.className = _DEFAULT_PX_CLASS;
   //metodos	
   this.setQuadrant = setQuadrant;
   //outros
   this.DOMtable.cellPadding = "0";
   this.DOMtable.cellSpacing = "0";
   this.DOMtable.border = "0";
   this.DOMtable.appendChild(this.DOMtbody);
   //linha 1
   var DOMtr = document.createElement("tr");
   this.DOMtr1 = DOMtr;
   var DOMtrTmp = document.createElement("tr");
   this.DOMq1.width = "1";
   this.DOMq2.width = "1";
   this.DOMq1.className = this.className;
   this.DOMq2.className = this.className;
   DOMtr.appendChild(this.DOMq2);
   var DOMtd = this.DOMtop;
   DOMtd.className = this.className;
   DOMtd.width = "100%";
   DOMtr.appendChild(DOMtd);
   DOMtr.appendChild(this.DOMq1);
   DOMtd.appendChild(DOMtrTmp);
   this.DOMtbody.appendChild(DOMtr);
   //linha 2
   DOMtr = document.createElement("tr");
   this.DOMtr2 = DOMtr;
   DOMtr.className = this.className;
   DOMtd = this.DOMq0;
   this.DOMq0.className = this.className;
   DOMtd.colSpan = 3;
   DOMtr.appendChild(DOMtd);
   this.DOMtbody.appendChild(DOMtr);
   //linha 3
   DOMtr = document.createElement("tr");
   this.DOMtr3 = DOMtr;
   this.DOMq3.width = "1";
   this.DOMq4.width = "1";
   this.DOMq3.className = this.className;
   this.DOMq4.className = this.className;
   DOMtr.appendChild(this.DOMq3);
   DOMtd = this.DOMdown;
   DOMtd.className = this.className;
   DOMtd.width = "100%";
   DOMtr.appendChild(DOMtd);
   DOMtr.appendChild(this.DOMq4);
   this.DOMtbody.appendChild(DOMtr);
   return this;
}
//contextual functions ************************

function setQuadrant(DOMtable, numQuad, plusHeight) {
   var DOMq = eval("this.DOMq" + numQuad);
   var child = DOMq;
   while(child.firstChild != null) {
      if(child.firstChild != null) child.removeChild(child.firstChild);
   }
   var DOMtableTmp = null;
   if(plusHeight != undefined) {
      var DOMtd = document.createElement("td");
      DOMtd.appendChild(document.createTextNode(""));
      DOMtd.style.width="9px";
      DOMtableTmp = document.createElement("table");
      DOMtd.height = plusHeight;
      DOMtableTmp.width = "9px";
      DOMtableTmp.className = this.className;
      DOMtd.className = this.className;
      DOMtableTmp.cellPadding = "0";
      DOMtableTmp.cellSpacing = "0";
      DOMtableTmp.border = "0";
      DOMtableTmp.appendChild(document.createElement("tbody"));
      DOMtableTmp.firstChild.appendChild(document.createElement("tr"));
      DOMtableTmp.firstChild.firstChild.appendChild(DOMtd);
   }
   DOMq.className = null;
   if(numQuad > 2 && DOMtableTmp != null) DOMq.appendChild(DOMtableTmp);
   DOMq.appendChild(DOMtable);
   if(numQuad < 3 && DOMtableTmp != null) DOMq.appendChild(DOMtableTmp);
}
//private functions ****************************

function _vMirror(DOMtable) {
   var DOMtbody = DOMtable.firstChild;
   var DOMtr = DOMtbody.lastChild;
   var DOMtmp = null;
   while(DOMtr != null) {
      DOMtmp = DOMtr.previousSibling;
      if(DOMtr != null) {
         DOMtbody.removeChild(DOMtr);
         DOMtbody.appendChild(DOMtr);
         DOMtr = DOMtmp;
      }
   }
   return DOMtable;
}

function _bMirror(DOMtable) {
   var DOMtbody = DOMtable.firstChild;
   var DOMtr = DOMtbody.lastChild;
   var DOMtd = null;
   var DOMtmp1 = null;
   var DOMtmp2 = null;
   while(DOMtr != null) {
      DOMtmp1 = DOMtr.previousSibling;
      if(DOMtr != null) {
         DOMtbody.removeChild(DOMtr);
         DOMtbody.appendChild(DOMtr);
         DOMtd = DOMtr.lastChild;
         while(DOMtd != null) {
            DOMtmp2 = DOMtd.previousSibling;
            if(DOMtd != null) {
               DOMtr.removeChild(DOMtd);
               DOMtr.appendChild(DOMtd);
               DOMtd.moved = true;
            }
            DOMtd = DOMtmp2;
         }
         DOMtr = DOMtmp1;
      }
   }
   return DOMtable;
}

function _hMirror(DOMtable) {
   var DOMtbody = DOMtable.firstChild;
   var DOMtr = DOMtbody.lastChild;
   var DOMtd = null;
   var DOMtmp1 = null;
   var DOMtmp2 = null;
   while(DOMtr != null) {
      DOMtmp1 = DOMtr.previousSibling;
      if(DOMtr != null) {
         DOMtd = DOMtr.lastChild;
         while(DOMtd != null) {
            DOMtmp2 = DOMtd.previousSibling;
            if(DOMtd != null) {
               DOMtr.removeChild(DOMtd);
               DOMtr.appendChild(DOMtd);
               DOMtd.moved = true;
            }
            DOMtd = DOMtmp2;
         }
         DOMtr = DOMtmp1;
      }
   }
   return DOMtable;
}

function _rotate(DOMtable) {
   var array = new Array();
   var DOMbody = DOMtable.firstChild;
   var tmpArray = null;
   var DOMtd = null;
   var DOMrow = DOMbody.lastChild;
   while(DOMrow.childNodes.length > 0) {
      tmpArray = new Array();
      array[array.length] = tmpArray;
      while(DOMrow != null) {
         DOMtd = DOMrow.firstChild;
         tmpArray[tmpArray.length] = DOMtd;
         DOMrow.removeChild(DOMtd);
         DOMrow = DOMrow.previousSibling;
      }
      DOMrow = DOMbody.lastChild;
   }
   DOMtable.removeChild(DOMbody);
   DOMbody = document.createElement("tbody");
   DOMtable.appendChild(DOMbody);
   for(var i = 0; i < array.length; i++) {
      DOMrow = document.createElement("tr");
      DOMbody.appendChild(DOMrow);
      for(var ii = 0; ii < array[i].length; ii++) {
         if(array[i][ii] != null) DOMrow.appendChild(array[i][ii]);
      }
   }
   DOMtable.height=array.length;
   DOMtable.width=array[0].length;
   return DOMtable;
}

function _negative(DOMtable, className) {
   var DOMtbody = DOMtable.firstChild;
   var DOMtr = DOMtbody.lastChild;
   var DOMtd = null;
   while(DOMtr != null) {
      DOMtd = DOMtr.lastChild;
      DOMtr.className = null;
      while(DOMtd != null) {
         if(DOMtd.className == null || DOMtd.className == "") 
         	DOMtd.className = className;
         else DOMtd.className = null;
         	DOMtd = DOMtd.previousSibling;
      }
      DOMtr = DOMtr.previousSibling;
   }
   return DOMtable;
}

function _change(DOMtable, className) {
   var DOMtbody = DOMtable.firstChild;
   var DOMtr = DOMtbody.lastChild;
   var DOMtd = null;
   while(DOMtr != null) {
      DOMtd = DOMtr.lastChild;      
      while(DOMtd != null) {
         if(DOMtd.className != null && DOMtd.className != "") 
         	DOMtd.className = className;
         else
         	DOMtd.className = "";
         DOMtd = DOMtd.previousSibling;         
      }
      DOMtr = DOMtr.previousSibling;
   }
   return DOMtable;
}
function _addUnderLine(DOMtable, className) {
   var DOMtr = DOMtable.firstChild.firstChild;
   DOMtr = DOMtr.cloneNode(true);
   DOMtr.className = className;
   DOMtable.firstChild.appendChild(DOMtr);
   return DOMtable;
}

function _newTRIANGLE_INI(triangleClass) {
   return rotate(addUnderLine(getShape("TRIANGLE_DOWN", triangleClass), triangleClass));
}

function _newTRIANGLE_END(triangleClass) {
   return hMirror(getShape("TRIANGLE_INI", triangleClass));
}

function _newTRIANGLE_LEFT(triangleClass) {
   return hMirror(getShape("TRIANGLE_RIGHT", triangleClass));
}

function _newTRIANGLE_RIGHT(triangleClass) {
   return rotate(getShape("TRIANGLE_UP", triangleClass));
}

function _newTRIANGLE_UP(triangleClass) {
   return vMirror(getShape("TRIANGLE_DOWN", triangleClass));
}

function _newTRIANGLE_DOWN(className) {
   var condiction = "i==0 ||";
   condiction += "i == 1 && (ii<8 && ii>0)||";
   condiction += "i == 2 && (ii<7 && ii>1)||";
   condiction += "i == 3 && (ii<6 && ii>2)||";
   condiction += "i == 4 && (ii<5 && ii>3)";
   return _newGeneral(className, condiction, "TRIANGLE", 5, 9);
}

function _newTD(triangleClass) {
   var DOMtd = document.createElement("td");
   DOMtd.width = 1;
   DOMtd.height = 1;
   DOMtd.className = triangleClass;
   return DOMtd;
}

function _newARC_Q2(className) {
   return hMirror(getShape("ARC_Q1", className));
}

function _newARC_Q3(className) {
   return bMirror(getShape("ARC_Q1", className));
}

function _newARC_Q4(className) {
   return vMirror(getShape("ARC_Q1", className));
}

function _newARC_Q1(className) {
   var condiction = "!((i == 0 && ii > 2) ||(i == 1 && ii > 4) ||(i == 2 && ii > 5) ||(i == 3 && ii > 6) ||(i > 3 && i < 6 && ii > 7))";
   return _newGeneral(className, condiction, "ARC", 9);
}

function _newX(className) {
   var condiction = "(i!=0 && i!=8) && (i - ii) == 0 ||";
   condiction += "i == 1 && ii == 7 ||";
   condiction += "i == 2 && ii == 6 ||";
   condiction += "i == 3 && ii == 5 ||";
   condiction += "i == 5 && ii == 3 ||";
   condiction += "i == 6 && ii == 2 ||";
   condiction += "i == 7 && ii == 1";
   return _newGeneral(className, condiction, "X", 9);
}

function _newSQUARE(className) {
   var condiction = "((i!=0 && i!=8) && (ii==1 || ii==7)) ||";
   condiction += "((ii!=0 && ii!=8) && (2==i || i==1 || i==7))";
   return _newGeneral(className, condiction, "SQUARE", 9);
}

function _newUNDERLINE(className) {
   var condiction = "(5==i||6==i||i==7) && (ii!=0&&ii!=8)";
   return _newGeneral(className, condiction, "UNDERLINE", 9);
}

function _newSTAR(className) {
   var condiction = "i == 4 || ii == 4 || (i!=0 && i!=8) && (i - ii) == 0 ||";
   condiction += "i == 1 && ii == 7 ||";
   condiction += "i == 2 && ii == 6 ||";
   condiction += "i == 3 && ii == 5 ||";
   condiction += "i == 5 && ii == 3 ||";
   condiction += "i == 6 && ii == 2 ||";
   condiction += "i == 7 && ii == 1";
   return _newGeneral(className, condiction, "STAR", 9);
}

function _newPIN(className) {
   var condiction = "((i!=0 && i!=4) && (ii==1 || ii==3)) ||";
   condiction += "((ii!=0 && ii!=4) && (i==1 || i==3))";
   return _newGeneral(className, condiction, "PIN", 5);
}


function _newFILTER(className) {
   var condiction = "i==0 ||";
   condiction += "(i == 1 && (ii<8 && ii>0))||";
   condiction += "(i == 2 && (ii<7 && ii>1))||";
   condiction += "(i == 3 && (ii<6 && ii>2))||";
   condiction += "(i == 4 && (ii<5 && ii>3))||";
   condiction += "(i >= 5 && ii == 4)||";
   condiction += "((i>=2 && i<5) && ii==8) ||";
   condiction += "(i == 5 && (ii>5 && ii<8))";
   return _newGeneral(className, condiction, "FILTER", 7, 9);
}

function _newPOSITIVE(className) {
   var condiction = "(i==3||i==4||ii==3||ii==4)";
   return _newGeneral(className, condiction, "POSITIVE", 8,8);
}

function _newNEGATIVE(className) {
   var condiction = "(i==3||i==4)";
   return _newGeneral(className, condiction, "NEGATIVE", 8,8);
}

function _newGeneral(className, condiction, id, loop, loop2) {
   if(loop2==undefined||loop2==null) loop2 = loop;	
   var DOMtable = document.createElement("table");
   var DOMtbody = document.createElement("tbody");
   DOMtable.appendChild(DOMtbody);
   DOMtable.id = id;
   DOMtable.valign = "top";
   DOMtable.cellPadding = "0";
   DOMtable.cellSpacing = "0";
   DOMtable.border = "0";
   DOMtable.width = loop2;
   DOMtable.height = loop;
   DOMtable.appendChild(DOMtbody);
   var DOMtd = null;
   var DOMtr = null;
   for(var i = 0; i < loop; i++) {
      DOMtr = document.createElement("tr");
      DOMtbody.appendChild(DOMtr);
      for(var ii = 0; ii < loop2; ii++) {
         DOMtd = document.createElement("td");
         DOMtd.width = 1;
         DOMtd.height = 1;
         DOMtr.appendChild(DOMtd);
         if(eval(condiction)) DOMtd.className = className;
      }
   }
   return DOMtable;
}
