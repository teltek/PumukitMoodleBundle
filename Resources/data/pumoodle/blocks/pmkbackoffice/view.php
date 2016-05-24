<?php

require_once('../../config.php');
//require_once('pmkbackoffice_form.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$pmk2_url = required_param('url', PARAM_RAW);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_pmkbackoffice', $courseid);
}

require_login($course);
$PAGE->set_url('/blocks/pmkbackoffice/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pagetitle', 'block_pmkbackoffice'));

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
<iframe src="<?php echo $pmk2_url ?>"
        id='pmk_iframe'
        width="100%"
        height="2200px"
        onload="requestIframeHeight(this)"
        scrolling="no"
        border="0"
        frameborder="0">
</iframe>
<?php
echo $OUTPUT->footer();
?>
