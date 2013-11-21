/*
* Add selected item of selectbox "from" to "to" and
* disable it in "from"
*/
function addOption(from, to, sort) {
	fromList = eval('document.forms[0].' + from);
	toList   = eval('document.forms[0].' + to);
	button   = eval('document.forms[0].btn_' + from);

	if(fromList.selectedIndex != -1 && !fromList.options[fromList.selectedIndex].disabled){
		txt = new String(fromList.options[fromList.selectedIndex].text);
		val = fromList.options[fromList.selectedIndex].value;

		toList.options[toList.length] = new Option(txt,val);
		fromList.options[fromList.selectedIndex].disabled = true;
		// if is IE, emulates disabled
		if(navigator.appName == "Microsoft Internet Explorer")
			fromList.options[fromList.selectedIndex].style.color = "gray";
		fromList.selectedIndex = -1;
		$(button).disable();

		if(sort){
			// sort selectbox "to"
			sortOptions(to);
		}
	}
}

/*
* Remove selected options of selectbox "from" and enable them in selectbox "to"
*/
function removeOptions(from, to) {
	fromList = eval('document.forms[0].' + from);
	toList = eval('document.forms[0].' + to);
	// get color style of the selected option of fromList
	var color = fromList.options[fromList.selectedIndex].style.color;
	// walk through "from" list searching the selected items,
	// enable them in the "to" and remove from "from"
	for (i = 0; i < fromList.options.length; i++) {
		var current = fromList.options[i];
		if (current.selected) {
			val = current.value;
			for (j = 0; j < toList.options.length; j++){
				if (toList.options[j].value == val){
					toList.options[j].disabled = false;
					// if is IE, emulates enabled
					if(navigator.appName == "Microsoft Internet Explorer")
						toList.options[j].style.color = color;
					break;
				}
			}
			fromList.options[i] = null;
			i--;
		}
	}
	button = eval('document.forms[0].btn_' + from);
	$(button).disable();
}

/*
* Check if there is a selected item in the selectbox objName and enable or disable its related button
*/
function enableButton(objName)
{
	select = eval('document.forms[0].' + objName);
	var buttonName = 'btn_' + objName;
	button = eval('document.forms[0].' + buttonName);
    if (select.selectedIndex != -1 && select.options[select.selectedIndex].value != -1){
		$(button).enable();
	} else {
		$(button).disable();
	}
}


/*
* Populate selectbox with the array elements
*/
function populateSelect(objName, array) {
	obj = eval('document.forms[0].' + objName);

	while(obj.firstChild)
		obj.removeChild(obj.firstChild);

	// walk through array and add elements to the selectbox
	for (i = 0; i < array.length; i++) {
		if( isArray(array[i]) ) {
			obj.options[obj.length] = new Option(array[i].text, array[i].value);
		}
	}
}

/*
* Populate the selectboxes.
* @param objNameT      Top selectbox name
* @param arrayT        Array with the elements of the top selectbox
* @param sortT         Boolean value to sort the top selectbox
* @param objNameB      Bottom selectbox name
* @param arrayB        Array with the elements of the bottom selectbox
* @param sortB         Boolean value to sort the bottom selectbox
* @param bool execDiff This value indicates if the user wants to execute the diff function
*/
function populateSelects(objNameT, arrayT, sortT,
						 objNameB, arrayB, sortB,
						 execDiff) {
	if( isArray(arrayT) )
		populateSelect(objNameT, arrayT);

	if( isArray(arrayB) )
		populateSelect(objNameB, arrayB);

	if( sortT )
		sortOptions(objNameT);

	if( sortB )
		sortOptions(objNameB);

	if(execDiff)
		selectDiffMultiple( objNameB, objNameT )
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
function array2select(array, objName) {
	obj = eval('document.forms[0].' + objName);

	if( isArray(array) ) {
		for(i = 0; i < obj.length; i++) {
			obj.options[i].text  = array[i].text;
			obj.options[i].value = array[i].value;
		}
	}
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
* Difference between two selectboxes. Remove the elements of the objName2
* that are found in objName1.
*/
function selectDiffMultiple(objName1, objName2) {
	array1 = select2array(objName1);
	array2 = select2array(objName2);
	obj2 = eval('document.forms[0].' + objName2);

	for( var i = 0; i < array1.length; i++ ) {
		for(var j = 0; j < array2.length; j++) {
			if(array1[i].value == array2[j].value){
				obj2.options[j].disabled = true;
				// if is IE, emulates disabled
				if(navigator.appName == "Microsoft Internet Explorer")
					obj2.options[j].style.color = "gray";
				break;
			}
		}
	}
}
