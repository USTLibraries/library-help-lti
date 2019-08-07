/* main js */

/*  ============================================================================================
    ********************************************************************************************
    JQTOOLKIT PLUGIN FUNCTION 
	********************************************************************************************
*/

(function( $ ) {

	"use strict";
	
/* +++++++++++++++++++++++++++++++++++++++++++++++++
   +++ Local variables +++ */
	
	/* Script info */
	var version = "0.1.0-20171017-01"; // just a manual version number for debugging and preventing unneccessary hair pulling: "Is it loading the code I *thought* I uploaded?"
	var code    = "github.com/";
	var handle  = "JQTOOLKIT";
	var name    = "JQuery Tookit";
	
	/* Settings (Read/Write) */
	var silent = false; // does debug() output to console.log?
	

/* +++++++++++++++++++++++++++++++++++++++++++++++++
   +++ Local Functions +++ */		


	 
	/* =====================================================================
		debug()

		If not silenced, outputs text pased to it to console.log 
		
		Need a line number? In your code use debug(yourmessage + " - Line:"+ (new Error()).lineNumber );

		This function has a companion variable: silent
	*/
	var debug = function( text ) {

		// as long as we aren't silenced (silent === false)...
		if( !silent ) {
			var d = new Date();
			var ts = d.getHours() +":"+ d.getMinutes() +":"+ d.getSeconds() +"."+ ("000"+d.getMilliseconds()).slice(-3);
			console.log(handle+" ["+ts+"] : " + text);
		}
	};
	
	
	/* =====================================================================
		setSilence()
	*/	
	var setSilence = function(silence){
		if( silence ) {
			debug("Silenced");
			silent = true;
		} else {
			silent = false;
			debug("Unsilenced");	
		}
	};
	
	var init = function() {
		
		var op = (function() {
			var o = [];

			debug( navigator.userAgent);

			// these are not exact, but serve our purpose, could be made more robust
			o.windows = ( navigator.userAgent.match(/windows/i) ? true : false);
			o.mac = ( navigator.userAgent.match(/macintosh/i) ? true : false);
			o.iOS = ( navigator.userAgent.match(/ipad|ipod|iphone/i) ? true : false);
			o.android = ( navigator.userAgent.match(/android/i) ? true : false);
			o.linux = ( navigator.userAgent.match(/linux/i) ? true : false);

			o.chrome = ( navigator.userAgent.match(/chrome/i) ? true : false);
			o.firefox = ( navigator.userAgent.match(/firefox/i) ? true : false);
			o.safari = ( navigator.userAgent.match(/safari/i) ? true : false);
			o.ie = ( navigator.userAgent.match(/msie/i) ? true : false);
			o.opera = ( navigator.userAgent.match(/opr\/|opera mini/i) ? true : false);
			o.edge = (navigator.userAgent.match(/edge/i) ? true : false);

			o.browser = (function() {
				var b = "Unassigned";

				if(o.chrome) { b = "chrome"; }
				else if(o.firefox) { b = "firefox"; }
				else if(o.safari) { b = "safari"; }
				else if(o.ie) { b = "ie"; }
				else if(o.opera) { b = "opera"; }
				else if(o.edge) { b = "edge"; }

				return b;

			})();

			o.os = (function() {
				var s = "Unassigned";

				if(o.windows) { s = "windows"; }
				else if(o.mac) { s = "mac"; }
				else if(o.iOS) { s = "iOS"; }
				else if(o.android) { s = "android"; }
				else if(o.linux) { s = "linux"; }

				return s;

			})();

			debug("OS: " + o.os + " BROWSER: " + o.browser);

			return o;
		})();
		
		var addAccessKeyToolTip = function() {
			var key = $(this).attr("accesskey");
			var title = $(this).attr("title");
			var combo = "";

			if (op.opera) {
				combo = "[Alt]";
			} else if (op.mac && (op.chrome || op.firefox || op.safari)) {
				combo = "[Control][Alt]";
			} else if (op.windows) {
				if (op.firefox) {
					combo = "[Alt][Shift]";
				} else {
					combo = "[Alt]";
				}
			} else if (op.linux && (op.firefox || op.chrome || op.opera)) {
				if (op.firefox) {
					combo = "[Alt][Shift]";
				} else {
					combo = "[Alt]";
				}
			} // no other supprt

			if (combo !== "") {
				title += " (" + combo + " + " + key + ")";
				$(this).attr("title", title);
			}

		};
		
		var copy = function(element) {
			// https://stackoverflow.com/questions/400212/how-do-i-copy-to-the-clipboard-in-javascript

			element.select();

			try {
				var successful = document.execCommand('copy');
				var msg = successful ? 'successful' : 'unsuccessful';
				debug('Copy text command was ' + msg);
				if(successful) {
					$(element).blur();
					//$(document).on("keydown", closeModal );
					displayModal({html: "Text copied to clipboard.", theme: "success"});
				}
			} catch (err) {
				debug('Oops, unable to copy'); // fail silently
			}
		};
		
		var addCopyMyself = function() {
			$(this).attr("readonly", "true");
			$(this).on("click", function() { copy(this); });
		};
		
		var addCopyThat = function() {
			var elementId = $(this).attr("data-jqtoolkit-copy");
			if ( elementId.charAt(0) !== "#") { elementId = "#"+elementId; } // check to make sure selector is an id
			$(this).on("click", function() { copy($(elementId)); });
		};

		$("body").attr({"data-jqtoolkit-os": op.os, "data-jqtoolkit-browser": op.browser, "data-jqtoolkit": "true" });
		// these should be just like atrakit and be added to whole body in case new come along
		$("[accesskey]").each(addAccessKeyToolTip); // add access key to aria and titles
		$(".jqtoolkit-copy-text").each(addCopyMyself); // add one click copy text functionality to elements
		$(".jqtoolkit-copy-action[data-jqtoolkit-copy]").each(addCopyThat); // add buttons that copy text from other areas
		
	};
	
	
	var toggleDisplayOf = function( selector, test ) {
		
		if ( selector === "" ) {
			selector = this;
		}
		
		if ( test === "" ) {
			$(selector).toggle();
			debug("Toggle:" + selector);
		} else {
			if (test) {
				$(selector).show();
				debug("Show:" + selector);
			} else {
				$(selector).hide();
				debug("Hide:" + selector);
			}	
		}
		
		
		
	};
	
	var displayModal = function( param ) {
		if (typeof param.images === 'undefined') { param.images = []; }
		if (typeof param.links === 'undefined') { param.links = []; }
		if (typeof param.theme === 'undefined') { param.theme = "light"; } // light, dark, info, success, advisory, critical 
		if (typeof param.background === 'undefined') { param.background = ""; }
		if (typeof param.color === 'undefined') { param.color = ""; }
		if (typeof param.html === 'undefined') { param.html = ""; }
		if (typeof param.maxHeight === 'undefined') { param.maxHeight = ""; }
		if (typeof param.minHeight === 'undefined') { param.minHeight = ""; }
		if (typeof param.maxWidth === 'undefined') { param.maxWidth = ""; }
		if (typeof param.minWidth === 'undefined') { param.minWidth = ""; }
		if (typeof param.opacityBehind === 'undefined') { param.opacityBehind = ""; }
		if (typeof param.colorBehind === 'undefined') { param.colorBehind = ""; }
		if (typeof param.closeOn === 'undefined') { param.closeOn = ""; } // will close on X and clicking on background, but what about keypress or click anywhere?
		
		var closeModal = function() {
			debug("Close Modal");
			$(document).off("keydown click", closeModal );
			$(".jqtoolkit-modal-wrapper").remove();
		};
		
		var modalWrapper = document.createElement("div");
		$(modalWrapper).addClass("jqtoolkit-modal-wrapper");
		
		var modal = document.createElement("div");
		$(modal).addClass("jqtoolkit-modal").addClass("jqtoolkit-modal-theme-"+param.theme);
		
		var modalContainer = document.createElement("div");
		$(modalContainer).addClass("jqtoolkit-modal-container");
		
		var modalContent = document.createElement("div");
		$(modalContent).addClass("jqtoolkit-modal-content");
		$(modalContent).append(param.html);
		
		var modalCloseButton = document.createElement("span");
		$(modalCloseButton).addClass("jqtoolkit-modal-closeButton").attr({tabindex: "1", "aria-label": "Close"}).html("×");
		
		
		// put it all together
		$(modalCloseButton).appendTo(modalContainer);
		$(modalContent).appendTo(modalContainer);
		$(modalContainer).appendTo(modal);
		$(modal).appendTo(modalWrapper);
		
		$(modalWrapper).appendTo("body");
		$(modalCloseButton).on("click", closeModal);
	};
	
	
	$.fn.jqtoolkit = function ( action, param ) {

		// ECMAScript 6 allows default in function, but not all code editors and minifiers support it
		// in future: $.fn.atrakit = function ( action="init", param={} )
		if (typeof action === 'undefined') { action = "init"; }
		if (typeof param === 'undefined') { param = {}; }
		
		switch ( action.toLowerCase() ) {
 
			case "init":
				init();
				break;
				
			case "toggle": // toggle is more than toggle, it also shows and hide based upon true and false if passed so state may stay same
				if ( typeof param.selector === 'undefined' ) { param.selector = $(this); }
				if ( typeof param.test === 'undefined' ) { param.test = ""; }
				toggleDisplayOf(param.selector , param.test );
				break;
		 
			case "config":
				if ( typeof param.silence !== 'undefined' ) { setSilence(param.silence); }
				break;
				
			case "modal":
				displayModal( param );
				break;

			default:
				debug("Unknown Command for jqtoolkit(): "+ action);
		}
		
		return this;
	};	
		
		
/* +++++++++++++++++++++++++++++++++++++++++++++++++
   +++ On Ready/Loaded +++ */		


	// let the devs know a little about the script in console.log
	debug("Loaded "+name+" ("+code+") [ver"+version+"]");
	
	$(document).jqtoolkit("init");
	
	
/* +++++++++++++++++++++++++++++++++++++++++++++++++
   +++ Done! +++ */		

 
}( jQuery ));
	 

