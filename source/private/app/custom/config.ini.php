;<?php
;die(); // contents of this file shouldn''t be seen on the web.
;/*
; This is your configuration file
; Comments start with ';' as in php.ini

;   ============================================================================================
;   ********************************************************************************************

;	APPLICATION CONFIG FILE
;	Library Help LTI (Canvas) | github.com/ustlibraries/library-help-lti
;	University of St. Thomas Libraries
;	stthomas.edu/libraries
;	Last Modified: 02-24-2019 - clkluck

;	********************************************************************************************

;		This is function template file from the PHP PROJECT FRAMEWORK library.
;		Visit github.com/chadkluck/php-project-framework page for more information.
;		FRAMEWORK FILE: config/config.ini.php
;		FRAMEWORK FILE VERSION: 2020-04-18

;	********************************************************************************************
;	============================================================================================


; NOTE: plain text and numbers can be entered without quotes.
;       For more on editing ini files visit: http://php.net/manual/en/function.parse-ini-file.php

; NOTE: Regular Expressions are used. I recommend one of these testers:
;       https://regex101.com/
;       https://www.regextester.com/



[header]
; =======================================================================================
; HTTP HEADER SETTINGS
; These are values that configure the way requests are received and responded to


timezone = "America/Chicago"
; FORMAT:      String
; DESCRIPTION: What timezone should be used? For a list of acceptable values see: http://php.net/manual/en/timezones.php
; DEFAULT:     "UTC"


allow-origin = "/^https?:\/\/(?:(?:[a-z0-9\-]+\.)*yourinst\.edu|yourinst\.(?:[a-z0-9\-]+\.)*instructure\.com)$/i"
; FORMAT:      Regular Expression (eg "/foo/i")
; DESCRIPTION: What websites should be allowed to embed these pages? Typical for CORS for pulling the page into an iframe or javascripts accessing the api. Leave blank if you want to allow any website
; RECOMMENDED: "/^https?:\/\/(?:(?:[a-z0-9]+\.)*yourdomain\.com)$/i"
; DEFAULT:     ""


bad-origin-allow-ip = "/^10\./"
; FORMAT:      Regular Expression e.g. "/^10\./"
; DESCRIPTION: If the request is made by a site not allowed to embed, what IP range is allowed to override?
; RECOMMENDED: "/^10\./" (or whatever ip range your local workstation, or a range of servers are)
; DEFAULT:     ""


api-cache = 3600
page-cache = 3600
; FORMAT:      Integer 0 through whatever
; DESCRIPTION: The number of seconds the browser should hold an api/page call in it''s cache
; RECOMMENDED: 3600 (1 hour) is good but adjust to your taste (NOTE: These are overriden with the value of 1 when DEBUG mode is invoked)
; DEFAULT:     3600


[security]
; =======================================================================================
; SECURITY SETTINGS
; Debugging, what''s allowed, etc.

; NOTE: In order to show a page in debug mode the debug parameter must be passed and set to 1 (true) in either a POST or GET
; For example when accessing via link (get) yourdomain.com/somepage/?debug=true


allow-debug = 1
; FORMAT:      1|0
; DESCRIPTION: Will we allow debugging which captures logs and outputs data to user at end of script run
; NOTE:        This does not turn debugging on, a debug param POST['debug']=1 or GET['debug']=1 must be sent with the page request
; RECOMMENDED: Set to 1 (true) when implementing/troubleshooting, 0 (false) when in production
; DEFAULT:     1 (just because of implementing. When not troubleshooting set to 0 (false) )


allow-debug-ip = "/^10\./"
; FORMAT:      Regular Expression (eg "/^10\./"
; DESCRIPTION: If we allow debugging what IPs are allowed to switch into debugging mode?
; NOTE:        allow-debug must still be set to 1 (true)
; RECOMMENDED: "/^10\./" (or whatever ip range your local workstation, or a range of servers are in) I recommend taking it down all the way to your actual IP or at least subnet
; DEFAULT:     ""


allow-debug-host = ""
; FORMAT:      Regular Expression (eg "/^https:\/\/yourtestserver\.yourdomain\.com$/i")
; DESCRIPTION: If we allow debugging what servers can we debug on? So if you have a separate test and production server debug will only be able to be switched on when in test environment
; NOTE:        allow-debug must still be set to 1 (true). This is typically just your domain with regex escape "\" in front of each / and .
; RECOMMENDED: If you have access to a test server, use it and put the domain here and use HTTPS only in the regex! "^https:\/\/" (requires https: at start) and NOT "^https?\/\/:" (http or https)
; DEFAULT:     ""


require-ssl = 1
; FORMAT:      1|0
; DESCRIPTION: Should HTTPS be forced?
; DEFAULT:     1


obfuscate-secrets = 1
; FORMAT:      1|0
; DESCRIPTION: If in debug mode, should we sanitize data before display, obfuscating secret keys, api keys, etc (e.g. *********dj7ixJ). Just in case you copy/paste/print/share while debugging
; NOTE:        This does not turn debugging on
; RECOMMENDED: Always set to 1 (true). Up to last 6 chars are shown so you can id key (half if key length is fewer than 6). Can''t think of a reason why you wouldn''t want this
; DEFAULT:     1


api_ipaccess = ""
; FORMAT:      regex
; DESCRIPTION: If this application will be accessed by other servers (not client-side scripts such as JavaScript running in the browser)
;			   You may restrict access by requiring the requesting server be from a designated ip address or range of ip addresses
; NOTE:        Code to check the api_key and IP address are not built into the framework, you will have to perform your own validation.
;              to authenticate access via client side scripts or applications. Hard coded tokens are a bad, bad, bad idea.
; DEFAULT:     ""


ip-restrict-allow-ip = ""
; FORMAT:      Regular Expression e.g. "/^10\.9\.104\./" (for IP 10.9.104.x)
; DESCRIPTION: Should access be restricted by IP? If this is set then access will only be granted to clients from a specific IP or IP range.
; NOTE:        By default this is checked in the custom/inc.php file which executes on all script executions. You may move it to a subset of
;              pages by removing, or commenting out from custom/inc.php and placing a call to restrictByIp() at the top of any other page you wish.
;              You can further restrict API access by setting api-restrict-access-ip below
; NOTE:        This will restrict access to the ENTIRE application (pages and apis). If you want to grant fine grained access, use the api-restrict-access-allow-ip for
;              just the IP or zone-restrict-allow-ip[] settings.
; DEFAULT:     ""


ip-restrict-allow-admin = 0
; FORMAT:      1|0
; DESCRIPTION: Even if ip-restrict is set, can a logged in admin override it? If you always want the IP to be locked, then this is 0. Useful
;              if you only want admins to access when they are on a set IP range, or if you beleive admin credentials could be comprimised.
;              Setting it to 1 is useful if you want to restrict access to basic functions but have another means of authenticating as an admin.
; DEFAULT:     0


ip-restrict-allow-user = 0
; FORMAT:      1|0
; DESCRIPTION: Even if ip-restrict is set, can a logged in user override it? If you always want the IP to be locked, then this is 0. Useful
;              if you beleive admin credentials could be comprimised. Setting it to 1 is useful if you want to restrict access to basic
;              functions but have another means of authenticating as a user. You will have to develop your own code to determine user roles and
;              if a user has access to functionality.
; DEFAULT:     0


api-restrict-allow-ip = ""
; FORMAT:      Regular Expression e.g. "/^10\.9\.104\./" (for IP 10.9.104.x)
; DESCRIPTION: If this application will be accessed by other servers (not client-side scripts such as JavaScript running in the browser)
;			   You may restrict access by requiring the requesting server be from a designated ip address or range of ip addresses
; NOTE:        This will restrict access to ALL apis. If you want to grant fine grained access for multiple APIs, use the zone-restrict-allow-ip[] settings.
; DEFAULT:     "" (open/no restriction)


zone-restrict-allow-ip[tools] = ""
; FORMAT:      Regular Expression e.g. "/^10\.9\.104\./" (for IP 10.9.104.x)
; DESCRIPTION: You may restrict access to specific zones by requiring the requesting client or server be from a designated ip address or range of ip addresses.
;              Add multiple zones, and add restrictByIpForZone("zone0"), for example, to the top of your php script.
; NOTE:        Rename these within the [] to anything descriptive. If you don''t need these in your application you can remove these lines from the config
;              and not present them to the admin installing your application.
; DEFAULT:     "" (open/no restriction)


[paths]
; =======================================================================================
; FILE PATHS AND STORAGE

assets = ""
; FORMAT:      string
; DESCRIPTION: This is the path returned when getPathAssets() is called
;              "" will return "/assets/" (https://yourdomain.com/assets/)
;			   "https://cdn.yourdomain.com/yourapp/" will return "https://cdn.yourdomain.com/yourapp/"
; NOTE:        This directory path must be publicly accessible and have the proper CORS settings
; DEFAULT:     "" to use default assets/ directory within the application folder
; USAGE:       $f = getPathAssets() . "js/main.js"; will set $f to "/assets/js/main.js" or
;              "https://cdn.yourdomain.com/youapp/js/main.js"


[secrets]
; =======================================================================================
; ROOT USER AND SERVER TO SERVER SECRETS
; For single user applications or server to server communications
; Anything under [secrets] and [secrets-custom] (in the app section below) will be obfuscated when [secrets][obfuscate-secrets] is set to 1
; This will also help when a future release of the project framework incorporates the option to use a secure key store (so that they do not need to be managed in this flat file)


password-hash = ""
; FORMAT:      string
; DESCRIPTION: Place the password-hash blob (not the password) generated by password_hash() function here. This can be a root password (for single user systems).
; NOTE:        Be sure to keep the password generated by the tool in a safe place, it will be needed when accessing the tools
;              This will be generated for you the first time you go to use the tools and will continue to generate until this value is set in this config file
;              If you forget the password just reset the string above to ""
; ALSO NOTE:   If you are security conscience you will be happy to know that the salt is contained in the blob, so there is salt but no pepper.
;              https://www.owasp.org/index.php/Password_Storage_Cheat_Sheet
; DEFAULT:     "" (to force password setup)


google-authenticator = ""
; FORMAT:      string
; DESCRIPTION: For a single user system enter the Google authenticator key for the root user.
;              Why use Google Authenticator on a root password? To prevent brute force login and to add an additional factor
;              of authentication, we provide Google Authenticator https://en.wikipedia.org/wiki/Google_Authenticator
; NOTE:        You must download and install the Google Authenticator app on a mobile device in order to use.
; RECOMMENDED: While not required, it is recommended that as soon as system is up and running you set up Google Authenticator
; DEFAULT:     ""


key-store[] = ""
key-store[] = ""
key-store[] = ""
key-store[] = ""
; FORMAT:      string
; DESCRIPTION: A keychain of randomly generated keys you can use for various security functions such as signing hashes.
; DEFAULT:     ""


api-restrict-allow-key[gmpyj] = ""
; FORMAT:      string
; DESCRIPTION: For server to server communication you can require servers requesting API data from your app to provide an API key.
;			   You can set up multiple api keys and it is recommended that each application you grant access to has
;			   it's own key. The key identifer is used as the key in the array as well as placed at the start of the
;			   api key: api-restrict-access-allow-key[ucedl] = "ucedl-TGEilOCJQQ6XKU6rZqCia4mi9"
;              These should be used for server to server communication and should never be used where code is placed on a
;              client computer (javascript in the browser or mobile app). Server to server only.
;			   You may restrict access of outside parties using your apis by requiring the requesting server to provide an API key
;              as if it were a password.
;              API keys should only be used for server to server communication. For greater security add the ip address or range of ip addresses
;              of expected requesting servers to api-restrict-access-ip
; NOTE:        This is a single, shared key and should not be used for multiple, untrusted, systems. If you are developing a 3rd party
;              platform you will need to code your own key management.
; SECURITY:    Why is this in plain text? It is an access code providing access to a common GET api. It is NOT an authentication method.
;			   If you plan on providing separate data segments for each api user then a different method should be used.
;              Notes of what each key is used for may be placed in the [api-key-assignment] section.
; USAGE:       api-restrict-access-allow-key[keyid] = "keyid-somekey" where keyid is an alpha id and somekey is the alpha-numeric key
;              The requestor would add ?apikey=keyid-somekey to their GET request
; EXAMPLE:     api-restrict-access-allow-key[ucedl] = "ucedl-TGEilOCJQQ6XKU6rZqCia4mi9"
; DEFAULT:     ""


[api-key-assignment]
; =======================================================================================
gmpyj = "[Library Dev Use]],[[For development purposes only 2018-02-24 clk]]"


[app-secrets]
; =======================================================================================
; APP SECRETS
; You can store server to server secrets in the [app-secrets] section of the config array ( getCfg("app-secrets") )
; so they are obfuscated when sanitized_print_r() is called
; Do not rename this heading as it is used by sanitized_print_r(). However, if it is removed or renamed the
; code is resiliant enough to continue working but won''t find, and therefore won''t sanitize, these particular secrets
; NOTE: Secrets generated during execution of the application should be stored in $app['secrets'] ( getApp('secrets') so they
; may be afforded the same level of obfuscation.


example-key = "ffhtvldvjherijijledbf"
; FORMAT:      string
; DESCRIPTION: An example secret key stored in the config [app-secrets] section. Access by calling getCfg("app-secrets")[example-key]
; DEFAULT:     "ffhtvldvjherijijledbf" (to show an example)

; for information on values for the keyes shown here please see the appropriate sections further down

lti[oauth_secret] = ""

lti[api_token] = ""

libapps[apiKey] = ""

alma[apiKey] = ""


[lti]
; =======================================================================================
; APP CONFIG SETTINGS FOR LMS
; This is what is used in the app/config.php XML file that is used to configure the app when adding it to the LMS


match_value = "Canvas.course.sisSourceId"
; FORMAT:      string
; DESCRIPTION: You will either use context_label (default) or another field (such as Canvas.course.sisSourceId) in the LMS that holds the unique identifier we use
;              to search LibGuides and Alma.
;              Most LTIs assume context_label contain the course id (eg 2018FABIOL201-01) but if not, then use the following web page to determine the correct LMS
;              variable your system uses: https://canvas.instructure.com/doc/api/file.tools_variable_substitutions.html
; DEFAULT:     "" to use context_label which is automatically passed by LMS


name = "Library Help LTI"
description = "St. Thomas Libraries LTI"
; FORMAT:      string
; DESCRIPTION: The name and description that will show up in the lti config xml file (Not seen by users)


oauth_clientid = ""
; FORMAT:      string
; DESCRIPTION: Place the clientid generated by /lti/tools/setup.php
; NOTE:        The Client ID and Secret will also be used to configure the lti app in the lms
;              Place the oauth_secret in lti[oauth_secret] above in the [app-secrets] section


api_domain = "https://stthomas.instructure.com"
; FORMAT:      string
; DESCRIPTION: These are optional and will require communication with your LMS admin.
;              If the token is supplied it will grab the instructor''s last name from the course
;              (if there is more than one the first one alphabetically) so that it may be matched with
;              Guide and Readinglist tags (LIBX201-LAMBERT)
;              If it is not supplied ("") courses with instructor last names will not be searched automatically.
;              The API domain is only required if you wish to pull from the API on a different beta, test, or prod instance.
;              For example, if you are running a test version of the api on yourschool.test.instructure.com, the default
;              API domain is yourschool.test.instructure.com, but if you have an api_token for prod then you would
;              want to override the default and use yourschool.instructure.com
; NOTE:        Place the api_token in lti[api_token] above in the [app-secrets] section
; DEFAULT:     ""


property[tool_id] = "library_help"
; FORMAT:      string
; DESCRIPTION: Give the LTI tool a unique ID (we suggest your school domain with _ instead of . (eg: someuniv_library_help) )
; DEFAULT:     "school_library_help"


property[text] = "Library Help"
; FORMAT:      string
; DESCRIPTION: The label of the button that shows up in course navigation


appnavoption[enabled] = 0
; FORMAT:      1|0
; DESCRIPTION: Should the button be shown in the Course Navigation in every course by default? (or should it be hidden until activated in a course?)
; DEFAULT:     0


cnavoption[enabled] = 1
; FORMAT:      1|0
; DESCRIPTION: When app is enabled, should the Course Navigation button be active or inactive (greyed out) by default?
; NOTE:        If appnavoption[enabled] is 0 (false), this should be set to 1 (true) so it is one less step to activate it
; DEFAULT:     1


useLMScss = 1
; FORMAT:      1|0
; DESCRIPTION: Should we use the LMS style sheet? If 0 (false) you should supply your own. Even if using the LMS style sheet you can override in the custom.css file


[libapps]
; =======================================================================================
; SPRINGSHARE LIBAPPS API SETTINGS
; Settings to access the LibApps API
; Subscription Access to the Springshare/LibApps/LibGuides CMS is is required for API


siteID = ""
; your libguides site id

libguides = ""
; your libguides domain

apiDomain = ""
; apiKey (place apiKey in libapps[apiKey] under the [app-secret] section above

subjexclude = "21274,21275"
; comma delimited subject ids

defaultSearchGroup = "0"
; comma delimited group ids
; set as "" to include all search groups. The default may be overriden by LMS variables using custom/data/override-libguides-search.json
; this is a reverse of exclude. Include only the base groups you want to search, such as a General College if you want to include guides in the Night College unless the LMS says it is a night class


[alma]
; =======================================================================================
; ALMA API SETTINGS
; Settings to access the Alma API
; You will need to set up a developer account as well as an authentication method for the webapp
; Set up account at: https://developers.exlibrisgroup.com
; Manage your app keys: https://developers.exlibrisgroup.com/dashboard/application


apiDomain = ""
; FORMAT:      url
; DESCRIPTION: The domain of the endpoint used for accessing the Alma API

link = ""
; FORMAT:      string
; DESCRIPTION: "open_url", "leganto_permalink", or write your own custom permalink using {{citation_id}} as a placeholder to insert the citation id
; 			   open_url and leganto_permalink are system provided links. "custom" will allow you to make your own link
; 			   custom format: "https://clic-stthomas.alma.exlibrisgroup.com/leganto/public/01CLIC_STTHOMAS/citation/{{citation_id}}?auth=SAML";

link_target = "_self"
; FORMAT:      string
; DESCRIPTION: Where should the course material resource links open? Standard a href target="" applies:
;			   _blank, _parent, _self, _top


[aws]
; =======================================================================================
; AWS INTEGRATION
; Settings for using AWS for saving settings
; This is reserved for future development


enabled = 0;



[rulesets]
; =======================================================================================
; LIBAPP and ALMA API SEARCH RULESETS
; Rulesets coded in PHP IF blocks
; It is recommended you do not modify anything outside of the custom/ directory, however
; it is most likely you will want to tweak the way the application searches the LibApp API
; for guides and the Alma API for readinglists. It is recommended you have some experience
; in coding PHP before you do so. Also, mistakes happen so we devised a way to easily
; version your rulesets so you can roll back hours, days, or weeks later if you find an error.
; Store the custom rules set files in the custom/ruleset/ directory using the naming convention:
; ruleset-reading-[version].php and ruleset-libapps-[version].php where [version] is the version
; identifier


libapps = "20171129"
reading = "20180814"
; FORMAT:      string
; DESCRIPTION: The version identifier of the custom ruleset file found in custom/ruleset/ directory.
; NOTE:        ruleset-libapp-[version].php where [version] is what is entered above
; EXAMPLE:     libapps = "20171202" would use the ruleset custom/ruleset/ruleset-libapps-20171202.php
;              reading = "20171120" would use the ruleset custom/ruleset/ruleset-reading-20171120.php
; NOTE:        It is highly recomended to keep old versions of rulesets to roll back to even weeks later
; DEFAULT:     ""


[univ]
; =======================================================================================
; Tell the LRI how to decode Course Identifiers fed to it so it can extract data such as
; Year, Term, Department, Course Number, and Section


test = "201920LIBX201-X9
201840LIBX201-W01
201840LIBX201
201840LIBX201X
201840LIBX202X-02
201840LIBX
2018LIBX
2018LIBX-W9
LIBX
LIBX201-W32
LIBX201
LIBX-THOMAS
LIBX201-THOMAS
LIBX201-THOM
LIBX201-LI
library_sandbox_345
201820ACCT316-D01
201820ACCT555-711
201830CIED551-M1
201830EDLD869-AM1
201830MGMT753-SA
201820ACCT601-203
201820ACST200-L01
201820AIST199A-01
201820AMSL111C-02
201820BIOL102-60L
201820BIOL102-D0L"
; FORMAT:      String with new lines separating values
; DESCRIPTION: New line delimited test cases for different ways we can expect the course identifier to be fed in
; NOTE:        You can add new test cases as they come up and test using the admin tool "Test Course ID"
; TIP:         Copy the test cases out of the admin tool and paste into an online RegEx tester (be sure to turn on multi-line flag!)


regex[course] = "^([0-9]{4})?([0-9]{2})?([A-Z]{4})([0-9]{3}[A-Z]?)?((?:-)[0-9A-Z]{2,})?$" ; full regex for validation
regex[year] = "^([0-9]{4})" ; regex to extract YEAR only
regex[term] = "(?<=^[0-9]{4})[0-9]{2}" ; regex to extract TERM only
regex[dept] = "((?<=^[0-9]{6})|(?<=^[0-9]{4})|(?<=^))[A-Z]{4}" ; regex to extract DEPARTMENT only
regex[crsnum] = "(?<=[A-Z]{4})[0-9]{3}[A-Z]?" ; regex to extract COURSE NUM only (no department)
regex[section] = "(?<=-)[0-9A-Z]{2,}$" ; regex to extract SECTION only
; FORMAT:      string - regex
; DESCRIPTION: RegEx specific to your university to extract course information.
; NOTE:        It is highly recommended you use an online regex tester (such as one mentioned
;              earlier in this file) to test each permutation expected. Also use the
;              course code tester in the /tools directory to test.
; EXAMPLE:     If DEPARTMENT follows year and term (201820BIOL) but may not always have
;              Year and Term included (BIOL201) it would be: "(?<=^[0-9]{4})[0-9]{2}"
; DEFAULT:     YYYYTTDEPTNUM-SECTION format - but change to meet your own


termdesc[] = "10,J-Term";
termdesc[] = "20,Spring";
termdesc[] = "30,Summer";
termdesc[] = "40,Fall";
; FORMAT:      string, comma delimited TT,DESCRIPTION
; DESCRIPTION: Term codes from SIS translated to terms listed in Alma for Term under a Course.


[analytics]
; =======================================================================================

google = ""


[noguides]
; =======================================================================================
; What link displays if no matches for guides or subject pages are found in LibGuides


link[] = "[[URL:http://yourlibguides.edu]],[[TEXT:Research Help]]"
; we can have multiple links
; EXAMPLE:     link[] = "[[URL:http://example.com]],[[TEXT:Example Link]]"


[messaging]
; =======================================================================================
; Messaging only appears for instructors in the form of help and feedback options


feedback[display] = 1
feedback[heading] = "Feedback &amp; Support"
feedback[html]    = "[[FILE:cust-feedback.html]]"
; FORMAT:      display: 1|0, heading: plaintext, html: html string or file name
; DESCRIPTION: Title is plaintext, html can be html code or a reference to a file in the custom/ directory (ex: [[FILE:cust-feedback.html]])
; NOTE:        The feedback section is only viewable to instructors in the LTI. It appears below the Customization area. It is not viewable to students or on the manual page outside of the LTI
; DEFAULT:     ""


[sections]
; =======================================================================================


intro[allowOverride] = 0
intro[desc] = "Display a link to the library home page."
intro[display] = 1
intro[heading] = ""
intro[html] = "[[FILE:cust-introduction.html]]"


discovery[allowOverride] = 1
discovery[desc] = "Display a search box to allow students quick access to the library catalog."
discovery[display] = 1
discovery[heading] = "CLICsearch"
discovery[html] = "[[FILE:cust-discovery.html]]"


guides[allowOverride] = 1
guides[desc] = "Display LibGuides that are tagged with a matching Course ID. For example <tt>PHIL201</tt> or <tt>PHIL201-LAMBERT</tt>. Work with your subject librarian if you would like to create/update a guide for your course."
guides[display] = 1
guides[heading] = "Research and Course Guides"
guides[prehtml] = ""
guides[posthtml] = ""


coursematerial[allowOverride] = 1
coursematerial[desc] = "If there is a matching Resource List display it. Resource List items may be links to articles, books, and databases in the library collection as well as downloadable documents. Contact the library to set up Resource Lists for your class."
coursematerial[display] = 1
coursematerial[heading] = "Resource List"
coursematerial[prehtml] = ""
coursematerial[posthtml] = ""


databases[allowOverride] = 1
databases[desc] = "Display library databases related to this course. Databases are selected based upon subject matter as tagged in the library's A-Z Database List."
databases[display] = 1
databases[heading] = "Related Databases"
databases[prehtml] = ""
databases[posthtml] = ""
databases[dbexpandAllowOverride] = 1
databases[dbexpand] = 0


librarian[allowOverride] = 0
librarian[desc] = "Display profile and contact info of the librarian assigned to your academic department."
librarian[display] = 1
librarian[heading] = "Need Help?"
librarian[prehtml] = "<p>Contact your librarian!</p>"
librarian[posthtml] = ""


chat[allowOverride] = 0
chat[desc] = "Students can chat with a librarian when they are in need of assistance."
chat[display] = 1
chat[heading] = ""
chat[html] = "[[FILE:cust-chat.html]]"


; =======================================================================================
; =======================================================================================
; CACHE-PROXY
; =======================================================================================

[cacheproxy]

endpoint = ""
key= ""

; =======================================================================================
; =======================================================================================
; Keep all custom app settings above these two lines

;*/

;?>