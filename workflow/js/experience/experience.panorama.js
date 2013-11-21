/* -------------------------------------------------------------------------- *
 *   Experience Javascript Library (version 1.0b)
 *  (c) 2007 Ahmed Saad <ahmeds@users.sourceforge.net>
 *
 *  Experience is freely distributable under the terms of an MIT-style license.
 *  For details, see project website at http://experience.sf.net
 * -------------------------------------------------------------------------- */

// Panorama component
experience.panorama  =  {

    Version : '0.2',

    //// MNEMONICS ////////////////////////////////////////////

    ZOOM_IN : 1,
    ZOOM_OUT : -1,
    ZOOM_RESTORE_SIZE : 0,
    MOVE_DOWN : 1,
    MOVE_UP :  2,
    MOVE_RIGHT : 3,
    MOVE_LEFT : 4,

    Viewer : function(userParams) {
    
        //// PRIVATE FIELDS //////////////////////////////////////

        this.Params = $H({
            ZoomFactor : 0.05, // %
            MoveBy : 2, // px
            MoveRate :  15, // ms
            ReversePanning : false,
            ReverseScrolling : true, 
            InnerUpperOffset : 40, // px 
            ShowStatus : true, 
            ShowNavigator : true,
            ShowZoomSelectBox : true,
            IconDirectory : 'icons',
            IconExtension : '.gif', 
            RenderIn: null // an element or an id
        }),

        this.isBeingDragged = false;
        this.lastLeft = this.lastTop = this.lastX = this.lastY = null;
        this.originalWidth = this.originalHeight = null;
        this.timeOutId = null;
        this.isMaximizedInstance = false;
        this.onFullWindowListeners = [];
        this.onUnFullWindowListeners = [];

        //// PUBLIC METHODS //////////////////////////////////////////////////////

        this.show = experience.panorama.show;
        this.hide = experience.panorama.hide;
        this.setImage = experience.panorama.setImage;
        this.getImage = experience.panorama.getImage;
        this.addOnFullWindowListener = experience.panorama.addOnFullWindowListener;
        this.removeOnFullWindowListener = experience.panorama.removeOnFullWindowListener;
        this.addOnUnFullWindowListener = experience.panorama.addOnUnFullWindowListener;
        this.removeOnUnFullWindowListener = experience.panorama.removeOnUnFullWindowListener;

        //// PRIVATE METHODS /////////////////////////////////////////////////////

        this.getImageResource = experience.panorama.getImageResource;
        this.positionImage = experience.panorama.positionImage;
        this.positionCanvas = experience.panorama.positionCanvas;
        this.setStatus = experience.panorama.setStatus;
        this.fireListeners = experience.panorama.fireListeners;

        //// VALIDATION //////////////////////////////////////////////////////////

        if (
              typeof(userParams['ImageURL']) != 'undefined' &&
              (
                typeof(userParams['ImageWidth']) == 'undefined' || 
                typeof(userParams['ImageHeight']) == 'undefined'
              )
           )
        {
            throw new Error("Experience::Panorama: You have to specify ImageWidth and ImageHeight when calling " +
                              "initialize() passing an ImageURL");
        }

        //// INITIALIZATION /////////////////////////////////////////////

        this.Params.merge(userParams);
        
        // construct canvas and all
        this.canvas = document.createElement('div');
        this.canvas.className = "panoramaCanvas";
        this.canvas.style.cursor = experience.panorama.getGrabCursor();
        this.positionCanvas(this.Params.RenderIn != null);

        // A queue for childern to be added to the canvas
        var childernQueue = [];


        var closeImg = this.closeImg  = document.createElement('img');
        closeImg.className = 'panoramaCloseIcon';
        closeImg.title = experience.tr("Close", experience.panorama.Locales);
        
        if (this.Params.RenderIn){
            Event.observe(closeImg, 'click', experience.panorama.toggleFullWindow.bindAsEventListener(this));
            closeImg.src = this.getImageResource('go_fullwindow');
            closeImg.title = closeImg.alt = experience.tr("Go Full-Window", experience.panorama.Locales);
        } else {
            Event.observe(closeImg, 'click', experience.panorama.hide.bindAsEventListener(this));
            closeImg.src = this.getImageResource('close');
            closeImg.title = closeImg.alt = experience.tr("Close", experience.panorama.Locales);
        }

        childernQueue.push(closeImg);

        var aboutIcon = document.createElement('img');
        aboutIcon.className = 'panoramaAboutIcon';
        aboutIcon.src = this.getImageResource('about');
        aboutIcon.title = aboutIcon.alt = experience.tr("Help", experience.panorama.Locales);
        Event.observe(aboutIcon, 'click', experience.panorama.showHelp.bindAsEventListener(this));
        childernQueue.push(aboutIcon);

        
        var zoomInIcon = document.createElement('img');
        zoomInIcon.className = 'panoramaZoomInIcon';
        zoomInIcon.src = this.getImageResource('zoom_in');
        zoomInIcon.title = zoomInIcon.alt = experience.tr("Zoom In", experience.panorama.Locales);
        Event.observe(zoomInIcon, 'click', 
                experience.panorama.zoom.bindAsEventListener(this, experience.panorama.ZOOM_IN));
        childernQueue.push(zoomInIcon);

      
        var zoomOutIcon = document.createElement('img');
        zoomOutIcon.className = 'panoramaZoomOutIcon';
        zoomOutIcon.src = this.getImageResource('zoom_out');;
        zoomOutIcon.title = zoomOutIcon.alt = experience.tr("Zoom Out", experience.panorama.Locales);
        Event.observe(zoomOutIcon, 'click', 
                experience.panorama.zoom.bindAsEventListener(this, experience.panorama.ZOOM_OUT));
        childernQueue.push(zoomOutIcon);
        

        var restoreSizeIcon = document.createElement('img');
        restoreSizeIcon.className = 'panoramaRestoreSizeIcon';
        restoreSizeIcon.src = this.getImageResource('restore_size');;
        restoreSizeIcon.title = restoreSizeIcon.alt =  
                experience.tr("Restore Original Size", experience.panorama.Locales);
         Event.observe(restoreSizeIcon, 'click', 
                experience.panorama.zoom.bindAsEventListener(this, experience.panorama.ZOOM_RESTORE_SIZE));
        childernQueue.push(restoreSizeIcon);



        var restorePositionIcon = document.createElement('img');
        restorePositionIcon.className = 'panoramaRestorePositionIcon';
        restorePositionIcon.src = this.getImageResource('restore_position');
        restorePositionIcon.title = restorePositionIcon.alt = 
            experience.tr("Restore Original Position", experience.panorama.Locales);
        Event.observe(restorePositionIcon, 'click', 
            experience.panorama.positionImage.bindAsEventListener(this));
        childernQueue.push(restorePositionIcon);
      

        if (this.Params.ShowNavigator){

            var goDownIcon = document.createElement('img');
            goDownIcon.className = 'panoramaGoDownIcon';
            goDownIcon.src = this.getImageResource('go_down');
            goDownIcon.title = goDownIcon.alt =  
                experience.tr("Scrolling .. ", experience.panorama.Locales);
            Event.observe(goDownIcon, 'mouseover', 
                experience.panorama.move.bindAsEventListener(this, 
                    this.Params.ReverseScrolling? experience.panorama.MOVE_UP : experience.panorama.MOVE_DOWN));
            Event.observe(goDownIcon, 'mouseout', 
                experience.panorama.clearMoveTimeout.bindAsEventListener(this));
            childernQueue.push(goDownIcon);
            
            var goUpIcon = document.createElement('img');
            goUpIcon.className = 'panoramaGoUpIcon';
            goUpIcon.src = this.getImageResource('go_up');
            goUpIcon.title = goUpIcon.alt = 
                experience.tr("Scrolling .. ", experience.panorama.Locales);
            Event.observe(goUpIcon, 'mouseover', 
                experience.panorama.move.bindAsEventListener(this, 
                    this.Params.ReverseScrolling? experience.panorama.MOVE_DOWN : experience.panorama.MOVE_UP));
            Event.observe(goUpIcon, 'mouseout', 
                experience.panorama.clearMoveTimeout.bindAsEventListener(this));
            childernQueue.push(goUpIcon);
            

            var goRightIcon = document.createElement('img');
            goRightIcon.className = 'panoramaGoRightIcon';
            goRightIcon.src = this.getImageResource('go_right');
            goRightIcon.title = goRightIcon.alt = experience.tr("Scrolling .. ", experience.panorama.Locales);
            Event.observe(goRightIcon, 'mouseover', experience.panorama.move.bindAsEventListener(this, 
                    this.Params.ReverseScrolling? experience.panorama.MOVE_LEFT : experience.panorama.MOVE_RIGHT));
            Event.observe(goRightIcon, 'mouseout', 
                    experience.panorama.clearMoveTimeout.bindAsEventListener(this));
            childernQueue.push(goRightIcon);
            

            var goLeftIcon = document.createElement('img');
            goLeftIcon.className = 'panoramaGoLeftIcon';
            goLeftIcon.src = this.getImageResource('go_left');
            goLeftIcon.title = goLeftIcon.alt =  
                    experience.tr("Scrolling .. ", experience.panorama.Locales);
            Event.observe(goLeftIcon, 'mouseover', 
                    experience.panorama.move.bindAsEventListener(this, 
                        this.Params.ReverseScrolling? experience.panorama.MOVE_RIGHT : experience.panorama.LEFT));
            Event.observe(goLeftIcon, 'mouseout', 
                    experience.panorama.clearMoveTimeout.bindAsEventListener(this));
            childernQueue.push(goLeftIcon);
        }
        
        if (this.Params.ShowZoomSelectBox){
            var zoomDropDown = document.createElement('select');
            zoomDropDown.className = 'panoramaZoomDropdown';
            this.indicatorOption = document.createElement('option');
            this.indicatorOption.className = 'panoramaIndicator';
            this.indicatorOption.innerHTML = '% 100';
            this.indicatorOption.value = '#';
            this.indicatorOption.selected = true;
            zoomDropDown.appendChild(this.indicatorOption);
            var zoomPercentages = [5, 10, 15, 25, 50, 75, 90, 100, 200, 400];
            for(var x = 0; x < zoomPercentages.length; x++){
                var opt = document.createElement('option');
                opt.innerHTML = zoomPercentages[x];
                opt.value = zoomPercentages[x]/100;
                zoomDropDown.appendChild(opt);
            }
            Event.observe(zoomDropDown, 'change', 
                experience.panorama.handleZoomPercentageChange.bindAsEventListener(this));
            childernQueue.push(zoomDropDown);
        }

        if (this.Params.ShowStatus){
            this.status = document.createElement('span');
            this.status.className = "panoramaStatus";
            childernQueue.push(this.status);
            this.setStatus(null, "<img src='" + this.getImageResource('loading') + "' />");
        }

        this.image = document.createElement('img');
        this.image.className = "panoramaImage";
        this.image.src = this.Params.ImageURL;
        this.image.alt = this.Params.ImageURL;
        this.image.style.position = "relative";
        this.image.style.width  =  this.Params.ImageWidth + "px";
        this.image.style.height =  this.Params.ImageHeight + "px";
        this.positionImage();
        childernQueue.push(this.image);
        
        Event.observe(this.image, 'load', experience.panorama.setStatus.bindAsEventListener(this, ""));
        Event.observe(this.image, 'error', 
                        experience.panorama.setStatus.bindAsEventListener(this, 
                                experience.tr("Could not load image.", experience.panorama.Locales)));

        // Misc. handlers ..
        Event.observe(this.canvas, 'mousedown', 
                experience.panorama.handleMouseDown.bindAsEventListener(this));
        Event.observe(this.canvas, 'mousemove', 
                experience.panorama.handleMouseMove.bindAsEventListener(this));
        Event.observe(document, 'mouseup', 
                experience.panorama.handleMouseUp.bindAsEventListener(this));

        var wheelHandler = experience.panorama.handleMouseWheel.bindAsEventListener(this);
        Event.observe(this.canvas, "DOMMouseScroll", wheelHandler, false); // Mozilla
        Event.observe(this.canvas, "mousewheel", wheelHandler, false);

        // add elements to the canvas (in reverse for proper z-Index ordering)
        for(var i = childernQueue.length - 1; i >= 0; i--){
            this.canvas.appendChild(childernQueue[i]);
        } 
        
    },

    //// PUBLIC METHODS ///////////////////////////////////////////////////////    

    show : function(){
        if(!this.Params.RenderIn){
            this.fireListeners(this.onFullWindowListeners);
        }

        this.canvas.style.visibility = 'visible';
    },

    hide : function(){
        if(!this.Params.RenderIn){
            this.fireListeners(this.onUnFullWindowListeners);
        }

        this.canvas.style.visibility = 'hidden';
    },

    toggleFullWindow : function(e){
        //experience.Console.log(this.isMaximizedInstance);

        if (this.isMaximizedInstance){ // minimize
            this.closeImg.src = this.getImageResource('go_fullwindow');
            this.closeImg.title = this.closeImg.alt = experience.tr("Go Full-Window", experience.panorama.Locales);

            this.positionCanvas(true);
            this.isMaximizedInstance = false;
        } else { // maximize
            this.closeImg.src = this.getImageResource('unfullwindow');
            this.closeImg.title = this.closeImg.alt = experience.tr("Restore", experience.panorama.Locales);

            this.positionCanvas(false);
            this.isMaximizedInstance = true;
        }
    },

    getImage : function (){
        return [this.Params.ImageURL, this.Params.ImageWidth, this.Params.ImageHeight];
    },

    setImage : function(url, width, height){
        this.image.src = this.Params.ImageURL = url;
        this.image.style.width = this.Params.ImageWidth  = width + "px";
        this.image.style.height = this.Params.ImageHeight = height + "px";
        this.positionImage();
    },

    addOnFullWindowListener : function(listener){
        this.onFullWindowListeners[this.onFullWindowListeners.length] = listener;
    },

    removeOnFullWindowListener : function(listener){
        if (this.onFullWindowListeners.include(listener)){
            this.onFullWindowListeners.splice(this.onFullWindowListeners.indexOf(listener), 1);
        }
    },

    addOnUnFullWindowListener : function(listener){
        this.onUnFullWindowListeners[this.onUnFullWindowListeners.length] = listener;
    },

    removeOnUnFullWindowListener : function(listener){
        if (this.onUnFullWindowListeners.include(listener)){
            this.onUnFullWindowListeners.splice(this.onUnFullWindowListeners.indexOf(listener), 1);
        }
    },

    //// PRIVATE METHODS ///////////////////////////////////////////////////////

    fireListeners : function(listeners){
        for(var i =0; i < listeners.length; i++){
            listeners[i]();
        }
    }, 

    setStatus : function(e, html){
        if (this.Params.ShowStatus){
            this.status.innerHTML = html;
        }
    },

    showHelp : function(e){
        alert(experience.tr("HelpText", experience.panorama.Locales));
    },

    positionImage :  function (e){
        var image = this.image;

        // detecting canvas width and height doesn't work in KHTML (and WebKit?)
        if (experience.detectEngine() == "khtml"){
            image.style.top = image.style.left = this.Params.InnerUpperOffset + "px";
            return;
        }

        var canvasWidth  = Element.getWidth(this.canvas);
        var canvasHeight = Element.getHeight(this.canvas);

        // center if it doesn't fill
        if (parseFloat(image.style.width) > canvasWidth){
            image.style.left = this.Params.InnerUpperOffset + "px";
        } else {
            image.style.left =  (canvasWidth / 2) -  (parseFloat(image.style.width) / 2) + "px";
        }
           
        if (parseFloat(image.style.height) > canvasHeight){
            image.style.top = this.Params.InnerUpperOffset + "px";
        } else {
            image.style.top =  (canvasHeight / 2) -  (parseFloat(image.style.height) / 2) + "px";
        }
    },

    positionCanvas : function (isRenderIn){

        if(this.canvas.parentNode){
            this.canvas.parentNode.removeChild(this.canvas);
        }

        if(isRenderIn){
            if(this.isMaximizedInstance){
                this.fireListeners(this.onUnFullWindowListeners);
            }

            $(this.Params.RenderIn).style.position = "relative";
            this.canvas.style.position = "absolute";
            this.canvas.style.left = 
                this.canvas.style.right =
                this.canvas.style.top = 
                this.canvas.style.bottom = "0px";
            this.canvas.style.height = this.canvas.style.width = "100%";
            this.canvas.style.visibility  = 'visible';

            $(this.Params.RenderIn).appendChild(this.canvas);
        } else {
            if(this.Params.RenderIn){
                this.fireListeners(this.onFullWindowListeners);
            }

            document.getElementsByTagName('body')[0].appendChild(this.canvas);

            if (experience.detectBrowser() == "ie5" || experience.detectBrowser() == "ie6"){
                this.canvas.style.position = "absolute";
                this.canvas.style.setExpression("top", 
                    "(ignoreMe = document.documentElement.scrollTop? " + 
                        "document.documentElement.scrollTop : document.body.scrollTop) + 'px'");
                this.canvas.style.setExpression("height",
                     "experience.panorama.getInnerWindowDimensions()['height']");
                this.canvas.style.setExpression("width",
                     "experience.panorama.getInnerWindowDimensions()['width']");
            } else {
                this.canvas.style.position = "fixed"
            }
        }

        //experience.Console.log(this.canvas.parentNode.tagName + "," + isRenderIn);
    },

    /**
      * Thanks to http://www.quirksmode.org/viewport/compatibility.html 
      */
    getInnerWindowDimensions: function (){
        var x,y;
        if (self.innerHeight) // all except Explorer
        {
            x = self.innerWidth;
            y = self.innerHeight;
        }
        else if (document.documentElement && document.documentElement.clientHeight)
            // Explorer 6 Strict Mode
        {
            x = document.documentElement.clientWidth;
            y = document.documentElement.clientHeight;
        }
        else if (document.body) // other Explorers
        {
            x = document.body.clientWidth;
            y = document.body.clientHeight;
        }

        return {width: x, height: y};
    },

    zoom : function(e, delta){

        var zoomFactor = this.Params.ZoomFactor;
        var newWidth, newHeight, newLeft, newTop = null;
        
        // parse current pixel dimensions to floats
        var pWidth = parseFloat(this.image.style.width);
        var pHeight = parseFloat(this.image.style.height);
        
        if (0 == delta){ // restore original size
            newWidth = this.Params.ImageWidth;
            newHeight = this.Params.ImageHeight;
            
            // distribute size change
            newLeft = (parseFloat(this.image.style.left) - ((newWidth - pWidth) / 2));
            newTop  = (parseFloat(this.image.style.top) - ((newHeight - pHeight) / 2)); 
        } else if (delta > 0){ // zoom in
            newWidth = (pWidth + (pWidth * zoomFactor));
            newHeight = (pHeight + (pHeight * zoomFactor));
            
            // distribute size change
            newLeft = (parseFloat(this.image.style.left) - ((pWidth * zoomFactor) / 2));
            newTop = (parseFloat(this.image.style.top) - ((pHeight * zoomFactor) / 2)); 
        } else if (delta < 0){ // zoom out
            newWidth = (pWidth - (pWidth * zoomFactor));
            newHeight =  (pHeight - (pHeight * zoomFactor));
            
            // distribute size change
            newLeft = (parseFloat(this.image.style.left) + ((pWidth * zoomFactor) / 2));
            newTop  = (parseFloat(this.image.style.top)  + ((pHeight * zoomFactor) / 2)); 
        } else {
            alert("Invalid delta value:" +  delta);
            return;
        }

        var percentage = (newWidth/this.Params.ImageWidth).toFixed(3);
        this.indicatorOption.innerHTML = "% " + (percentage * 100).toFixed(1);           

        this.image.style.width  = newWidth  + "px";
        this.image.style.height = newHeight + "px";
        this.image.style.left   = newLeft   + "px";
        this.image.style.top    = newTop    + "px";

    },

    move : function (e, toWhere){
        if (!this.isBeingDragged){
            var MOVE_BY = this.Params.MoveBy;
    
            switch(toWhere){
                case experience.panorama.MOVE_DOWN:
                    this.image.style.top = (parseFloat(this.image.style.top) + MOVE_BY) + "px";
                    break;
                case experience.panorama.MOVE_UP:
                    this.image.style.top = (parseFloat(this.image.style.top) - MOVE_BY) + "px";
                    break;
                case experience.panorama.MOVE_RIGHT:
                    this.image.style.left = (parseFloat(this.image.style.left) + MOVE_BY) + "px";
                    break;
                case experience.panorama.MOVE_LEFT:
                    this.image.style.left = (parseFloat(this.image.style.left) - MOVE_BY) + "px";
                    break;
                default:
                    experience.Console.log("Unrecognized 'toWhere' value '" + toWhere + "'");
                    return;
            }
    
            this.timeOutId = 
                setTimeout(experience.panorama.move.bind(this, null, toWhere), this.Params.MoveRate);
        }
    },

    clearMoveTimeout : function (e){
         clearTimeout(this.timeOutId);
    },

    handleZoomPercentageChange : function(e){
        var percentage = Event.element(e).value;

        if (percentage != '#'){
            var newWidth, newHeight, newLeft, newTop;
            
            newWidth  = this.Params.ImageWidth * percentage;
            newHeight = this.Params.ImageHeight * percentage;
            
            // distribute size change
            newLeft = parseFloat(this.image.style.left) - 
                            ((newWidth - parseFloat(this.image.style.width)) / 2);
            newTop  = parseFloat(this.image.style.top) - 
                            ((newHeight - parseFloat(this.image.style.height)) / 2); 
            
            // apply new size
            this.image.style.width  = newWidth  + "px";
            this.image.style.height = newHeight + "px";
            this.image.style.left   = newLeft   + "px";
            this.image.style.top    = newTop    + "px";
            
            Event.element(e).selectedIndex = 0;
            Event.element(e).options[0].innerHTML = "% " + (percentage * 100);
        }
    },

    handleMouseDown : function(e){
        if (
            (
                Event.element(e) == this.canvas ||
                Event.element(e) == this.image
            ) && Event.isLeftClick(e)
           ){

            this.isBeingDragged = true;
            this.canvas.style.cursor = experience.panorama.getGrabbingCursor();
            this.lastLeft = parseFloat(this.image.style.left);
            this.lastTop  = parseFloat(this.image.style.top);
            this.lastX = Event.pointerX(e);
            this.lastY = Event.pointerY(e);
            
            // I love Prototype!
            Event.stop(e);
        }
    },

    handleMouseMove : function (e){
        if (
            Event.element(e) == this.canvas ||
            Event.element(e) == this.image
           ){
                var sign = this.Params.ReversePanning? -1 : 1;
                if (this.isBeingDragged){
                    this.image.style.left =  this.lastLeft + (sign * (Event.pointerX(e) - this.lastX)) + "px"; 
                    this.image.style.top  =  this.lastTop  + (sign * (Event.pointerY(e) - this.lastY)) + "px"; 
                }
                
                Event.stop(e);
        }
    },

    handleMouseUp : function(e){
        this.isBeingDragged = false;
        this.canvas.style.cursor = experience.panorama.getGrabCursor();
    },

    getGrabCursor : function(e) {
        if(experience.detectEngine() == "gecko"){
            return '-moz-grab';
        } else {
            return 'move';
        }
    },

    getGrabbingCursor : function(e){
        if(experience.detectEngine() == "gecko"){
            return '-moz-grabbing';
        } else {
            return 'move';
        }
    },

    /**
     * See also 
     *  http://adomas.org/javascript-mouse-wheel/
     *  http://www.ogonek.net/mousewheel/demo.html
     */
    handleMouseWheel: function(event){
        var delta = 0;

        if (event.wheelDelta) { // IE/Opera. 
            delta = event.wheelDelta/120;
            //In Opera 9, delta differs in sign as compared to IE.
            if (window.opera)
                delta = -delta;
        } else if (event.detail) { // Mozilla case. 
                // In Mozilla, sign of delta is different than in IE.
                // Also, delta is multiple of 3.
                delta = -event.detail/3;
        }

        delta = Math.round(delta); //Safari Round

        // If delta is nonzero, handle it.
        // Basically, delta is now positive if wheel was scrolled up,
        // and negative, if wheel was scrolled down.
        if (delta){
                experience.panorama.zoom.bindAsEventListener(this, delta)();
        }

        Event.stop(event);
    },

    getImageResource : function (resource){
            return this.Params.IconDirectory + "/" + resource 
                                        + this.Params.IconExtension;
    },


    Locales : $H({
        'en_US' : {
            'HelpText' : "Grab (click and drag) the image to move it around. Use your mouse wheel to zoom in and out the image or, if you don't have one, use the zoom icons in the toolbar. If you hover with your mouse pointer over any of the navigator arrow in the bottom right corner, the image will starting moving in that direction (automatic scrolling.)\n\nPanorama is part of the Experience JavaScript Library.\nFor details, see project website at http://experience.sf.net\n"
        },

        'ar' : {
            'Zoom In' : 'تكبير',
            'Zoom Out' : 'تصغير',
            'Restore Original Size' : 'استعادة الحجم الأصلي',
            'Restore Original Position' : 'استعادة الموقع الأصلي',
            'Close' : 'إغلاق',
            'Scrolling .. ' : 'جاري التحريك ...',
            'Help': 'مساعدة',
            'HelpText' :  'أجذب (أنقر و جر) الصورة كي تحركها. استخدم عجلة الفأرة لتكبير أو تصغير الصورة. إذا لم يكن لديك واحدة، فاستخدم الأيقونات التى على شريط الأدوات. إذا حركت مؤشر الفأرة فوق أحدى أسهم النقال في الجانب الأيمن السفلى، فسوف تتحرك الصورة فى فى ذلك الإتجاه (تحريك تلقائي). \n\n بانوراما هي جزء من مكتبة إكسبريبس البرمجية لجافاسكربت. \n لمزيد من التفاصيل، يرجى زيارة موقع المشروع فى \n http://experience.sf.net',
            'Could not load image' : 'خطأ أثناء تحميل الصورة',
            'Go Full-Window' : 'إملء النافذة بأكملها',
            'Restore' : 'استرجاع'
        },
        'pt_BR' : {
            'Zoom In' : 'Aproximar',
            'Zoom Out' : 'Afastar',
            'Restore Original Size' : 'Restaurar para o tamanho original',
            'Restore Original Position' : 'Restaurar para a posi��o original',
            'Close' : 'Fechar',
            'Scrolling .. ' : 'Deslizando ..',
            'Help': 'Ajuda',
			'HelpText' :  'Segure (clique e arraste) a imagem para mov�-la. Use a roda do mouse para aproximar ou afastar a imagem ou, se voc� n�o a possui, use os �cones de zoom na barra de ferramentas. Se voc� passar o cursor sobre as setas de navega��o, no canto inferior direito, a imagem se mover� na dire��o selecionada (rolagem autom�tica).\n\nPanorama � parte da Experience JavaScript Library.\nPara detalhes, veja o website do projeto em http://experience.sf.net\n',
            'Could not load image' : 'N�o foi poss�vel carregar a imagem',
            'Go Full-Window' : 'Ir para tela cheia',
            'Restore' : 'Restaurar'
        }
    })
}

