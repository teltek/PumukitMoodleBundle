<?php

require_once('../../config.php');
//require_once('pmkbackoffice_form.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$course_id = required_param('course_id', PARAM_INT);
$instance_id = required_param('instance_id', PARAM_RAW);

if (!$course = $DB->get_record('course', array('id' => $course_id))) {
    print_error('invalidcourse', 'block_pmkbackoffice', $course_id);
}

require_login($course);
$PAGE->set_url('/blocks/pmkbackoffice/view.php', array('id' => $course_id));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pagetitle', 'block_pmkbackoffice'));
$PAGE->set_title('Moodle: PuMuKIT Media Manager');
$pmk2_url = $DB->get_record('repository_instance_config' , array('instanceid' => $instance_id, 'name' => 'pmksearch_managerurl'));
if(!$pmk2_url)
    send_header_404();
else {
    $pmk2_url = $pmk2_url->value;
    $db_secret = $DB->get_record('repository_instance_config' , array('instanceid' => $instance_id, 'name' => 'pmksearchrepositorysecret'));
    if($db_secret)
        $secret = $db_secret->value;
    else
        $secret = '';
    $username = $USER->username;
    $date = date('d/m/Y');
    $domain = parse_url($pmk2_url)['host'];
    $hash = md5($username.$secret.$date.$domain);
    $pmk2_url .= '?hash='.$hash.'&username='.rawurlencode($username);
}
echo $OUTPUT->header();

?>
<script>
 function requestIframeHeight(ifrm) {
     ifrm.contentWindow.postMessage("gimme_iframe_height", "*");
 }
 window.addEventListener("message", setNewHeight, false);
 function setNewHeight(event){
     ifrm = document.getElementById('pmk_iframe');
     if(event.data.height) {
         ifrm.style.visibility = 'hidden';
         ifrm.style.height = "10px"; // reset to minimal height ...
         // IE opt. for bing/msn needs a bit added or scrollbar appears
         ifrm.style.height = event.data.height + 4 + "px";
         ifrm.style.visibility = 'visible';
     }
 }
</script>
<?php if($pmk2_url): ?>
  <iframe src="<?php echo $pmk2_url ?>"
          id='pmk_iframe'
          width="100%"
          height="2200px"
          onload="requestIframeHeight(this)"
          scrolling="no"
          border="0"
          frameborder="0"
          allowfullscreen>
  </iframe>
<?php else: ?>
<h2>404 - Not Found</h2>
<p>
<?php echo get_string('pagenotfoundtext', 'block_pmkbackoffice') ?>
</p>
<?php endif?>
<?php
echo $OUTPUT->footer();
?>
