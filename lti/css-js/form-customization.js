$(document).ready( function() {

	"use strict";

	var update = function() {
		console.log("Update Customizations: " + $(this).attr("name"));

		var useDefault = $("#field_recommended").prop("checked");
		var query = "";

		// get course info
		var c = $("#field_course");
		
		var u = $(c).val().toUpperCase();
		$(c).val(u); 
		

		query = "?" + $(c).attr("name") + "=" + $(c).val();
		
		var pref = function() {
			var q = "";
			
			$("form fieldset input[type=checkbox]").each(function() {
				//if ( $(this).prop("checked") !== ($(this).attr("data-default-value") === $(this).attr("value")) ) {
					q += "&" + $(this).attr("name") + "=" + ( $(this).prop("checked") ? 1 : 0);
				//}
			});
			
			$("form fieldset input[type=radio]:checked").each(function() {
				//if ( $(this).prop("checked") !== ($(this).attr("data-default-value") === $(this).attr("value")) ) {
					q += "&" + $(this).attr("name") + "=" + $(this).attr("value");
				//}
			});
			
			q += "&cdp=1";
			
			return q;
		};

		if( $(this).attr("name") === "recommended" && useDefault ) {
			// perform a reset
			console.log("Perform Preference Reset to Defaults");

			$("form fieldset.customOptions input[type=checkbox],form fieldset.customOptions input[type=radio]").each(function() {
				$(this).prop("checked", ($(this).attr("data-default-value") === $(this).attr("value")) ).prop("disabled", true);
			});
			
			$("form fieldset#secondary-dbexpand").hide();
			$("form fieldset.customOptions").hide();

		} else if( $(this).attr("name") === "recommended" && !useDefault ) {
			
			console.log("Enabling Preferences");
			
			// show the options
			$("form fieldset.customOptions").show();
			$("form fieldset#secondary-dbexpand").show();
			//set all checkboxes and radio buttons to default (recommended) values
			$("form fieldset.customOptions input[type=checkbox],form fieldset.customOptions input[type=radio]").prop("disabled",false);
			query += pref();
		} else if ( useDefault ) {
			// do nothing
			console.log("Do nothing");
		} else {

			console.log("Update URL");
			
			query += pref();

		}

		var pattern = $(c).attr("pattern");
		var re = new RegExp(pattern);

		// if the course ID is a valid format then requirement is met
		if( re.test( $(c).val() ) ) {
			$("#codeArea").addClass("showCodeArea").removeClass("disableCodeArea");

			var t = window.location.pathname.split('/');
			if( t[t.length-1] === "" ) { t.pop(); }
			t.pop();
			var path = t.join('/');
			var host = window.location.hostname;

			var url = "https://" + host + path + "/display/" + query;

			$("#preview").attr("href", url);
			//$("#code").val(iframe);
			$("#code").val(url);

			$("#codeArea").css("display", "block");
		} else {
			$("#codeArea").addClass("disableCodeArea");
		}


	};

	// enhance the page
	// change the min-height of the body to force the browser scroll bar
	$("body").css("min-height", window.innerHeight + 100);

	// if not iOS change the span#copy to look like an actionable item (iOS does not support js copy/paste)
	if ( $("body").attr("data-jqtoolkit-os") !== "iOS") {
		$("#copy").addClass("copybutton"); // the function is still there, we just don't visually present it as a button as we can't promise anything
	}

	// add the event handlers
	$("input[type!=text]").on("change", update); // field updates
	$("input[type=text]").on("input", update); // text field updates
	/*$("#copy, #code").on("click keypress", copy); // clicking on copy button*/

	// put focus on the course field
	$("#field_course").focus();

	
	var toggleDbExpand = function () {
		var t = $("#field_displayDatabases-1").prop("checked");
		$(document).jqtoolkit("toggle", {selector: "#secondary-dbexpand", test: t});
	};
	
	var toggleCustomOptions = function () {
		var t = !$("#field_recommended").prop("checked");
		$(document).jqtoolkit("toggle", {selector: "fieldset.customOptions", test: t});
	};
	
	toggleDbExpand();
	$("#field_displayDatabases-1").on("change", toggleDbExpand);
	
	toggleCustomOptions();
	
});