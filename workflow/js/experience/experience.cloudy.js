/* -------------------------------------------------------------------------- *
 *   Experience Javascript Library (version 1.0b)
 *  (c) 2007 Ahmed Saad <ahmeds@users.sourceforge.net>
 *
 *  Experience is freely distributable under the terms of an MIT-style license.
 *  For details, see project website at http://experience.sf.net
 * -------------------------------------------------------------------------- */

// Cloudy component

experience.cloudy  =  {

    Version : '0.1',

    WeightedList : function (userParams){

        this.Params = $H({
            MinFontSize : 8, 
            MaxFontSize : 40, 
            Unit :  "px",
            Items : null, // an array of items
            RenderIn: null, // an element or id
            ItemCSSClass: "cloudyItem",
            TipCSSClass:  "cloudyTip"
        });

        //// PUBLIC METHODS ////////////////////////////////////////////////

        this.render = experience.cloudy.render;

        //// INITIALIZATION ///////////////////////////////////////////////

        this.Params.merge(userParams);
    },

    /**
    * Renders the weighted list inside an element (ie a <div>)
    * Parameters:
    *  arr: A numerically indexed array of hash-like objects 
    *       representing items that has the following properties:
    *        label:  the label of the item (ie tag name)
    *        weight: a number indicating item weight
    *        href:   where to link the item
    *        html:   (optional) a piece of html code (or just text) to 
    *                 display when hovering over the item 
    *        handlers: (optional) an object-like hash of event names and 
    *                   their handlers                            
    **/
        
    render : function(){

        if (this.Params.Items == null){
            throw new Error ("No Items array was specified in the Items parameter");
        }

        if (this.Params.RenderIn == null){
            throw new Error ("No element (or id) was specified in the RenderIn parameter");
        }

        var arr = this.Params.Items;
        var min, max, fix;
        
        for(var i = 0; i < arr.length; i++){
            max = (arr[i]["weight"] > max || max == null)? arr[i]["weight"] : max;
            min = (arr[i]["weight"] < min || min == null)? arr[i]["weight"] : min;
        } 
    
        var fix = max - min == 0? 1 : max - min;
    
        for(var i = 0; i < arr.length; i++){
            var ratio = (1 - (max - arr[i]["weight"]) / fix);
            var fontSize = this.Params.MinFontSize + (ratio * (this.Params.MaxFontSize - this.Params.MinFontSize)) ;
            
            var a  = document.createElement('a');
            a.href = arr[i]['href']? arr[i]['href'] : '#' ;
            a.className = this.Params.ItemCSSClass;
            a.style.fontSize =  fontSize + this.Params.Unit;
            a.innerHTML =  arr[i]["label"];

            if(typeof(arr[i]["html"]) != 'undefined'){
                experience.Core.bindTip(a, arr[i]["html"], this.Params.TipCSSClass );
            }

            if (arr[i]["handlers"]){
                for(eventName in arr[i]["handlers"]){
                    Event.observe(a, eventName, arr[i]["handlers"][eventName]);
                }
            }

            $(this.Params.RenderIn).appendChild(a);
            $(this.Params.RenderIn).appendChild(document.createTextNode(' '));
        } 
    }
}


