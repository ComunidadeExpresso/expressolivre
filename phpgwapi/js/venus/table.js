/* Source: Venus Project 
   http://www.jbanana.org/venus/  
	 License: LGPL-CC & Creative Commons */

//** ROW TYPES ******
var _TITLE_ROW = 0;
var _FIRST_ROW = 1;
var _SECOND_ROW = 2;
//** CCS CLASS NAMES
var _DEFAULT_TABLE_TITLE = "TableTitle";
var _DEFAULT_TABLE_EXPAND_CELL = "TableExpandCell";
var _DEFAULT_TABLE_ROW_1 = "TableRow1";
var _DEFAULT_TABLE_ROW_2 = "TableRow2";
var _DEFAULT_TABLE_ROW_1_HOVER = "TableRow1Hover";
var _DEFAULT_TABLE_ROW_2_HOVER = "TableRow2Hover";
var _DEFAULT_SUB_TABLE = "SubTable";
var _DEFAULT_SUB_TABLE_TITLE = "SubTableTitle";
var _DEFAULT_SUB_TABLE_ROW_1 = "SubTableRow1";
var _DEFAULT_SUB_TABLE_ROW_2 = "SubTableRow2";
var _DEFAULT_SUB_TABLE_ROW_1_HOVER = "SubTableRow1Hover";
var _DEFAULT_SUB_TABLE_ROW_2_HOVER = "SubTableRow2Hover";
//** CURSORS
var _CURSOR_NORMAL = "default";
var _CURSOR_TITLE = "n-resize";
var _CURSOR_BODY = "pointer";
//** OTHERS
var _DEFAULT_SORT_COMPARE = "lexicalCompare";
var _DEFAULT_TABLE_ROW_CLICK_FUNCTION_NAME = "tableRowClick";
var _DEFAULT_EXPAND_ROW_CLICK_FUNCTION_NAME = "expandRowClick";
//** objetos *****************************************

function Table(id) {
   //metodos
   this.newRow = newRow;
   this.newTitle = newTitle;
   this.sort = sort;
   this.setExpandColVisible = setExpandColVisible;
   this.setRowClickEnable = setRowClickEnable;
   this.setSelectionVisible = setSelectionVisible;
   this.getRowsBySelection = getRowsBySelection;
   this.removeRows = removeRows;
   this.repaintRows = repaintRows;
   //atributos
   this.rows = null;
   this.id = id;
   this.DOMtable = document.createElement("table");
   this.DOMtbody = document.createElement("tbody");
   this.titleRow = null;
   this.footerRow = null;
   this.lastSort = - 1;
   this.isRowClickEnable = false;
   this.isTitleRowVisible = true;
   this.isExpandColVisible = false;
   this.isSelectionVisible = false;
   this.DOMtable.style.cursor = _CURSOR_BODY;
   this.tableRowClickFunctionName = _DEFAULT_TABLE_ROW_CLICK_FUNCTION_NAME;
   this.expandRowClickFunctionName = _DEFAULT_EXPAND_ROW_CLICK_FUNCTION_NAME;
   this._TABLE_TITLE = _DEFAULT_TABLE_TITLE;
   this._TABLE_ROW_1 = _DEFAULT_TABLE_ROW_1;
   this._TABLE_ROW_2 = _DEFAULT_TABLE_ROW_2;
   this._TABLE_ROW_1_HOVER = _DEFAULT_TABLE_ROW_1_HOVER;
   this._TABLE_ROW_2_HOVER = _DEFAULT_TABLE_ROW_2_HOVER;
   this._TABLE_EXPAND_CELL = _DEFAULT_TABLE_EXPAND_CELL;
   //outros
   this.DOMtable.appendChild(this.DOMtbody);
   if(this.isTitleRowVisible) {
      this.titleRow = this.newRow();
      if(this.isSelectionVisible) this.DOMtr.removeChild(this.DOMselection);
      this.titleRow._setRowAs(_TITLE_ROW);
      this.footerRow = this.newRow();
      this.footerRow._setRowAs(_TITLE_ROW);
      this.rows = new Array();
   }
}

