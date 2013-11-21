/*
* Move the selected items from selectbox "from" to "to",
* where "from" and "to" are the selectbox names.
*/
function moveOptions(from, to) {
	fromList = eval('document.forms[0].' + from);
	toList = eval('document.forms[0].' + to);
	var sel = false;

	// walk through "from" list searching the selected items,
	// copy them to "to" and remove from "from"
	for (i = fromList.options.length - 1; i >= 0; i--)
	{
		var current = fromList.options[i];
		if (current.selected)
		{
			sel = true;
			txt = current.text;
			val = current.value;
			toList.options[toList.length] = new Option(txt,val);
			fromList.options[i] = null;
		}
	}

	if (!sel)
		alert ("Selecione um item!");

	// sort selectbox "to"
	sortOptions(to);
}

/*
* Populate selectbox with the array elements
*/
function populateSelect(objName, array) {
	obj = eval('document.forms[0].' + objName);
	obj.length = 0;

	// walk through array and add elements to the selectbox
	for (i = 0; i < array.length; i++) {
		if( isArray(array[i]) ) {
			obj.options[obj.length] = new Option(array[i].text, array[i].value);
		}
	}
}

/*
* Populate the selectboxes.
* @param objNameL Left selectbox name
* @param arrayL   Array with the elements of the left selectbox
* @param sortL    Boolean value to sort the left selectbox
* @param objNameR Right selectbox name
* @param arrayR   Array with the elements of the right selectbox
* @param sortR    Boolean value to sort the right selectbox
* @param execDiff This value indicates the direction of the diff
*                 Values:  LEFT  - remove the left elements found in the right selectbox
*                          RIGHT - remove the right elements found in the left selectbox
*                          NONE  - don't execute diff
*/
function populateSelects(objNameL, arrayL, sortL,
						 objNameR, arrayR, sortR,
						 execDiff) {
	if( isArray(arrayL) )
		populateSelect(objNameL, arrayL);

	if( isArray(arrayR) )
		populateSelect(objNameR, arrayR);

	if( sortL )
		sortOptions(objNameL);

	if( sortR )
		sortOptions(objNameR);

	switch(execDiff) {
		case "LEFT":
			selectDiff( objNameR, objNameL );
			break;
		case "RIGHT":
			selectDiff( objNameL, objNameR );
			break;
		default:
			break;
	}
}

/*
* Select all items in a selectbox.
*/
function selectAllOptions( objName ) {
	obj = eval('document.forms[0].' + objName);

	for(i = 0; i < obj.length; i++) {
		obj.options[i].selected = true;
	}
}

/*
* Check if the parameter is an array.
* Return true if it's an array, and false, if it's not.
*/
function isArray( obj ) {
	if (typeof obj == 'object') {
		var criterion = obj.constructor.toString().match(/array/i);
		return (criterion != null);
	}
	return false;
}

/*
* Get the values of a selectbox and return them in an array.
*/
function select2array(objName) {
	obj = eval('document.forms[0].' + objName);

	if(obj){
		array = new Array();

		for(i = 0; i < obj.length; i++) {
			array[i] = new Array(2);
			array[i].text  = obj.options[i].text;
			array[i].value = obj.options[i].value;
		}

		return array;
	}
	return null;
}

/*
* Copy the values of an array to a selectbox.
*/
function array2select(array, objName)
{
	obj = eval('document.forms[0].' + objName);

	/* remove all elements from the selectbox */
	for (i = obj.length - 1; i >= 0; i--)
		obj.options[i] = null;

	if (isArray(array))
		for (i = 0; i < array.length; i++)
			obj.options[i] = new Option(array[i].text,array[i].value);
}

/*
* Redefine the javascript sort function.
* We need to compare the attributes a.text and b.text.
*/
function sortFunction(a, b) {
	if(a.text > b.text)
		return 1;
	if(a.text < b.text)
		return -1;
	return 0;
}

/*
* Sort the elements of a selectbox.
*/
function sortOptions(objName) {
	arrTexts = select2array(objName);
	arrTexts.sort(sortFunction);
	array2select(arrTexts, objName);
}

/*
* Remove the accents of a string.
*/
function removeAccents( text ){
	var accents    = "áàãââÁÀÃÂéêÉÊíÍóõôÓÔÕúüÚÜçÇ";
	var traduction = "aaaaaAAAAeeEEiIoooOOOuuUUcC";
	var pos, chr;
	var newTxt = "";

	for (var i = 0; i < text.length; i++){
		chr = text.charAt(i);
		pos  = accents.indexOf(chr);
		if (pos > -1)
			newTxt += traduction.charAt(pos);
		else
			newTxt += text.charAt(i);
	}
	return newTxt;
}

function search(array, key) {
	for (i = 0; i < array.length; i++){
		if (key == array[i].text)
			return i;
	}
	return -1;

}

/*
* Binary search in a SORTED ARRAY.
* Returns the element position, or -1.
*/
function binarySearch(array, key) {
	var i, lo, hi;

	// get the first and the last positions of the array,
	lo = 0;
	hi = array.length - 1;

	while( lo <= hi ) {
		// get the middle value
		var mid = Math.floor((lo + hi) / 2);

		// if the key is in the 'mid' position, return 'mid'
		if( key == array[mid].text )
			return mid;
		// if key > 'mid' text,
		else if( key > array[mid].text)
			lo = mid + 1;
		// key < array[mid]
		else
			hi = mid - 1;
	}
	return -1;
}

/*
* Difference between two selectboxes. Remove the elements of the objName2
* that are found in objName1.
*/
function selectDiff(objName1, objName2) {
	array1 = select2array(objName1);

	obj2 = eval('document.forms[0].' + objName2);

	for( var i = array1.length - 1; i >= 0; i-- ) {
		array2 = select2array(objName2);
		j = search(array2, array1[i].text);
		if( j != -1 )
			obj2.options[j] = null;
	}
}
