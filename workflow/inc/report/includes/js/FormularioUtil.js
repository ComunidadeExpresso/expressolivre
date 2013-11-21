/*
 * Funções não liberadas.
 * 
*/
/*
function showDown(evt) { 
    evt = (evt) ? evt : ((event) ? event : null); 
    if (evt) { 
	   // alert(evt.keyCode);
		
        if (navigator.appName=="Netscape") { 
	            if (evt.which == 116) { 
	                // When F5 is pressed
	                cancelKey(evt); 
	            } else if (evt.ctrlKey && (evt.which == 82)) { 
	                // When ctrl is pressed with R or N
	                cancelKey(evt); 
	            }
        } else {
	            if (event.keyCode == 116) { 
	                // When F5 is pressed
	                cancelKey(evt); 
	            } else if (event.ctrlKey && (event.keyCode == 78 || event.keyCode == 82)) { 
	                // When ctrl is pressed with R or N
	                cancelKey(evt); 
	            }
        }
    } 
		
} 
 
function cancelKey(evt) { 
    if (evt.preventDefault) { 
        evt.preventDefault(); 
        return false; 
    } else { 
        evt.keyCode = 0; 
        evt.returnValue = false; 
    } 
} */
 
/*TECLA t NÃO FUNCIONAVA.
 * if (navigator.appName=="Netscape") document.addEventListener("keypress",showDown,true);
 * */
//document.onkeydown  = showDown;

/*
function iUtilsValidachecks(frm,prefixo){
    ret = false;
	for (var i=1;i<frm.elements.length;i++)  {
      var e = frm.elements[i];
      if ((e.name.substring(0,prefixo.length + 1)  == (prefixo + "["))){
        if (e.checked) { ret = true; }
      }
    }
	return ret;
}
*/
function iUtilsSelecionachecks(check,frm,prefixo){
    for (var i=1;i<frm.elements.length;i++)  {
      var e = frm.elements[i];
      if ((e.name.substring(0,prefixo.length + 1)  == (prefixo + "["))){
        e.checked = check;
      }
    }
}

/*
function iUtilsMudaOrdem(formpesquisa,acao,novaordem,ordematual,tipo) {
	  if (novaordem == ordematual) {
		  if ((tipo == 'desc') || (tipo == '')) {
	         novotipo = 'asc';
	      } else {
	         novotipo = 'desc';
	      }
	  } else {
		  novotipo = 'asc';
	  }
	  document.getElementById(formpesquisa.name + "_acao").value = acao;
	  document.getElementById(formpesquisa.name + "_ordem").value = novaordem;
	  document.getElementById(formpesquisa.name + "_ordemtipo").value = novotipo;
	  formpesquisa.submit();
}

function iUtilsVisibilidadeQuadro(idquadro,visible) {
	if (visible == true) { 
    	visibility = ""; 
    } else { 
    	visibility = "none"; 
    }
	quadro = document.getElementById("quadro_" + idquadro);
	if (quadro != null) {
		quadro.style.display = visibility;
    }
}

function iUtilsOcultaQuadro(idquadro) {
	iUtilsVisibilidadeQuadro(idquadro,false)
}

function iUtilsExibeQuadro(idquadro) {
	iUtilsVisibilidadeQuadro(idquadro,true)
}
*/


function formUtilExibeImgAjax(campos) {
	campos_array = campos.split(",");
	i = 0;
	while (i < campos_array.length){
        img = document.getElementById("imgAjax_" + campos_array[i]);
			if (img != null) {
			  img.style.display = "";
			}
	    i = i + 1;
	} 
	/*img = document.getElementById("imgAjax_" + idcampo);
	if (img != null) {
	  img.style.display = "";
	}*/
}

function formUtilOcultaImgAjax(campos) {
	campos_array = campos.split(",");
	i = 0;
	while (i < campos_array.length){
        img = document.getElementById("imgAjax_" + campos_array[i]);
			if (img != null) {
			  img.style.display = "none";
			}
	    i = i + 1;
	} 
}

function formUtilOcultaCampos(campos) {
   campos_array = campos.split(",");
   
   i = 0;
   while (i < campos_array.length){
	   formUtilVisibilidadeCampo(campos_array[i],false);
	   i = i + 1; 
   }
}

function formUtilExibeCampos(campos) {
   campos_array = campos.split(",");
   i = 0;
   while (i < campos_array.length){
	   formUtilVisibilidadeCampo(campos_array[i],true);
	  i = i + 1; 
   }
}


function formUtilVisibilidadeCampo(idcampo,visible) {
    var	campo = null;
    campo = document.getElementById("id_" + idcampo);
    if (visible == true) { 
    	visibility = ""; 
    } else { 
    	visibility = "none"; 
    } 
    if (campo != null) {
    	campo.style.display = visibility;
    } else {
    	campo = document.getElementById("not_id_" + idcampo);
    	if (campo) {
    		campo.style.display = visibility;
    	}
    }
    inforight = document.getElementById("id_info_" + idcampo);
    if (inforight) {
    	inforight.style.display = visibility;
    }
    label = document.getElementById("id_lbl_" + idcampo);
    if (label) {
    	label.style.display = visibility;
    }
    ckbox = document.getElementById("not_div_ckbox_" + idcampo);
    if (ckbox) {
    	ckbox.style.display = visibility;
    }
    ckbox = document.getElementById("div_ckbox_" + idcampo);
    if (ckbox) {
    	ckbox.style.display = visibility;
    }
    imgcalendar = document.getElementById("id_imgcal_" + idcampo + "_inicio");
    if (imgcalendar) {
    	imgcalendar.style.display = visibility;
    }
    imgcalendar = document.getElementById("id_imgcal_" + idcampo + "_fim");
    if (imgcalendar) {
    	imgcalendar.style.display = visibility;
    }
}
