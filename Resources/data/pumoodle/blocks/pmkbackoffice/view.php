<?php

require_once('../../config.php');
//require_once('pmkbackoffice_form.php');

global $DB, $OUTPUT, $PAGE;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_pmkbackoffice', $courseid);
}

require_login($course);
$PAGE->set_url('/blocks/pmkbackoffice/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pagetitle', 'block_pmkbackoffice'));

echo $OUTPUT->header();
?>
<iframe src="https://pumukit2.armazeg.org/app_dev.php/admin"
        width="100%"
        height="100%"
        border="0"
        frameborder="0">

</iframe>
<?php
echo $OUTPUT->footer();
?>
