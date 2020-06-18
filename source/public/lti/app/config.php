<?php

/*

https://www.eduappcenter.com/tools/xml_builder#/new

https://community.canvaslms.com/groups/designers/blog/2017/07/21/how-to-create-a-simple-cavas-lti

https://weblinprod.stthomas.edu/libraries/lti/app/config.php

https://mitt.uib.no/doc/api/file.tools_variable_substitutions.html

*/
require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init

$appURL = getBaseURL();

header('Content-type: application/xml');

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

?>
<cartridge_basiclti_link
  xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0"
  xmlns:blti="http://www.imsglobal.org/xsd/imsbasiclti_v1p0"
  xmlns:lticm="http://www.imsglobal.org/xsd/imslticm_v1p0"
  xmlns:lticp="http://www.imsglobal.org/xsd/imslticp_v1p0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.imsglobal.org/xsd/imslticc_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticc_v1p0.xsd http://www.imsglobal.org/xsd/imsbasiclti_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imsbasiclti_v1p0p1.xsd http://www.imsglobal.org/xsd/imslticm_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticm_v1p0.xsd http://www.imsglobal.org/xsd/imslticp_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticp_v1p0.xsd">
  <blti:title><?php echo getCfg("lti")['name']; ?></blti:title>
  <blti:description>
    <!--[CDATA[<?php echo getCfg("lti")['description']; ?>]]-->
  </blti:description>
  <blti:icon><?php echo $appURL ?>/assets/custom/img/icon.png</blti:icon>
  <blti:launch_url><?php echo $appURL ?>/app/launch.php</blti:launch_url>
  <blti:custom>
  	<?php
	if( getCfg("lti")['match_value'] !== "" ) {
		?><lticm:property name="custom_lri_course_id">$<?php echo getCfg("lti")['match_value']; ?></lticm:property><?php
	} ?>
  	<lticm:property name="custom_lri_api_domain">$Canvas.api.domain</lticm:property>
  	<lticm:property name="custom_lri_css_common">$Canvas.css.common</lticm:property>
  	<lticm:property name="custom_lri_id">$Canvas.course.id</lticm:property>
    <lticm:property name="custom_lri_acct_name">$Canvas.account.name</lticm:property>
    <lticm:property name="custom_lri_acct_id">$Canvas.account.id</lticm:property>
  </blti:custom>
  <blti:extensions platform="canvas.instructure.com">
    <lticm:property name="default"><?php echo (getCfg("lti")['appnavoption']['enabled'] ? "enabled" : "disabled"); ?></lticm:property>
    <lticm:property name="tool_id"><?php echo getCfg("lti")['property']['tool_id']; ?></lticm:property>
    <lticm:property name="privacy_level">anonymous</lticm:property>
    <lticm:property name="text"><?php echo getCfg("lti")['property']['text']; ?></lticm:property>
    <lticm:options name="course_navigation">
      <lticm:property name="enabled"><?php echo (getCfg("lti")['cnavoption']['enabled'] ? "true" : "false"); ?></lticm:property>
      <lticm:property name="url"><?php echo $appURL ?>/app/launch.php</lticm:property>
		<lticm:property name="visibility">public</lticm:property>
    </lticm:options>
  </blti:extensions>
</cartridge_basiclti_link>