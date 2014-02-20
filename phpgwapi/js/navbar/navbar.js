/**
 * Created by Thiago on 05/02/14.
 */
function showBar(){
    bar = document.getElementById("toolbar");
    bar.style.visibility = "";
    bar.style.position ="static";
    but = document.getElementById("hiddenButton");
    but.style.visibility = "";
    but.style.position = "absolute";
    but.style.top = "55px";
    but.style.left = "2px";
    title = "{hide_bar_txt}";
    extra = document.getElementById("extraButton");
    extra.style.visibility = "hidden";
    but.innerHTML='<a title="{hide_bar_txt}" href="#" onclick="javascript:changeBar()"><img src="../images/up.button.png" alt="Minimizar Barra" /></a>';
    var neverExpires = new Date("January 01, 2100 00:00:00");
    document.cookie = "showHeader=true"+
        ";expires=" + neverExpires.toGMTString()+
        ";path=/";
}

function hideBar(){
    bar = document.getElementById("toolbar");
    bar.style.position ="absolute";
    bar.style.visibility = "hidden";
    but = document.getElementById("hiddenButton");
    but.style.visibility = "hidden";
    title = "{show_bar_txt}";
    extra = document.getElementById("extraButton");
    extra.style.visibility = ""
    extra.style.top = "-11px";
    extra.style.left = "-10px";
    var neverExpires = new Date("January 01, 2100 00:00:00");
    document.cookie = "showHeader=false"+
        ";expires=" + neverExpires.toGMTString()+
        ";path=/";
}
function changeBar(){
    bar = document.getElementById("toolbar");
    if(bar.style.visibility == "hidden")
        showBar();
    else
        hideBar();
}
function initBar(val){

    if(val == 'true')
        showBar();
    else
        hideBar();
}
var zoominTimer = new Array();
var zoomoutTimer = new Array();
function zoom_in(id)
{
    clearTimeout(zoomoutTimer[id]);
    var elem = document.getElementById(id);
    if (elem.height > 34)
    {
        clearTimeout(zoominTimer[id]);
        return false;
    }
    elem.height += 4;
    elem.width += 4;
    zoominTimer[id] = setTimeout('zoom_in("'+id+'");',30);
}
function zoom_out(id)
{
    clearTimeout(zoominTimer[id]);
    var elem = document.getElementById(id);
    if (elem.height < 24)
    {
        clearTimeout(zoomoutTimer[id]);
        return false;
    }
    elem.height -= 2;
    elem.width -= 2;
    zoomoutTimer[id] = setTimeout('zoom_out("'+id+'");',30);
}

function openWindow(newWidth,newHeight,link)
{

    newScreenX  = screen.width - newWidth;
    newScreenY  = 0;
    Window1=window.open(link,'',"width="+newWidth+",height="+newHeight+",screenX="+newScreenX+",left="+newScreenX+",screenY="+newScreenY+",top="+newScreenY+",toolbar=no,scrollbars=yes,resizable=no");

}

new ypSlideOutMenu("menu2", "right", 0, 165, 160, 200);