function Row(table) {
   //metodos
   this.newCell = newCell;
   this._setRowAs = _setRowAs;
   this._newSelectionCell = _newSelectionCell;
   //atributos   
   this.table = table;
   this.cels = new Array();
   this.DOMtr = document.createElement("tr");
   this.DOMexpand = document.createElement("td");
   this.DOMexpand.width = 10;
   this.DOMexpand.align = "center";
   this.DOMexpand.className = "TriangleCell";
   this.DOMexpand.row = this;
   this.DOMselection = this._newSelectionCell();
   this.DOMsubrow = document.createElement("tr");
   //outros
   if(this.table.footerRow == null) table.DOMtbody.appendChild(this.DOMtr);
   else table.DOMtbody.insertBefore(this.DOMtr, this.table.footerRow.DOMtr);
   this.DOMtr.appendChild(this.DOMexpand);
   this.DOMexpand.style.cursor = _CURSOR_NORMAL;
   this.DOMselection.style.cursor = _CURSOR_NORMAL;
   if(table.isExpandColVisible) {
      var fp = new Function("var row = getJSObject('row', arguments[0]);_expandRow(row);");
      this.DOMexpand.className = table._TABLE_EXPAND_CELL;
      var DOMtriangle = getShape("TRIANGLE_RIGHT");
      this.DOMexpand.onmousedown = fp;
      DOMtriangle.nextTriangleOrientation = - 1;
      DOMtriangle.row = this;
      this.DOMexpand.appendChild(DOMtriangle);
   }
   if(table.isSelectionVisible) {
      this.DOMtr.appendChild(this.DOMselection);
   }
   if(table.isRowClickEnable) {
      this.DOMtr.onmouseover = getEnableHoverRow();
      this.DOMtr.onmouseout = getDisableHoverRow();
   }
}

function Cell(row, isVisible) {
   //metodos
   this.addText = addText;
   //atributos   
   this.colIndex = null;
   this.compareFunctionName = null;
   this.DOMcontent = null;
   this.row = row;
   this.isVisible = isVisible;
   this.DOMtd = document.createElement("td");
   this.DOMtd.cell = this;
   //outros
   if(this.isVisible) row.DOMtr.appendChild(this.DOMtd);
   if(row.table.isSelectionVisible) {
      row.DOMtr.removeChild(row.DOMselection);
      row.DOMtr.appendChild(row.DOMselection);
   }
}
//** construtores ***************

function newTable(id) {
   return new Table(id);
}

function newSubTable(id) {
   var table = new Table(id);
   table._TABLE_TITLE = _DEFAULT_SUB_TABLE_TITLE;
   table._TABLE_ROW_1 = _DEFAULT_SUB_TABLE_ROW_1;
   table._TABLE_ROW_2 = _DEFAULT_SUB_TABLE_ROW_2;
   table._TABLE_ROW_1_HOVER = _DEFAULT_SUB_TABLE_ROW_1_HOVER;
   table._TABLE_ROW_2_HOVER = _DEFAULT_SUB_TABLE_ROW_2_HOVER;
   return table;
}

function newRow() {
   var tmpRow = new Row(this);
   if(this.rows != null) {
      if(this.rows.length % 2) {
         tmpRow._setRowAs(_SECOND_ROW);
      }
      else {
         tmpRow._setRowAs(_FIRST_ROW);
      }
      this.rows[this.rows.length] = tmpRow;
   }
   return tmpRow;
}

