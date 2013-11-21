/**
* Gets all fields that have the id starting with "_".
* It's usefull when you have to validate fields with an ajax call, for example.
* Returns an array of elements.
*/
function get_form_fields(form_id){
	var elems = document.forms[form_id].elements;
	var arr   = new Object();
	var j     = 0;
	for(var i = 0; i < elems.length; i++){
		if(elems[i].id.substr(0,1) == "_"){
			// if the element is a radiobutton and is NOT checked, goes to the next element
			if(elems[i].type == "radio" && !elems[i].checked){
				continue;
			}

			if(elems[i].type == "select-multiple"){
				var selectBoxMultiple = elems[i];
				var selectArr = new Array();
				for(k = 0; k < elems[i].length; k++){
					var option = selectBoxMultiple.options[k];
					if(option.selected){
						if(option.value != ''){
							selectArr.push(option.value);
						} else {
							selectArr.push(option.innerHTML);
						}
					}
				}

				arr[elems[i].id] = selectArr;
				j++;
				continue;
			}

			// if the element has [] in the end of its name, return its values (or checked elements) like an array
			if(elems[i].name.substr(-2,2) == "[]"){
				elem_name = elems[i].name.substr(0, elems[i].name.length - 2);
				if(arr[elem_name] == undefined){
					arr[elem_name] = new Array();
				}

				if(elems[i].type != "checkbox")
					arr[elem_name].push(elems[i].value);
				else if(elems[i].checked)
					arr[elem_name].push(elems[i].value);
				j++;
				continue;
			}

			arr[elems[i].id] = elems[i].value;
			j++;
		}
	}
	return arr;
}
