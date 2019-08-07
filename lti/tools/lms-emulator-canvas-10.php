<?php

require_once __DIR__."/../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require_once __DIR__."/../inc/inc-tool-access-check.php";

$pageName = getCfg("lti")['name'] . ": LMS Emulator";
$needsSetup = ( getCfg('lti')['oauth_clientid'] === "" || getCfg('app-secrets')['lti']['oauth_secret'] === "" ); // make sure client_id and secret are set
$formFields = array();
$loaderData = array();
$submitted = false;
$postFields = array();

$requestUrl = getBaseURL() . "/app/launch.php";

if (!$needsSetup) {
    
    // load defaults from emulator-test.json document
    $str_data = file_get_contents(__DIR__."/../custom/data/emulator-test.json");
    $data = json_decode($str_data, true);

    // set defaults
    $formFields = $data['default'];
    $loaderData = $data;
    
    // If form was submitted
    if (getParameter("submit", "POST")) {
        
        $submitted = true;
        
        // load in post values
        // do a one to one load except for roles
        
        foreach ($formFields as $key => $entries) {
            
            $fieldValue = "";
            $userFormValue = "";
            
            $pre = ( array_key_exists("prepend", $entries) ) ? $entries['prepend'] : ""; // some fields may have a prepend that is not included in the stated values
            $append = ( array_key_exists("append", $entries) ) ? $entries['append'] : "";

            switch ($entries['type']) {
                case "multi":
                    foreach (getParameter($key) as $selectedOption){
                        $fieldValue .= $pre . $selectedOption . $append . ","; // for emulator (hidden form submitted to IFRAME)
                        $userFormValue .= $selectedOption . ","; // for user edits
                    }
                    $fieldValue = substr($fieldValue, 0, -1); // remove the trailing ,
                    $userFormValue = substr($userFormValue, 0, -1); // remove the trailing ,
                    break;
                default:
                    $fieldValue = $pre . getParameter($key) . $append; // for emulator
                    $userFormValue = getParameter($key); // for user edits
            }

            $postFields[$key] = $fieldValue;
            $formFields[$key]['value'] = $userFormValue; // this updates the user editable form fields

        }
        
        $postFields['lti_message_type'] = "emulator"; // post variable lti_message_type = "basic-lti-launch-emulator"; // on display.php change so that there is no save option
            
        // using oauth, sign the request
        $oauth = generateOAuth($postFields, $requestUrl);
    }
    

}

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageName ?></title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body>
<?php
	include(__DIR__."/../inc/inc-tool-header.php");
?>