function newCell(isVisible) {
   var argumentsFunction;
   var nArg = newCell.arguments.length;
   if(nArg == 0) isVisible = true;
   var tmpCell = new Cell(this, isVisible);
   this.cels[this.cels.length] = tmpCell;
   var fn = "var cell=getJSObject('cell', arguments[0]);";
   fn += this.table.tableRowClickFunctionName + "(cell);";
   if(this.table.isRowClickEnable) tmpCell.DOMtd.onmousedown = new Function(fn);
   return tmpCell;
}

function newTitle(value, compareFunctionName, isVisible) {
   var cell = this.titleRow.newCell();
   var fcell = this.footerRow.newCell();
   cell.colIndex = this.titleRow.cels.length - 1;
   cell.DOMtd.className = this._TABLE_TITLE;
   cell.row.DOMtr.className = null;
   cell.addText(value);
   cell.DOMtd.style.cursor = _CURSOR_NORMAL;
   cell.DOMtd.onmousedown = null;
   fcell.colIndex = this.titleRow.cels.length - 1;
   fcell.DOMtd.className = this._TABLE_TITLE;
   fcell.row.DOMtr.className = null;
   fcell.DOMtd.style.cursor = _CURSOR_NORMAL;
   fcell.DOMtd.onmousedown = null;
   var nArg = newTitle.arguments.length;
   if(nArg >= 2){
      cell.compareFunctionName = compareFunctionName;
      cell.DOMtd.style.cursor  = _CURSOR_TITLE;
      cell.DOMtd.onmousedown   = _sortClick;
      //cell.DOMtd.onmouseover = getEnableHoverCell();
      //cell.DOMtd.onmouseout = getDisableHoverCell();  
      //cell.DOMtd.hoverClassName = this._TABLE_TITLE+"Hover";
   }
   if(nArg == 3) {
      if(!isVisible) this.titleRow.DOMtr.removeChild(cell.DOMtd);
   }
}

function addText(value) {
   this.DOMcontent = document.createTextNode(value);
   this.DOMtd.appendChild(this.DOMcontent);
}
//** metodos de objetos contextuais ************************

function removeRows(rowsArray) {
   var newArray = new Array();
   var addRow = null;
   for(var i = 0; i < this.rows.length; i++) {
      addRow = true;
      for(var ii = 0; ii < rowsArray.length; ii++) {
         if(rowsArray[ii] == this.rows[i]) {
            addRow = false;
            this.DOMtbody.removeChild(this.rows[i].DOMtr);
            break;
         }
      }
      if(addRow) newArray[newArray.length] = this.rows[i];
   }
   this.rows = newArray;
   if(this.rows.length > 0) this.repaintRows();
}

function sort(indexCol, compareFunction, invert) {
   var nArg = sort.arguments.length;
   if(nArg == 0) {
      indexCol = 0;
      compareFunction = _DEFAULT_SORT_COMPARE;
      invert = false;
   }
   else if(nArg == 1) {
      compareFunction = _DEFAULT_SORT_COMPARE;
      invert = false;
   }
   else if(nArg == 2) {
      invert = false;
   }
   if(invert) this.rows.sort(_invertedCompareHook(indexCol, compareFunction));
   else this.rows.sort(_compareHook(indexCol, compareFunction));
   this.repaintRows();
}

function repaintRows() {
   this.DOMtable.removeChild(this.DOMtbody);
   this.DOMtbody = document.createElement("tbody");
   this.DOMtable.appendChild(this.DOMtbody);
   this.DOMtbody.appendChild(this.titleRow.DOMtr);
   var DOMexpand = null;
   var DOMtriangle = null;
   for(index = 0; index < this.rows.length; index++) {
      if(index % 2) {
         this.rows[index].DOMtr.className = this._TABLE_ROW_2;
         this.rows[index].DOMtr.hoverClassName = this._TABLE_ROW_2_HOVER;
      }
      else {
         this.rows[index].DOMtr.className = this._TABLE_ROW_1;
         this.rows[index].DOMtr.hoverClassName = this._TABLE_ROW_1_HOVER;
      }
      this.rows[index].DOMtr.onmouseover = getEnableHoverRow();
      this.rows[index].DOMtr.onmouseout = getDisableHoverRow();
      this.DOMtbody.appendChild(this.rows[index].DOMtr);
      DOMexpand = this.rows[index].DOMexpand;
      if(DOMexpand.firstChild != null) DOMexpand.removeChild(DOMexpand.firstChild);
      if(this.isExpandColVisible) {
         DOMtriangle = getShape("TRIANGLE_RIGHT");
         DOMexpand.appendChild(DOMtriangle);
         DOMtriangle.nextTriangleOrientation = - 1;
         DOMtriangle.row = this.rows[index];
      }
   }
   this.DOMtbody.appendChild(this.footerRow.DOMtr);
}

