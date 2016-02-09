<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of pmkpersonalvideos
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage pmkpersonalvideos
 * @copyright  2012 Andres Perez aperez@teltek.es
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php'); 

global $USER;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // pmkpersonalvideos instance ID - it should be named as the first character of the module

if ($id) {
	$cm      = get_coursemodule_from_id('pmkpersonalvideos', $id, 0, false, MUST_EXIST);
	$course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$pmkpersonalvideos = $DB->get_record('pmkpersonalvideos', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
	$pmkpersonalvideos = $DB->get_record('pmkpersonalvideos', array('id' => $n), '*', MUST_EXIST);
	$course  = $DB->get_record('course', array('id' => $pmkpersonalvideos->course), '*', MUST_EXIST);
	$cm      = get_coursemodule_from_instance('pmkpersonalvideos', $pmkpersonalvideos->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'pmkpersonalvideos', 'view', "view.php?id={$cm->id}", $pmkpersonalvideos->name, $cm->id);

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);


/// Print the page header
$PAGE->set_url('/mod/pmkpersonalvideos/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pmkpersonalvideos->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('pmkpersonalvideos-'.$somevar);

// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading($pmkpersonalvideos->name);

if ($pmkpersonalvideos->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('pmkpersonalvideos', $pmkpersonalvideos, $cm->id), 'generalbox mod_introbox', 'pmkpersonalvideosintro');
}

// Creates a ticket to view the file to ensure that the video is displayed.
// as Pmkpersonalvideos requires it for the non-public broadcast profiles.
$url_temp_array = explode('/', $pmkpersonalvideos->embed_url); // Strict standards: Only variables should be passed by reference


preg_match('/id\=(\w*)\&|id\=(\w*)$/i', $pmkpersonalvideos->embed_url, $result);
$mm_id = isset($result)?  $result[1] : null;
preg_match('/lang\=(\w*)\&|id\=(\w*)$/i', $pmkpersonalvideos->embed_url, $result);
$lang = isset($result)?  $result[1] : null;
$concatChar = ($mm_id || $lang) ? '&': '?';
$parameters = array(
              'professor_email' => $pmkpersonalvideos->professor_email,
              'ticket' => pmkpersonalvideos_create_ticket($mm_id, $pmkpersonalvideos->professor_email)
              );
$url = $pmkpersonalvideos->embed_url . $concatChar . http_build_query($parameters, '', '&');
// TO DO - test an iframe instead of direct curl echo.
echo pmkpersonalvideos_curl_action_parameters($url , null, true);

// Finish the page
echo $OUTPUT->footer();
