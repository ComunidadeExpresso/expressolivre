/* -------------------------------------------------------------------------- *
 *   Experience Javascript Library (version 1.0b)
 *  (c) 2007 Ahmed Saad <ahmeds@users.sourceforge.net>
 *
 *  Experience is freely distributable under the terms of an MIT-style license.
 *  For details, see project website at http://experience.sf.net
 * -------------------------------------------------------------------------- */

// global namespace
var experience  = {
    Version : '1.0b',

    Params : $H({
        Locale: 'pt_BR',  /* The language of strings embeded in code is considered the default */
        InterfaceDir: 'ltr'
    }),

    /* The interface to the world */
    Core :  {
        initialize : function(userParams){
            experience.Params.merge(userParams);

            //// PUBLIC METHODS ////////////////////////////

            this.bindTip = experience.bindTip;
            this.unbindTip = experience.unbindTip;
        },       

        bindTip : function(element, html, className){
            element = $(element);
            element.tipHTML = html;
            element.tipClass = className ? className : "experienceTip";            
            Event.observe(element, 'mousemove', experience.showHoverDiv);
            Event.observe(element, 'mouseout', experience.hideHoverDiv);        
        },
    
        unbindTip : function(element){
            if ($('experienceTip') &&  $('experienceTip').srcElement == element){
                experience.hideHoverDiv();
            }
    
            Event.stopObserving(element, 'mousemove', experience.showHoverDiv);
            Event.stopObserving(element, 'mouseout', experience.hideHoverDiv); 
        }
    },

    ///// PRIVATE FUNCTIONS ////////////////////////////////////////////////

    Console : {
        isEnabled: true,
        log : function(obj){
            if (experience.Console.isEnabled){
                if(window.console){ // Firebug
                    window.console.log(obj);
                } else if (typeof(opera) != "undefined"){
                    opera.postError(Object.inspect(obj));
                } else {
                    alert(Object.inspect(obj));
                }
            }
        }
    },

    // internationalization support 
    tr : function (str, locales){
        if (
            locales.keys().include(this.Params.Locale) &&
            typeof(locales[this.Params.Locale][str]) != 'undefined'
        ) {
            return locales[this.Params.Locale][str];
        } else {
            return str;
        }
    },

    // browser detection 
    detectBrowser: function (){
        if (navigator.userAgent.indexOf("MSIE 5") != -1){
            return "ie5";
        } else if (navigator.userAgent.indexOf("MSIE 6") != -1){ 
            return "ie6";
        } else if (navigator.userAgent.indexOf("Safari") != -1){
            return "safari";
        } else if (navigator.userAgent.indexOf("Firefox") != -1) {
            return "firefox";
        } else if (navigator.userAgent.indexOf("Opera") != -1) {
            return "opera";
        } else if (navigator.userAgent.indexOf("Konqueror") != -1) {
            return "konqueror";
        }
    },

    detectEngine: function() {
        var browser = experience.detectBrowser();
        if (browser == "ie5" || browser == "ie6"){
            return "trident";
        } else if (navigator.userAgent.indexOf("AppleWebKit") != -1){
            return "webkit";
        } else if (navigator.userAgent.indexOf("KHTML") != -1){
            return "khtml";
        } else if ( navigator.userAgent.indexOf("Gecko") != -1 &&
                    navigator.userAgent.indexOf("like Gecko") == -1 ){
            return "gecko";
        } 
    },

    showHoverDiv : function(e){
        var el = Event.element(e);

        if (!$('experienceTip')){
            var div = document.createElement('div');
            div.id = 'experienceTip';
            div.style.position = "absolute";
            document.getElementsByTagName('body')[0].appendChild(div);
        }

        /* A speed optimization (it's slow to reassign on every call */
        if (experience.lastTipElement != el){
            $('experienceTip').className = el.tipClass;
            $('experienceTip').innerHTML = el.tipHTML;
            $('experienceTip').srcElement = el;
        }

        $('experienceTip').style.display = "block";
        $('experienceTip').style.top = Event.pointerY(e) + 20 + "px";
        $('experienceTip').style.left = Event.pointerX(e) + "px";

        experience.lastTipElement = el;
    },

    hideHoverDiv : function(e){
        $('experienceTip').style.display = "none";
    }
}