function setExpandColVisible(isVisible) {
   if(this.isExpandColVisible == isVisible)
   return;
   this.isExpandColVisible = isVisible;
   var DOMexpand = null;
   var index = 0;
   var DOMtriangle = null;
   var fp = new Function("var row=getJSObject('row', arguments[0]);_expandRow(row);");
   if(isVisible) {
      for(; index < this.rows.length; index++) {
         DOMexpand = this.rows[index].DOMexpand;
         DOMexpand.className = this._TABLE_EXPAND_CELL;
         DOMtriangle = getShape("TRIANGLE_RIGHT");
         DOMexpand.appendChild(DOMtriangle);
         if(DOMexpand.onmousedown == null) {
            DOMexpand.onmousedown = fp;
            DOMtriangle.nextTriangleOrientation = - 1;
            DOMtriangle.row = this.rows[index];
         }
      }
   }else {
      for(; index < this.rows.length; index++) {
         DOMexpand = this.rows[index].DOMexpand;
         DOMexpand.removeChild(DOMexpand.firstChild);
      }
   }
}

function setRowClickEnable(isEnable) {
   this.isRowClickEnable = isEnable;
   var fn = null;
   if(isEnable) {
      fn = "var cell=getJSObject('cell', arguments[0]);";
      fn += this.tableRowClickFunctionName + "(cell);";
      fn = new Function(fn);
   }
   var index = 0;
   for(; index < this.rows.length; index++) {
      for(ii = 0; ii < this.rows[index].cels.length; ii++) {
         this.rows[index].cels[ii].DOMtd.onmousedown = fn;
         this.rows[index].DOMtr.onmouseover = getEnableHoverRow();
         this.rows[index].DOMtr.onmouseout = getDisableHoverRow();
      }
   }
}

function setSelectionVisible(isVisible) {
   if(this.isSelectionVisible == isVisible)
   return;
   this.isSelectionVisible = isVisible;
   var DOMexpand = null;
   var index = 0;
   var row = null;
   if(isVisible) {
      for(; index < this.rows.length; index++) {
         row = this.rows[index];
         row.DOMtr.appendChild(row.DOMselection);
      }
      row.table.titleRow.DOMtr.appendChild(row.table.titleRow.DOMselection);
      row.table.footerRow.DOMtr.appendChild(row.table.footerRow.DOMselection);
   } else {
      for(; index < this.rows.length; index++) {
         row = this.rows[index];
         row.DOMtr.removeChild(row.DOMselection);
      }
      row.table.titleRow.DOMtr.removeChild(row.table.titleRow.DOMselection);
      row.table.footerRow.DOMtr.removeChild(row.table.footerRow.DOMselection);
   }
}

function getRowsBySelection(isSelected) {
   if(!this.isSelectionVisible)
   return new Array();
   var DOMexpand = null;
   var index = 0;
   var row = null;
   var tmpArray = new Array();
   for(; index < this.rows.length; index++) {
      row = this.rows[index];
      if(row.DOMselection.firstChild.checked == isSelected) tmpArray[tmpArray.length] = row;
   }
   return tmpArray;
}
//** compare functions (sort)

