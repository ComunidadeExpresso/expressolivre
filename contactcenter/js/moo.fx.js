/*
moo.fx, simple effects library built with prototype.js (http://prototype.conio.net).
by Valerio Proietti (http://mad4milk.net) MIT-style LICENSE.
for more info (http://moofx.mad4milk.net).
10/24/2005
v(1.0.2)
*/
var Class = {
  create: function() {
    return function() { 
      this.initialize.apply(this, arguments);
    }
  }
}

Object.extend = function(destination, source) {
  for (property in source) {
    destination[property] = source[property];
  }
  return destination;
}

Function.prototype.bind = function(object) {
  var __method = this;
  return function() {
    return __method.apply(object, arguments);
  }
}

function $() {
  var elements = new Array();

  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element == 'string')
      element = document.getElementById(element);

    if (arguments.length == 1) 
      return element;

    elements.push(element);
  }

  return elements;
}


if (!window.Element) {
  var Element = new Object();
}

Object.extend(Element, {
  remove: function(element) {
    element = $(element);
    element.parentNode.removeChild(element);
  },

  hasClassName: function(element, className) {
    element = $(element);
    if (!element)
      return;
    var a = element.className.split(' ');
    for (var i = 0; i < a.length; i++) {
      if (a[i] == className)
        return true;
    }
    return false;
  },

  addClassName: function(element, className) {
    element = $(element);
    Element.removeClassName(element, className);
    element.className += ' ' + className;
  },
  
  removeClassName: function(element, className) {
    element = $(element);
    if (!element)
      return;
    var newClassName = '';
    var a = element.className.split(' ');
    for (var i = 0; i < a.length; i++) {
      if (a[i] != className) {
        if (i > 0)
          newClassName += ' ';
        newClassName += a[i];
      }
    }
    element.className = newClassName;
  },
  
  // removes whitespace-only text node children
  cleanWhitespace: function(element) {
    element = $(element);
    for (var i = 0; i < element.childNodes.length; i++) {
      var node = element.childNodes[i];
      if (node.nodeType == 3 && !/\S/.test(node.nodeValue)) 
        Element.remove(node);
    }
  }
});
//base
var fx = new Object();
fx.Base = function(){};
fx.Base.prototype = {
	setOptions: function(options) {
	this.options = {
		duration: 500,
		onComplete: ''
	}
	Object.extend(this.options, options || {});
	},

	go: function() {
		this.duration = this.options.duration;
		this.startTime = (new Date).getTime();
		this.timer = setInterval (this.step.bind(this), 13);
	},

	step: function() {
		var time  = (new Date).getTime();
		var Tpos   = (time - this.startTime) / (this.duration);
		if (time >= this.duration+this.startTime) {
			this.now = this.to;
			clearInterval (this.timer);
			this.timer = null;
			if (this.options.onComplete) setTimeout(this.options.onComplete.bind(this), 10);
		}
		else {
			this.now = ((-Math.cos(Tpos*Math.PI)/2) + 0.5) * (this.to-this.from) + this.from;
			//this time-position, sinoidal transition thing is from script.aculo.us
		}
		this.increase();
	},

	custom: function(from, to) {
		if (this.timer != null) return;
		this.from = from;
		this.to = to;
		this.go();
	},

	hide: function() {
		this.now = 0;
		this.increase();
	},

	clearTimer: function() {
		clearInterval(this.timer);
		this.timer = null;
	}
}
//fader
fx.Opacity = Class.create();
fx.Opacity.prototype = Object.extend(new fx.Base(), {
	initialize: function(el, options) {
		this.el = $(el);
		this.now = 1;
		this.increase();
		this.setOptions(options);
	},

	increase: function() {
		if (this.now == 1) this.now = 0.9999;
		if (this.now > 0 && this.el.style.visibility == "hidden") this.el.style.visibility = "visible";
		if (this.now == 0) this.el.style.visibility = "hidden";
		if (window.ActiveXObject) this.el.style.filter = "alpha(opacity=" + this.now*100 + ")";
		this.el.style.opacity = this.now;
	},

	toggle: function() {
		if (this.now > 0) this.custom(1, 0);
		else this.custom(0, 1);
	}
});