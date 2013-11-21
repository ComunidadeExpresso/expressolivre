// Esse arquivo serve para customizar o editor RTF existente na API do eGroupWare.
// Foram escondidos alguns bot�es e tamb�m foi traduzido para o portugu�s - BR algumas palavras que faltavam.
// Autor: Nilton E. Buhrer Neto
// Modificado para uso no workflow

_editor_url = "workflow/js/htmlarea";
_editor_lang = "pt_br";

document.write('<script');
document.write(' language="javascript"');
document.write(' type="text/javascript"');
document.write(' src="' + _editor_url + '/htmlarea.js">');
document.write('</script>');

document.write('<script');
document.write(' language="javascript"');
document.write(' type="text/javascript"');
document.write(' src="' + _editor_url + '/plugins/CharacterMap/character-map.js">');
document.write('</script>');

document.write('<script');
document.write(' language="javascript"');
document.write(' type="text/javascript"');
document.write(' src="' + _editor_url + '/plugins/CharacterMap/lang/en.js">');
document.write('</script>');

function initDocument(fieldName)
{
	if (typeof(this.editor) == "undefined")
		this.editor = new Array();
	if (!fieldName)
		fieldName = "body_rtf";

	var numberOfEditors = this.editor.length;

	this.editor[numberOfEditors] = new HTMLArea(fieldName);
	this.editor[numberOfEditors].config.formatblock = {
		"T�tulo 1": "h1",
		"T�tulo 2": "h2",
		"T�tulo 3": "h3",
		"T�tulo 4": "h4",
		"T�tulo 5": "h5",
		"T�tulo 6": "h6",
		"Normal": "p",
		"Endere�o": "address",
		"Pr�-formatado": "pre"
	};

	this.editor[numberOfEditors].config.hideSomeButtons(" insertimage about cut copy paste htmlmode popupeditor showhelp ");

	this.editor[numberOfEditors].config.height="600";
	if(screen.width >= 1024)
		this.editor[numberOfEditors].config.width="713";
	else
		this.editor[numberOfEditors].config.width="642";

	this.editor[numberOfEditors].config.statusBar = false;
	this.editor[numberOfEditors].registerPlugin("CharacterMap");

	this.editor[numberOfEditors].generate();
}

function endDocument()
{
	editor = (this.editor) ? this.editor : parent.editor;
	for (var i = 0; i < editor.length; i++)
		editor[i]._textArea.value = editor[i].getInnerHTML();
}