function lexicalCompare(indexCol, obj1, obj2) {
   var v1 = obj1.cels[indexCol].DOMcontent.nodeValue;
   var v2 = obj2.cels[indexCol].DOMcontent.nodeValue;
   return(v1 < v2) ? - 1 :(v1 > v2) ? 1 : 0;
}

function numericCompare(indexCol, obj1, obj2) {
   var v1 = eval(obj1.cels[indexCol].DOMcontent.nodeValue);
   var v2 = eval(obj2.cels[indexCol].DOMcontent.nodeValue);
   return(v1 < v2) ? - 1 :(v1 > v2) ? 1 : 0;
}
//** metodos privados ******************************

function _newSelectionCell() {
   var DOMcell = document.createElement("td");
   var chk = document.createElement("input");
   chk.type = "checkbox";
   chk.id = "chk_" + this.table.id;
   chk.name = "chk_" + this.table.id;
   DOMcell.appendChild(chk);
   return DOMcell;
}

function _setRowAs(type) {
   var celsclass = null;
   if(type == _TITLE_ROW) {
      celsclass = this.table._TABLE_TITLE;
      this.DOMtr.className = null;
      this.DOMselection = document.createElement("td");
      this.DOMselection.className = celsclass;
      this.DOMselection.hoverClassName = celsclass + "Hover";
   }
   else if(type == _FIRST_ROW) {
      this.DOMtr.className = this.table._TABLE_ROW_1;
      this.DOMtr.hoverClassName = this.DOMtr.className + "Hover";    
   }
   else if(type == _SECOND_ROW) {
      this.DOMtr.className = this.table._TABLE_ROW_2;
      this.DOMtr.hoverClassName = this.DOMtr.className + "Hover";    
   }
   for(i = 0; i < this.cels.length; i++) {
      this.cels[i].DOMtd.className = celsclass;
      this.cels[i].DOMtd.hoverClassName = celsclass + "Hover";
   }
}

function _sortClick(args0) {
   var cell = getJSObject("cell", args0);
   var table = cell.row.table;
   if(table.lastSort == cell.colIndex) {
      table.sort(cell.colIndex, cell.compareFunctionName, true);
      table.lastSort = - 1;
   }else {
      table.sort(cell.colIndex, cell.compareFunctionName);
      table.lastSort = cell.colIndex;
   }
}

function _invertedCompareHook(indexCol, compareFunction) {
   var result = "this.indexCol=" + indexCol + ";\n";
   result += "return -1*(" + compareFunction + "(indexCol, arguments[0], arguments[1]));";
   return new Function(result);
}

function _compareHook(indexCol, compareFunction) {
   var result = "this.indexCol=" + indexCol + ";\n";
   result += "return " + compareFunction + "(indexCol, arguments[0], arguments[1]);";
   return new Function(result);
}

function _expandRow(row) {
   var lastTriangle = row.DOMexpand.firstChild;
   var name = "TRIANGLE_RIGHT";
   if(lastTriangle.nextTriangleOrientation < 0) name = "TRIANGLE_DOWN";
   var DOMtriangle = getShape(name);
   DOMtriangle.nextTriangleOrientation = - 1 * lastTriangle.nextTriangleOrientation;
   DOMtriangle.row = row;
   row.DOMexpand.removeChild(lastTriangle);
   row.DOMexpand.appendChild(DOMtriangle);
   var DOMtr = row.DOMtr.nextSibling;
   if(lastTriangle.nextTriangleOrientation < 0) {
      if(DOMtr == null) row.table.DOMtbody.appendChild(row.DOMsubrow);
      else row.table.DOMtbody.insertBefore(row.DOMsubrow, DOMtr);
   }else {
      row.table.DOMtbody.removeChild(row.DOMsubrow);
   }
   eval(row.table.expandRowClickFunctionName + "(row, " + lastTriangle.nextTriangleOrientation + ")");
}