/*  ********************************************************************************************
        END -- JS TOOLKIT
    ********************************************************************************************
    ============================================================================================ 
*/

/*  ============================================================================================
    ********************************************************************************************
    ATRAKIT PLUGIN FUNCTION 
	********************************************************************************************
*/
/** @preserve ATRAKIT: VIEW DOC|CODE|LIC @ github.com/chadkluck/atrakit */
"undefined"==typeof atrakit&&(atrakit=!1),function(t){var e="0.1.3-20170118-01",a="github.com/chadkluck/atrakit",n="ATRAKIT",i="Analytics TRAcking toolKIT",r=!1,o=!1,c="txt|pdf|pptx?|xlsx?|docx?|mp(3|4|eg)|mov|avi|wmv|wav|zip|jar|gif|jpe?g|png|exe|css|js",f={a:"click",button:"click",form:"submit",select:"change",input:"change"},d={TRAD:{desc:"Using Traditional Google Analytics (ga.js)",eventfn:function(t,e,a){pageTracker._trackEvent(t,e,a)},pagefn:function(t){pageTracker._trackPageview(t)},detectfn:function(){return"undefined"!=typeof gaJsHost}},CLAS:{desc:"Using Async Classic Google Analytics (ga.js async)",eventfn:function(t,e,a){_gaq.push(["_trackEvent",t,e,a])},pagefn:function(t){_gaq.push(["_trackPageview",t])},detectfn:function(){return"undefined"!=typeof _gaq}},UNIV:{desc:"Using Universal Analytics (analytics.js)",eventfn:function(t,e,a){ga("send","event",t,e,a)},pagefn:function(t){ga("send","pageview",t)},detectfn:function(){return"undefined"!=typeof ga}},GTMX:{desc:"Using Google Tag Manager",eventfn:function(t,e,a){},pagefn:function(t){},detectfn:function(){return"undefined"!=typeof dataLayer}},NONE:{desc:"No Analytics Detected",eventfn:function(t,e,a){},pagefn:function(t){},detectfn:function(){return!1}}},s=null,l=0,u=["Track Only","Tag and Track","Tag Only"],p=function(t){if(!r){var e=new Date,a=e.getHours()+":"+e.getMinutes()+":"+e.getSeconds()+"."+e.getMilliseconds();console.log(n+" ["+a+"] : "+t)}},g=function(t){t?(p("Silenced"),r=!0):(r=!1,p("Unsilenced"))},y=function(){return atrakit},k=function(t){return atrakit=t===!0,y()},v=function(t){t?(p("Event types will be sent with Action Attributes"),o=!0):(p("Event types will NOT be sent with Action Attributes"),o=!1)},h=function(t){d.addElem(t),s=null,U()},b=function(t){c=t},m=function(e){var a=o?a=" ("+e.type+")":"",n=t(this);n.atrakitAdd("data"),N(n.attr("data-category"),n.attr("data-action")+a,n.attr("data-label"))},w=function(e,a){var n=new Date;if(t(document).atrakitGet("gaFlavor"),e.is(document)&&(e=t(document.getElementsByTagName("BODY")[0])),"undefined"==typeof e.prop("tagName"))p("Tracking selector returned 0 results");else{if(p("Init on for "+e.prop("tagName")+": "+u[a+1]),a>=0){var i="",r="[data-category][data-action][data-label], [data-atrakit='false']";i="form, select:required, input:required, a[href=''], a[href^='javascript:'], a[href^='mailto:'], a[href^='tel:'], a.button, button, [data-atrakit='true'], [data-atrakit-event]",e.find(i).not(r).atrakitAdd("data"),i="a[href*='#'], a[href*='.']",e.find(i).not(".button, "+r).atrakitLinkFilter().atrakitAdd("data")}if(a<=0){var o="";Object.keys(f).forEach(function(t){e.on(f[t],t+"[data-category]:not([data-atrakit-event])",m),o=o+t+", "},f),e.find("[data-category][data-atrakit-event]").each(function(){t(this).on(t(this).attr("data-atrakit-event"),m)}),e.on("click","[data-category]:not("+o+"[data-atrakit-event])",m)}}var c=Math.abs(new Date-n);p("Init of "+l+" elements completed in "+c+" milliseconds")},T=function(e,a){"undefined"==typeof a&&(a={});var n=function(t,e){switch(t){case"category":0===i&&(i=e);break;case"action":0===r&&(r=e);break;case"label":0===o&&(o=e)}},i=0,r=0,o=0,c=!1;if(a!=={}&&(i="undefined"!=typeof a.category?a.category:0,r="undefined"!=typeof a.action?a.action:0,o="undefined"!=typeof a.label?a.label:0,c=!0),0===i&&(i="undefined"!=typeof t(e).attr("data-category")?t(e).attr("data-category"):0),0===r&&(r="undefined"!=typeof t(e).attr("data-action")?t(e).attr("data-action"):0),0===o&&(o="undefined"!=typeof t(e).attr("data-label")?t(e).attr("data-label"):0),l++,!i||!r||!o){var f=function(e){var a="";if("undefined"!=typeof t(e).closest("[id]").attr("id")){var n=t(e).closest("[id]");a=n.prop("tagName")+"#"+n.attr("id")+" "}return a},d=function(e){return"undefined"!=typeof t(e).attr("id")?"#"+t(e).attr("id"):" "},s=t(e).prop("tagName").toUpperCase();if("A"===s)if(t(e).attr("href")){var u=A(t(e));if(u.isDownload)n("category","Download"),n("action","Download ("+u.type+")"),n("label",u.name+" ("+u.path+")");else{var p=t(e).attr("href");/^mailto:/i.test(p)?(n("category","Contact"),n("action","Email"),n("label",p)):/^tel:/i.test(p)?(n("category","Contact"),n("action","Telephone"),n("label",p)):/^javascript:/i.test(p)?(n("category","Script"),n("action","Link"),n("label","'javascript:' in HREF")):(n("label",t(e).attr("href")),n("action","Link"))}}else{var g="undefined"!=typeof t(e).attr("id")?"#"+t(e).attr("id")+" ":"",y=t(e).text()?t(e).text():"Empty Text";n("label","A"+g+" "+y+" (No HREF)")}else if("INPUT"===s||"SELECT"===s)n("label",f(e)+s+d(e)+"[name='"+t(e).attr("name")+"']");else if("FORM"===s){n("action","Form Submitted");var k="undefined"!=typeof t(e).attr("id")?"#"+t(e).attr("id"):"";t(e).attr("action")?n("label","FORM"+k+" submitted to "+t(e).attr("action")):n("label","FORM"+k+" submitted from "+window.location.href)}else t(e).attr("id")?n("label",s+"#"+t(e).attr("id")):t(e).closest("[id]")?n("label",t(e).closest("[id]").prop("tagName")+"#"+t(e).closest("[id]").attr("id")+" "+s):t(e).attr("title")?n("label",s+" "+t(e).attr("title")):t(e).closest("[title]")?n("label",t(e).closest("[id]").prop("tagName")+" "+t(e).closest("[title]").attr("title")+" "+s):n("label",s+" at "+window.location.href);n("action","Event Trigger"),n("category",s),c=!0}c&&t(e).attr({"data-label":o,"data-action":r,"data-category":i})},E=function(e){if(t(e).attr("data-category")&&t(e).attr("data-action")&&t(e).attr("data-label")){var a="click";if(t(e).attr("data-atrakit-event"))a=t(e).attr("data-atrakit-event"),t(e).on(a,m);else{var n=t(e).prop("tagName"),i={};try{Object.keys(f).forEach(function(t){if(n===t)throw a=f[t],i},f)}catch(t){if(t!==i)throw t}}t(e).on(a,m)}else p("No data tags so no event added")},A=function(e){var a={isDownload:!1};if("A"===t(e).prop("tagName").toUpperCase()){var n=t(e)[0].pathname,i=new RegExp("("+c+")$","gi");if(i.test(n)){var r=n.substring(n.lastIndexOf("/")+1),o=r.substring(r.lastIndexOf(".")+1).toUpperCase(),f=t(e)[0].hostname+n;a={isDownload:!0,name:r,type:o,path:f}}}return a},N=function(t,e,a){O().eventfn(t,e,a),p("Event Triggered (category: '"+t+"', action: '"+e+"', label: '"+a+"') Sent To: "+O().name)},O=function(){if("undefined"==typeof s||null===s){s=d.NONE,s.name="NONE";var t={};try{Object.keys(d).forEach(function(e){if(this[e].detectfn())throw s=d[e],s.name=e,t},d)}catch(e){if(e!==t)throw e}p(s.desc)}return s},C=function(t){return t===O().name},U=function(){return"NONE"!==O().name};t.fn.atrakitAdd=function(t,e){return"undefined"==typeof t&&(t="both"),"undefined"==typeof e&&(e={}),this.each(function(){switch(t.toLowerCase()){case"data":T(this,e);break;case"event":E(this);break;case"both":T(this,e),E(this);break;default:p("Unknown Command for atrakitAdd(): "+t)}})},t.fn.atrakit=function(e,a){switch("undefined"==typeof e&&(e="init"),"undefined"==typeof a&&(a={}),e.toLowerCase()){case"init":var n=0;"undefined"!=typeof a.tagOnly&&(n=a.tagOnly?1:n),"undefined"!=typeof a.trackOnly&&(n=a.trackOnly?-1:n),w(t(this),n);break;case"config":"undefined"!=typeof a.silence&&g(a.silence),"undefined"!=typeof a.listEventType&&v(a.listEventType),"undefined"!=typeof a.customAnalytic&&h(a.customAnalytic),"undefined"!=typeof a.fileTypes&&b(a.fileTypes);break;default:p("Unknown Command for atrakit(): "+e)}return this},t.fn.atrakitGet=function(t,e){"undefined"==typeof t&&(t="gaFlavor"),"undefined"==typeof e&&(e="");var a="";switch(t.toLowerCase()){case"gaflavor":a=O();break;case"is":""!==e?a=C(e.toUpperCase()):p("Empty arg for atrakitGet('is', arg)");break;default:p("Unknown Command for atrakitGet(): "+t)}return a},t.fn.atrakitLinkFilter=function(){var e=window.location.hostname.replace(/\./g,"\\."),a="(https?\\:)?\\/\\/(?!"+e+")",n="(("+e+"){0,1}.*#)|^\\s*$",i="\\.(?:"+c+")(?=\\?([^#]*))?(?:#(.*))?",r="("+a+")|("+i+")|("+n+")";return this.filter(function(){var e=new RegExp(r,"gi");return e.test(t(this).attr("href"))})},k(!0),p("Loaded "+i+" ("+a+") [ver"+e+"]"),U()}(jQuery);

/*  ********************************************************************************************
        END -- ATRAKIT
    ********************************************************************************************
    ============================================================================================ 
*/

$(document).ready( function() {

	"use strict";
	
	// if we have an lti admin view section place the debug info there,
	// otherwise it will just be hanging out at the end of the page outside the body tag
	$("#lib-admin-section div").append( $("#debug-info") );

});

// init ATRAKIT to track outbound links
$(document).ready( function () { $(document).atrakit("init"); }); // comment out if not using the ATRAKIT PLUGIN FUNCTION code above