<div>


	<h1><?php echo $pageName ?></h1>

    
    <p>This emulator may be used to test the LTI's configuration in a simulated environment with or without access to an actual LMS.</p>
    


	<?php

    if ($needsSetup) {

        ?>
        <h2>LMS settings required first</h2>

        <p>Before using the emulator you need to configure an LMS connection even if you do not have access to an LMS at this time.</p>

        <p class="lti-settings"><a href="lms-setup-10.php" class="button">Configure LMS</a></p>
        <?php
        
    } else {
    
        ?>
    
        <style>
            form.emulator div {
                display: block;
                width: 100%;
            }

            form.emulator > div > div:first-child,
            form.emulator > div > div:last-child {
                font-family: Consolas, "Andale Mono", "Lucida Console", "Lucida Sans Typewriter", Monaco, "Courier New", "monospace";
            }

            form.emulator > div > div:first-child {
                padding-top: 1.5rem;
                font-weight: bold;
                color: black;
            }

            form.emulator input[readonly] {
                color: blue;
                background-color: #f5f5f5;
                font-family: Consolas, "Andale Mono", "Lucida Console", "Lucida Sans Typewriter", Monaco, "Courier New", "monospace";
            }

            form.emulator input[type=text] {
                width: 100%
            }
            
            .hide {
                display: none;
            }
        </style>

    
        <?php
        
        // If the form was submitted, load the LTI into an IFRAME
        
        if ($submitted) {
            ?>
                <h2>Preview</h2>

                <div class="tool_content_wrapper" style="height: 992px;">

                    <form action="<?php echo $requestUrl; ?>" class="hide" method="POST" target="tool_content" id="tool_form" data-tool-launch-type="" data-tool-id="stthomas_library_help" data-tool-path="/lti/app/launch.php" data-message-type="tool_launch" style="display: none;">

                        <?php

                            $postForm = "";
                            foreach ( array_merge($postFields, $oauth['fields']) as $key => $value) {
                                $postForm .= "\n<input name='".$key."' id='".$key."' value=\"".$value."\" type='hidden'>";
                            }

                            echo $postForm;

                        ?>
                        <div style="margin-bottom: 20px;">
                            <div class="load_tab">
                                This tool needs to be loaded in a new browser window
                                <div style="margin: 10px 0;">
                                    <button class="btn" type="submit" data-expired_message="The session for this tool has expired. Please reload the page to access the tool again">
                                        Load Library Help in a new window
                                    </button>
                                </div>
                            </div>
                            <div class="tab_loaded" style="display: none;">
                                This tool was successfully loaded in a new browser window. Reload the page to access the tool again.
                            </div>
                        </div>
                    </form>

                    <iframe src="about:blank" name="tool_content" id="tool_content" class="tool_launch" allowfullscreen="allowfullscreen" webkitallowfullscreen="true" mozallowfullscreen="true" tabindex="0" title="Tool Content" style="height:100%;width:100%;" allow="geolocation; microphone; camera; midi; encrypted-media; autoplay"></iframe>

                </div>

                <script>
                    // this submits the hidden form which loads the launch.php page into the iframe
                    window.onload=function(){
                        document.getElementById("tool_form").submit();
                    }
                </script>

            <?php
        }
        ?>
    
        <h2>Load preset variables</h2>
        <p>The emulator-test.json file may be edited to provide some demo courses to test configurations. Use it to make sure guides, subjects, librarians, and course lists show up as expected. See: <a href="lms-emulator-canvas-11.php">LMS Emulator Test Data</a> for more information about creating test courses.</p>

        <?php
        
        // generate the loader selection menu
        $loader = "\n<select id='loadSaved' name='loadSaved'>";
        
        $loader .= "<option value='' selected='selected'></option>";
        
        foreach ($data as $key => $value) {
            $loader .= "\n\t<option value='".$key."'>".$key."</option>";
        }
        
        $loader .= "\n</select>";
        
        echo $loader;
        $loader = "";
        
        ?>
        
        <h2>Submit values</h2>
        <p>NOTE! <code>custom_lri_id</code> field MUST be unique when switching courses, otherwise the LTI will fail in switching between courses both in the emulator and in the LMS.</p>
    
        <?php
        // generate the form
        $form = "<form method='post' action='' id='dataForm' name='dataForm' class='emulator'>\n";
        
    	foreach ($formFields as $key => $value) {
            $form .= "\n\t<div>\n\t\t<div>".$key."</div><div>";
            
            switch ($value['type']) {
                case "multi":
                    $form .= "<select id='emulator_".$key."' name='".$key."[]' multiple>";
                    $options = explode(",", $value['options']);
                    $currVal = explode(",", $value['value']);
                    foreach ($options as $option) {
                        
                        // We will mark any current values as selected
                        $selected = "";
                        
                        // This function may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE, so we mark as TRUE only if it is not FALSE
                        if ( !(array_search($option, $currVal) === false) ) {
                            $selected = " selected='selected'";
                        }
                        
                        $form .= "<option value='".$option."'".$selected.">".$option."</option>\n";
                    }
                    $form .= "</select>";
                    break;
                default:
                    $disabled = $value['type'] === "readonly" ? " readonly " : ""; // we prefer readonly rather than the disabled attribute so that the user can select and copy the text in the field. Don't know the use case for it but hey, why not give a free feature?
                    $form .= "<input type='text' id='emulator_".$key."' name='".$key."' value=\"".$value['value']."\"" . $disabled . ">";
            }

            $form .= "</div>\n\t\t</div>";

        }

        $form .="\n<input type='submit' name='submit' value='Submit'>";

        $form .= "\n</form>";

        echo $form;
        $form = "";

        ?>

        <script>
        // add on change to loader

            (function() {
                

                var load = function ( dataSetName ) {
                    
                    // load in the json data from emulator settings
                    var loaderData = JSON.parse('<?php echo json_encode($loaderData, JSON_HEX_APOS); ?>');

                    console.log("---- Loading values for " + dataSetName + " ----");
                    
                    // load the default values
                    // we need to do a deep copy, and make sure we have a copy, not a reference! - crazy JavaScript ;)
                    // https://scotch.io/bar-talk/copying-objects-in-javascript
                    let newObj = JSON.parse(JSON.stringify(loaderData['default']));
                    //emulateData = loaderData['default'];
                    console.log("BEFORE (default): ", newObj);

                    //const emulateDataCopy = dataSetName !== "default" ? Object.assign(emulateData, loaderData[dataSetName]) : emulateData;
                    
                    
                    
                    if ( dataSetName !== "default" ) {
                        
                        // we need to do a deep copy, and make sure we have a copy, not a reference! - crazy JavaScript ;)
                        // https://scotch.io/bar-talk/copying-objects-in-javascript
                        const obj  = JSON.parse(JSON.stringify(loaderData[dataSetName]));
                        
                        // iterate through the dataset we need and copy over values as a deep copy
                        for ( const [field, fieldData] of Object.entries(obj)) {
                            console.log("Update value: " + field + " with " + fieldData['value']);
                            
                            for ( const [key, value] of Object.entries(fieldData)) {
                                //console.log(key, value);
                                newObj[field][key] = value;
                            }
                            
                        }
                    }
                    
                    console.log("AFTER (default + " + dataSetName + "): ", newObj);
                    loadFields( newObj );
                    //loadFields( emulateDataCopy );

                };

                var loadFields = function(data) {

                    console.log("---- Updating fields...");

                    const obj = data;
                    for ( const [key, value] of Object.entries(obj)) {
                        var fieldID = "emulator_"+key;
                        console.log("Field ID " + fieldID + " with " + value['value']);
                        
                        switch (value['type']) {
                            case "multi": 
                                
                                var options = document.getElementById(fieldID).options; // get the current list of selectors
                                var savedVal = value['value'].split(","); // get the saved values and put them into an array

                                // check each option one by one and see if it needs to be selected
                                for ( const [selector, entry] of Object.entries(options) ) {
                                    if ( savedVal.indexOf(entry.value) !== -1 ) {
                                        entry.selected = true;
                                    } else {
                                        entry.selected = false;
                                    }
                                }
                                break;
                            default:
                                document.getElementById(fieldID).value = value['value'];
                        }
                        
                    }

                };

                document.getElementById("loadSaved").onchange = function (e) {
                    console.log("Emulated Data Changed To: " + this.value);
                    load( this.value );
                }

            })();

        </script>

        <?php
    }
    
    ?>
    
    <!-- end page content -->



</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>