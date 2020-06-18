# library-help-lti

Beta - early but stable development of a Library LTI that can be dropped into Canvas

With minimal configuration, once installed on a server running PHP 7.1+, this application provides a Learning Tool Integration that can provide library resources such as LibGuides, librarian profiles, and Alma/Leganto reading lists specific to a given course. In addition to integrating with an LMS such as Canvas, the application also provides APIs that can be utilized by other campus resources such as intranets, portals, and mobile apps. Though built specifically as an LTI, an LMS is not required as a separate public page is available and the APIs will still work.

## Requirements

1. LibGuides CMS
2. Server running PHP 7.1+
3. A willingness to learn minimal PHP/JSON

## Optional

1. A Learning Management System (LMS) such as Canvas. While it is specific to Canvas, some LTI config changes may be necessary to make it work with Blackboard, Moodle, or another LMS.
2. Alma/Leganto reading lists for reading list functionality

## Customizations

This project makes use of a core application maintained by the University of St. Thomas Libraries development team that is not meant to be customized by the installer. However, the application is HIGHLY customizable by making use of a customization directory and custom.ini.php file. While the outer application may receive updates, you should keep back-ups of
and version your custom folder. (During updates some manual changes to files in custom may be required.)

While great care has been taken to minimize development changes in the customization folder, this project is still in beta form. From time to time when installing updates a list of manual changes to the files in custom will be provided under the updates section of this document.

## Installation

1. Upload the private directory to a non-public space on your server. Upload the contents of `public` to your web root document directory (sometimes named public, web, www, web_root, etc) to your server running PHP7.0 or greater and running https. You'll preferably want to do this in a development environment before moving a copy to production.
2. Go to https://yourdomain.com/lti/tools and follow the instructions.
3. You will be asked to update variables in the private/app/custom/config.ini.php file.
4. Update the config file in several passes, don't do it all at once. Just get it up and running, make changes, upload, test, make changes, upload, test, etc.
5. The /tools site acts as a wizard to walk you through configuration and customizations. The majority of documentation (which is continually being improved on!) is found there.

## Advanced Installation

If you only have a web root directory (and you don't have access to directories below the web root to store non-public scripts and documents) then you can move your private directory into the `lti` directory. While this isn't recommended, it is possible, and there is an .htaccess document in the private directory that prevents public viewing. Make sure to check
that it is working.

If you move the private directory then you need to update the `public/lti/inc/inc.php` file to point to the new location.

Also, if you have other apps running on your server space that use (PHP-Project-Framework)[https://github.com/chadkluck/php-project-framework] then you can use the same `private/lib/php-project-framework` (Check the version) and add the `oauth_validator` and `GoogleAuthenticator` to the `lib` directory.

If you rename `private/app` be sure to update `public/lti/inc/inc.php`

## Updates

### 2019-08-07

#### New Feature: Emulator

There is now an LMS emulator included in the Tools section. This allows the admin to test LTI functionality and configurations outside of an LMS. It also assists in initial configuration if the admin does not have access to an LMS at the beginning.
Saved scenerios may be added to the /custom/data/emulator-test.json

#### New Feature: Overrides

Overrides use additional fields from requesting systems (Canvas or systems using the api) to perform more narrow searches within LibGuides. For example, a LMS may use sub accounts to distinguish between different campuses and colleges that may not be evident just by using the course code. Using comma lists, strings, and regex rules may be created to override librarian, guide, group, and add a tag to the search.
Override rules may be added to the /custom/data/override-libguides-search.json

#### Enhanced: Librarian Profiles

The librarian profile pic and name now show up in card format. If only one librarian is shown an expanded profile is shown below the card. Now more than one librarian can show up even if there is a guide match. Before multiple librarians would be displayed in a compact form if there were no guides and the application rolled up to the subject level and displayed one or more subject experts. If guides were found then only the librarian attached to the first guide would be shown. Now if mutiple guides are found each owner is displayed.

#### Fixes: No Subject Fix

If a department or guide or course has no assigned subjects there could be unexpected results. This is now fixed.

#### Custom Update

If updating a previous install, there are a few items you will need to do manually for the 2019-08-07 update.

1. Copy over the two new JSON files from `private/app/custom/data` into your `custom/data` directory: `emulator-test.json` and `override-libguides-search.json`
2. In `public/lti/assets/custom/css/custom.css` bring over the styles for "Librarian Profiles" - they are currently set to create a card and a circle profile picture. Place this in your `custom/css/` directory.
3. A new rule template has been added to `private/app/inc/ruleset-libapps-default.php` which allows for the use of "tag" overrides. If you wish to use this rule and you have custom rulesets in `private/app/custom/rulesets/` then copy the rule over.
4. There is an added field in `private/app/custom/config.ini.php` around line 400 under the `[libapps]` section called `defaultSearchGroup`. This will need to be copied into your own `custom/config.ini.php` file under the `[libapps]` section.

### 2020-06-18

Updated to the latest version of PHP-Project-Framework (June 2020). This reorganizes the file structure to make it more modular and secure. Make a copy of your custom folders as a back-up. Install the new files in their new locations. Then update the custom files. Sorry, but this LTI is still beta. However, it is becoming more and more stable each quarter and changes like these should hopefully occur less and less. The latest update takes into account that we want to minimize changes like this in the future.

Just make a backup of all your files, do a fresh install on a dev server, and slowly add in your customizations.