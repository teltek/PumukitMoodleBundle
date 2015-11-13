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
 * Prints a particular instance of pumukit
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage pumukit
 * @copyright  2012 Andres Perez aperez@teltek.es
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php'); 

global $USER;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // pumukit instance ID - it should be named as the first character of the module

if ($id) {
	$cm      = get_coursemodule_from_id('pumukit', $id, 0, false, MUST_EXIST);
	$course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$pumukit = $DB->get_record('pumukit', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
	$pumukit = $DB->get_record('pumukit', array('id' => $n), '*', MUST_EXIST);
	$course  = $DB->get_record('course', array('id' => $pumukit->course), '*', MUST_EXIST);
	$cm      = get_coursemodule_from_instance('pumukit', $pumukit->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'pumukit', 'view', "view.php?id={$cm->id}", $pumukit->name, $cm->id);

/// Print the page header
$PAGE->set_url('/mod/pumukit/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pumukit->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('pumukit-'.$somevar);

// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading($pumukit->name);

if ($pumukit->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('pumukit', $pumukit, $cm->id), 'generalbox mod_introbox', 'pumukitintro');
}

// Creates a ticket to view the file to ensure that the video is displayed.
// as Pumukit requires it for the non-public broadcast profiles.
$url_temp_array = explode('/', $pumukit->embed_url); // Strict standards: Only variables should be passed by reference


preg_match('/id\=(\w*)\&|id\=(\w*)$/i', $pumukit->embed_url, $result);
$mm_id = isset($result)?  $result[1] : null;
preg_match('/lang\=(\w*)\&|id\=(\w*)$/i', $pumukit->embed_url, $result);
$lang = isset($result)?  $result[1] : null;
$concatChar = ($mm_id || $lang) ? '&': '?';
$parameters = array(
              'professor_email' => $pumukit->professor_email,
              'ticket' => pumukit_create_ticket($mm_id, $pumukit->professor_email)
              );
$url = $pumukit->embed_url . $concatChar . http_build_query($parameters, '', '&');
// TO DO - test an iframe instead of direct curl echo.
echo pumukit_curl_action_parameters($url , null, true);

// Finish the page
echo $OUTPUT->footer();