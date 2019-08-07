<?php

require_once __DIR__."/../../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init

$app['page'] = "getlink";

function defaultValue($v) {
	$s = "";
	
	$s .= "data-default-value=\"".$v."\"";
	if ($v) { $s .= " checked"; }
	
	return $s;
}

/* **********************************************
 *  START
 */


?><!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width"/>
<title>Generate Code for Library Integration</title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/getlink.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body>
	<div>

		<h1>Library Resources in Canvas</h1>
	
		<p>In most cases following the &quot;Drag and Drop&quot; method described in Option 1 will be all you need. You can experiment to see how your Library Help page will appear by using the Course ID field and preview link below.</p>
	
		<p>To get started you will need your Course ID. Any of the following formats are acceptable:</p>
		<ul>
			<li><tt>PHIL201</tt> (Deptartment and Course Number)</li>
			<li><tt>PHIL201-01</tt> (Course Number and Section)</li>
			<li><tt>PHIL201-LAMBERT</tt> (Course Number and last name of instructor for when an instructor teaches multiple sections)</li>
			<li><tt>PHIL-LAMBERT</tt> (Department and last name of instructor for when an instructor uses course guides or reading lists for multiple courses)</li>
		</ul>
		<p>The system will do a best match going up the search hierarchy (if no PHIL201-02 found it will try PHIL201 then PHIL). If you would like a Library Course Guide developed specific to your course please contact your subject librarian.</p>
	
		<p>Course ID is the ONLY required field and is all you need to get started.</p>
	
		<h2>Step 1: Generate the preview link</h2>
	
	<form onsubmit="event.preventDefault();" class="lti-settings">
		
		<?php echo getPreferenceFormHTML() ?>
		
	</form>
	<script src="<?php echo getJSdirectoryUrl(); ?>/form-customization.js"></script>
	
	<div id="codeArea" class="lti-settings">
	
		<h2>Step 2: Preview &amp; Copy the link</h2>
		
		<p><a href="" id="preview" target="_blank" tabindex="1" title="Preview selected library resources (Opens in new window)" aria-label="Preview selected library resources (Opens in new window)" accesskey="p">Preview</a></p>
		
		<p><span id="copy" class="jqtoolkit-copy-action" data-jqtoolkit-copy="#code" title="Copy code" tabindex="1" accesskey="c" aria-label="Copy code">Copy</span> <span>the link below. You will need this IF you use the &quot;Redirect Tool App&quot; to add Library Help to your course.</span></p>
		
		<textarea class="jqtoolkit-copy-text" id="code" tabindex="1" aria-label="Copy this link into your course"></textarea>
		
		<h2>Step 3: Add Library Help to your course</h2>
		
		<p>The recommended option (Option A: Drag, Drop, and Save!) is a simple 4 step drag and drop into your navigation menu. The alternate option (Option B: Use Canvas Redirect Tool App) may be used to customize what is presented if necessary.</p>
		
		<h3>Option A: Drag, Drop, and Save!</h3>
		
		<p>The easiest solution for most courses.</p>
		
		<ol>
			<li>Go into the course settings</li>
			<li>Click on the Navigation tab</li>
			<li>Drag Library Help from the inactive section to the active section</li>
			<li>And Save!</li>
		</ol>

		<img src="<?php echo getCustomDirectoryUrl()?>/img/canvas-library-help-steps.png" style="width: 100%; margin-top: 1rem; margin-bottom: 1rem;"/>
		
		<h3>Option B: Use Canvas Redirect Tool App</h3>
		
		<ol>
			<li>Go into your <a href="https://stthomas.instructure.com" target="_blank">Canvas course</a> and from within your course navigation go to &quot;Settings&quot;</li>
			<li>Click on the app filter box and start typing in &quot;Redirect Tool&quot;</li>
			<li>Click on the app button.</li>
			<li>Click on &quot;+ Add App&quot;</li>
			<li>In the &quot;Name&quot; field name it &quot;Library Help&quot;. This is the label that will show up in your course navigation (below Home, Syllabus, Modules, etc).</li>
			<li>In the &quot;URL Redirect&quot; field paste in the link copied from the generator above.</li>
			<li>Uncheck &quot;Force open in new tab&quot;</li>
			<li>Check &quot;Show in Course Navigation&quot;</li>
			<li>Click on the &quot;Add App&quot; button.</li>
			<li>You're done! You may need to hit refresh for the link to show up in your course navigation.</li>			
		</ol>
		
	</div>

	<h2>Feedback</h2>
		
	<p>Thank you for incorporating library resources into your course on Canvas! Your feedback is important to us as we continue to develop and improve access to library resources from within Canvas.</p>
		
	<p>If you used the Drag and Drop method there will be a feedback section on the Library Help page that only shows up in the instructor view (not the student view) with a link to a feedback form. Otherwise, you may send your feedback through our <a href="https://www.stthomas.edu/libraries/ask/askalibrarianform/">Ask a Librarian page</a>.</p>
	
	<h2>Troubleshooting</h2>
	
	<h3 id="help-matching">Course Matching</h3>
	<p>The system performs hierarchical matching from the order of most specific (201710PHIL201-01) to least specific (PHIL). If, for example PHIL201-01 is given in the Course ID field but there is no match for PHIL201-01 then PHIL201 is tried. If, still no match, PHIL is tried for general subject matter. If an instructor has multiple sections of the same course then a last name may be added instead of a section (PHIL201-LAMBERT). Also, if an instructor has a guide or Course Reserve for all their courses then PHIL-LAMBERT may be used.</p>

</div>

<?php echo getTrackingHTML(); ?>
	
<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/getlink.js"></script>



</body>
</html>
<?php appExecutionEnd(); ?>
