function show_messages(text) 
{
	messages.addText(text);
}


function phpeditor_checksyntax()
{
	var chkSyntax = function(data) {
		show_messages(data);
	};
	var txtcode;

	txtcode = editor.getContents();
	frmSend.code.value = txtcode;
	cExecuteFormData("$this.bo_editor.check_syntax",frmSend,chkSyntax);
	editor.setInputFocus();
}

function phpeditor_salvar()
{
	var hndSalvar = function(data) {
		show_messages(data);
	};
	var txtcode;

	txtcode = editor.getContents();
	frmSend.code.value = txtcode;
	cExecuteFormData("$this.bo_editor.save_php_source",frmSend,hndSalvar);
	document.title = editor.window_title;
	editor.setInputFocus();
	editor.unsaved = false;
}
