(function( $, pConfig ) {

	"use strict";

	/* *** Local variables *** */

	/* Just version and credits that will show in console log */
	var info = {
		version: "0.0.1-20190312-02", // just a manual version number for debugging: "Is it loading the code I *thought* I uploaded?" Recommend 0.0.0-YYYYMMDD-00 format
		handle:  "LHLP", // the uppercase short handle that shows in console log
		name: 	 "Library Help LTI - Course Page", // the name of the script
		author:  "University of St. Thomas Libraries", // author or organization credited with writing it
		code:    "github.com/ustlibraries" // github or other link for code - optional, leave "" if no public repository
	};

	// this can eithr be passed in as the second param (very bottom) or set here
	var configDefault = {
		silence: { allowToggle: true, default: false },
		allowMultipleExecutions: false, // no reason to ever set this as true
		apiURL: "", // set this to the location of the api
		linkTarget: "_blank"
	};

	const CONFIG = (function() {
		// someday this should iterate through all passed settings, including children
		if( pConfig.linkTarget !== undefined ) {
			configDefault.linkTarget = pConfig.linkTarget;
		}
		
		return configDefault;		
	})();

	/* Runtime Settings (Read/Write) */
	var settings = {
		silent: false, // does debug() output to console.log?
	};


/* *** Local Functions *** */
	

	/* =====================================================================
	 *  init()
	 *
	 *  Initial function called at runtime
	*/
	var init = function() {
		
		attribution();
		setSilence(CONFIG.silence.default);
		execute();

	};

	/* =====================================================================
	 *	debug()
	 *
	 *	If not silenced, outputs text passed to it to console.log
	 *
	 *	Need a line number? In your code use debug(yourmessage + " - Line:"+ (new Error()).lineNumber );
	 *
	 *	This function has a companion variable: silent
	 */
	var debug = function( text ) {

		// as long as we aren't silenced (silent === false)...
		if( !settings.silent ) {
			var d = new Date();
			var ts = d.getHours() +":"+ d.getMinutes() +":"+ d.getSeconds() +"."+ d.getMilliseconds();
			console.log(info.handle+" ["+ts+"] : " + text);
		}
	};


	/* =====================================================================
	 *  setSilence()
	 *
	 *  If silenced, debug() won't send messages to console.log
	*/
	var setSilence = function(silence){
		if ( silence !== settings.silent ) {
			if (CONFIG.silence.allowToggle ) {
				if( silence ) {
					debug("Silenced");
					settings.silent = true; // we do it last so that there was one final peep
				} else {
					settings.silent = false;
					debug("Unsilenced");
				}
			} else {
				settings.silent = CONFIG.silence.default;
			}
		}

	};

	/* =====================================================================
	 *  attribution()
	 *
	 *  Display info about the script in the command line
	*/
	var attribution = function(){
		debug("Loading " + info.name + " by " + info.author);
		debug("Version " + info.version);
		if(info.code !== "") { debug("Get Code: " + info.code); }
	};

	/* =====================================================================
	 *  API functions to get remote JSON data
	 */
	var xhrSuccess = function() { this.callback.apply(this, this.arguments); };

	var xhrError = function() { console.error(this.statusText); };

	var loadFile = function(sURL, fCallback) {
		var oReq = new XMLHttpRequest();
		oReq.callback = fCallback;
		oReq.arguments = Array.prototype.slice.call(arguments, 2);
		oReq.onload = xhrSuccess;
		oReq.onerror = xhrError;
		oReq.open("get", sURL, true);
		oReq.send(null);
	};

	var getAPI = function(url, display) {

		// Define the function we want to use to process the data, accepting a callback function as a parameter (which will be pased to it later)
		var process = function(callback) {
			var data = JSON.parse(this.responseText);
			callback(data);
		}; // end callback processing function

		// The actual call to the loadFile, passing the two functions we wish to execute
		loadFile (url, process, display);
	};

	/* ****************************************************************************
	 * EXECUTION
	 * ****************************************************************************
		Function that runs at execution time, invoked by init()
		All code goes in here
	 * ************************************************************************** */

	var execute = function() {
		
		/* ************************************************************************
		 * Import Date for Display
		 *
		 * When using Date() JavaScript does not apply the local date, instead it 
		 * uses UTC which is problematic if you just want to display a formatted 
		 * date. For example, if you were to set Date() to "2019-03-27" with no 
		 * time zone or time, and the client machine was in the US Central Time 
		 * Zone, it would import as "2019-03-26 6:00 PM" because it would treat 
		 * "2019-03-27" as "2019-03-27T00:00:00Z" (UTC) It always treats dates 
		 * as UTC unless specified otherwise. This is a problem if all you want 
		 * is a date ("2019-03-27") to show up as "March 27, 2019" no matter the 
		 * locale.
		 *
		 * What this function accomplishes is to not use UTC as the default and 
		 * to display a date as the passed date reguardless of the timezone 
		 * (unless specified).
		 *
		 * It should treat the date passed to it more as a string, and not a 
		 * date that allows comparisons or calculations.
		 *
		 * The return value is used for outputing to the user.
		 * 
		 * @param dateToImport "YYYY-MM-DDTHH:MM:SS" or "YYYY-MM-DD" format each
		 *  					each with or without the timezone "Z" or "-|+HHMM"
		 *
		 */

		var importDateForDisplay = function (dateToImport) {

			// our date object which is returned. It contains values as well as functions .timeString(), .dateString(), and .dateTimeSring()
			// type can be -1 (time only), 0 (date and time), or 1 (date only)
			var d = {
				"month": 0, "date": 0, "year": 0, "hour": 0, "minute": 0, "second": 0, "timezone": "", "type": 0, 
				"dateTimeString": function(display=0) {
					var s = (d.dateString(display) + " " + d.timeString());
					return s.trim();
				},
				"timeString": function() {

					var s = "";

					// if there is a time associated with the date then start making that human readable
					if( d.type !== 1 ) {
						var mer = d.hour < 12 ? "AM" : "PM";
						var hrs = d.hour > 12 ? d.hour - 12 : d.hour;
						if ( hrs == 0 ) { hrs = "12"; }
						s = " " + hrs + ":" + (d.minute < 10 ? '0' : '') + d.minute + mer;
					}
					return s.trim();
				},
				"dateString": function(display=0) {
					// because JavaScript doesn't
					var months = { 
						"abbr": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
						"long": ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
					};

					var leadZero = function (n) { return ((n < 10 ? '0' : '') + n); }

					var s = "";

					// if there is a date associated then start making it human readable
					if ( d.type !== -1 ) {
						switch(display) {
							case 4: // MONTH D, YYYY
								s = months.long[d.month-1] + " " + d.date + ", " + d.year;
								break; 
							case 3:// MON D, YYYY
								s = months.abbr[d.month-1] + " " + d.date + ", " + d.year;
								break;
							case 2: // MM/DD/YYYY
								s = leadZero(d.month) + "/" + leadZero(d.date) + "/" + d.year;
								break;
							case 1: // MM-DD-YYYY
								s = leadZero(d.month) + "-" + leadZero(d.date) + "-" + d.year;
								break;
							default: // YYYY-MM-DD
								s = d.year + "-" + leadZero(d.month) + "-" + leadZero(d.date);
						}

					}

					return s.trim();
				}
			};

			// extract the date parts
			var parseDate = function (theDate) {
				var arr = theDate.split("-");
				d.year = parseInt(arr[0]);
				d.month = parseInt(arr[1]);
				d.date = parseInt(arr[2]);
			}


			// extract the time parts
			var parseTime = function (theTime) {
				// remove and store any other time zone if present ("2019-01-03-0500") or ("2019-01-03T09:00:00-5000")
				var i = -1;
				if( theTime.indexOf('-')> -1) {
					i = theTime.indexOf('-');
				} else if ( theTime.indexOf('+')> -1) {
					i = theTime.indexOf('+');
				}

				if (i > -1) {
					d.timezone = theTime.substring(i, theTime.length);
					theTime = theTime.substring(0, i);
				}

				var arr = theTime.split(":");
				d.hour = parseInt(arr[0]);
				d.minute = parseInt(arr[1]);
				d.second = parseInt(arr[2]);
			}

			// remove and store the UTC time zone if present ("2019-01-03Z") or ("2019-01-04T09:00:00Z")
			if( dateToImport.indexOf('Z') > -1 ) {
				d.timezone = "UTC";
				dateToImport = dateToImport.substring(0, dateToImport.length - 1);
			}		

			// extract the date and time separately
			var theDt = dateToImport.split("T");

			if(theDt.length === 1) {
				if(theDt[0].indexOf('-') > -1) {
					d.type = 1;
					parseDate(theDt[0]);
				} else {
					d.type = -1;
					parseTime(theDt[0]);
				}
			} else if (theDt.length === 2) {
				d.type = 0;
				parseDate(theDt[0]);
				parseTime(theDt[1]);
			}

			return d;

		};
		

		// This is where you add all your functions. If using APIs don't forget to declare a function that will be excuted after the api data is returned
		// showData() is provided as an example
		
		var getBaseURL = function (homeDir) {

			// starting left to right : 
			// 1. get the pathname, split by ? and take the first part (effectively removing the query string if there is one)
			// 2. if there is a trailing or preceeding "/" remove it with replace()
			// 3. split the path into it's separate directories
			var myArray = (window.location.pathname.split("?")[0]).replace(/^\/+|\/+$/gm,'').split("/");

			// reverse to truncate after last lti (in case https://somedomain.com/something/lti/morestuff/lti/module)
			var index = myArray.reverse().indexOf(homeDir);

			// reverse again so it is in the original order, take the necessary first directories up to and including "lti" and join together into a path
			var path = myArray.reverse().slice(0, myArray.length - index).join("/");
			var url = "https://"+ window.location.hostname + "/" + path.replace(/^\/+|\/+$/gm,'');

			return url;
		}		

		var showData = function(data) {
			
			// let the console know how many visible citations we found
			debug("Found "+data.stats.citations_visible+" citations");

			// if we have available citations, display them
			if( data.stats.citations_visible !== 0 ) {
				
				// find the div element we will be placing course materials inside
				var listDiv = $("#lib-section-coursematerial-list");

				// loop through the sections
				data.readinglist.forEach(function(section){
					
					// debug info
					debug("======== SECTION ========");
					debug("ID  : " + section.id);
					debug("NAME: " + section.name);
					debug("DESC: " + section.description);
					
					// create the h3 section header and place the title inside
					var h = document.createElement("H3");
					$(h).text(section.name);
					
					// add the h3 section header
					$(listDiv).append(h);
					
					// if there is a description, add it
					if(section.description) {
						var p = document.createElement("P");
						$(p).text(section.description);
						$(listDiv).append(p);
					}
					
					// generate the ordered list element in which we will place items
					var ol = document.createElement("OL");
					
					// go through each item in the section
					section.items.forEach(function(item){
						
						// debug info
						debug("------------------------");
						debug("ID  : " + item.id);
						debug("LINK: " + item.link);
						debug("TEXT: " + item.text);

						// generate the list item element
						var li = document.createElement("LI");
						var hasFrom = false;
						var hasDue = false;

						// generate the link with various attributes.
						var a = document.createElement("A");
						$(a).attr('href', item.link);
						$(a).attr('data-category', 'Course Reserve'); // for analytics purposes
						$(a).attr('data-label', $("body").attr("data-course")+": "+item.text+" : "+item.link); // for analytics purposes
						$(a).attr('target', CONFIG.linkTarget);
						$(a).text(item.text);
						
						// generate the from div tag
						var fromDiv = null
						if ( item.from ) {
							fromDiv = document.createElement("DIV");
							$(fromDiv).addClass("small-note");
							$(fromDiv).text(item.from);						
						}

						// generate the due date div tag
						var dueDiv = null
						if ( item.due ) {
							
							// create the div element for the due date, give it a class, and put in the text
							dueDiv = document.createElement("DIV");
							$(dueDiv).addClass("date-advisory").addClass("small-note");
							$(dueDiv).text("(Due: " + importDateForDisplay(item.due).dateString(3) + ")");
						}

						// append the link (and due date and from info if present) to the li, and the li to the ol
						$(li).append(a);
						if ( dueDiv !== null  ) { $(li).append(dueDiv); }
						if ( fromDiv !== null ) { $(li).append(fromDiv); }
						$(ol).append(li);
					});
					
					// append the ol to the section
					$(listDiv).append(ol);
					
				});
				
				// we are now ready to show the course materials
				$("#lib-section-coursematerial").addClass("show");
			}

		}

		// we need to figure out where this script resides and where the api is
		var url = getBaseURL("lti")+"/api/readinglist/?course="+$("body").attr("data-course");
		debug("GETTING READING LISTS: "+url);
		
		// call the api
		getAPI(url, showData);	

	};


	/* ****************************************************************************
	 * RUN-TIME
	 * ****************************************************************************
		Code that runs on load, typically just an init which in turn calls execute()
		after some initial initialization is perfomed
	 * ************************************************************************** */

	// if there is a section for course material, then we should perform the operations
	if ($("#lib-section-coursematerial")) {
		init();
	}

})(jQuery, rlistConfig);

// this is for the instructor, admin, and feedback bars that appear when viewed in the LMS. It allows them to expand and collapse
$(document).ready( function() {

	"use strict";
	
	var toggleSiblingDiv = function () {
		var $sibling = $(this).next("div");
		if( $sibling.hasClass("show") ) {
			$sibling.removeClass("show").addClass("hide");
		} else {
			$sibling.removeClass("hide").addClass("show");
		}
	};
	
	// if we have an lti admin view section place the debug info there,
	// otherwise it will just be hanging out at the end of the page outside the body tag
	$(".management-section h2").on("click", toggleSiblingDiv);

});