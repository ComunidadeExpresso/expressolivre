/*
*	Extension of JQuery UI Dialog widget to add custom minimizing capabilities
*	Written by: Ryan Curtis
*
*/
(function($){
	var _init = $.ui.dialog.prototype._init;
	
	//Optional top margin for page, wont let a user move a dialog into this spot.
	var topMargin = 0;
	
	//Custom Dialog Init
	$.ui.dialog.prototype._init = function() {
		var self = this;
        _init.apply(this, arguments);
		uiDialogTitlebar = this.uiDialogTitlebar;
		
		
		//we need two variables to preserve the original width and height so that can be restored.
		this.options.originalWidth = this.options.width;
		this.options.originalHeight = this.options.height;
		
		//save a reference to the resizable handle so we can hide it when necessary.
		this.resizeableHandle =  this.uiDialog.resizable().find('.ui-resizable-se');
		
		//Save the height of the titlebar for the minimize operation
		this.titlebarHeight = parseInt(uiDialogTitlebar.css('height')) + parseInt(uiDialogTitlebar.css('padding-top')) + parseInt(uiDialogTitlebar.css('padding-bottom')) + parseInt(this.uiDialog.css('padding-top')) + parseInt(this.uiDialog.css('padding-bottom')) ;
		
		uiDialogTitlebar.append('<a href="#"class="dialog-restore ui-dialog-titlebar-rest"><span class="ui-icon ui-icon-newwin"></span></a>');
		uiDialogTitlebar.append('<a href="#" id="dialog-minimize" class="dialog-minimize ui-dialog-titlebar-min"><span class="ui-icon ui-icon-minusthick"></span></a>');
		
		//Minimize Button
		this.uiDialogTitlebarMin = $('.dialog-minimize', uiDialogTitlebar).hover(function(){
			$(this).addClass('ui-state-hover');
		}, function(){
			$(this).removeClass('ui-state-hover');
		}).click(function(){
			self.minimize();
			return false;
		});
		//Restore Button
		this.uiDialogTitlebarRest = $('.dialog-restore', uiDialogTitlebar).hover(function(){
			$(this).addClass('ui-state-hover');
		}, function(){
			$(this).removeClass('ui-state-hover');
		}).click(function(){
			self.restore();
			self.moveToTop(true);
			return false;
		}).hide();
		
		
		//restore the minimize button on close
		this.uiDialog.bind('dialogbeforeclose', function(event, ui) {
			self.uiDialogTitlebarRest.hide();
			self.uiDialogTitlebarMin.show();
		});


		
	};
	//Custom Dialog Functions
	$.extend($.ui.dialog.prototype, {
		restore: function() {
			this.uiDialog.resizable( "option", "disabled", false );
			//We want to prevent the dialog from expanding off the screen
			var windowHeight = $(window).height();
			var dialogHeight = this.options.originalHeight;
			var dialogTop = parseInt(this.uiDialog.css('top'));
			if(dialogHeight+dialogTop > windowHeight)
			{
				//there is 22 pixels of padding at the bottom of a dialog per css file
				var newTop = windowHeight-dialogHeight-22;
				this.uiDialog.css('top',newTop);
			}			
			var windowWidth = $(window).width();
			var dialogWidth = this.options.originalWidth;
			var dialogLeft = parseInt(this.uiDialog.css('left'));
			if(dialogWidth+dialogLeft > windowWidth)
			{
				//there are 2 pixels of padding per css
				var newLeft = windowWidth-dialogWidth-2;
				this.uiDialog.css('left',newLeft);
			}
			this.uiDialog.css({width: this.options.originalWidth, height:this.options.originalHeight});
			this.element.show();
			
			this.resizeableHandle.show();
			this.uiDialogTitlebarRest.hide();
			this.uiDialogTitlebarMin.show();
		},
		minimize: function() { 
			//Store the original height/width
			this.uiDialog.resizable( "option", "disabled", true );
			this.options.originalWidth = this.options.width;
			this.options.originalHeight = this.options.height;
			
			this.uiDialog.animate({width: 200, height:this.titlebarHeight},200);
			this.element.hide();
			
			this.uiDialogTitlebarMin.hide();
			this.uiDialogTitlebarRest.show();
			this.resizeableHandle.hide();
		}
	});
})(jQuery); 