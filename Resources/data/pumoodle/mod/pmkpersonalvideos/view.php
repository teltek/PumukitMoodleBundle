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
$context = context_module::instance($cm->id);

//New 'all view' log event.
$event = \mod_pmkpersonalvideos\event\course_module_viewed::create(array(
    'objectid' => $cm->instance,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->trigger();
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

$link_params = array();
parse_str(html_entity_decode(parse_url($pmkpersonalvideos->embed_url, PHP_URL_QUERY)), $link_params);
$mm_id = isset($link_params['id']) ? $link_params['id'] : null;
$lang = isset($link_params['lang']) ? $link_params['lang'] : null;
$opencast = isset($link_params['opencast']) ? ($link_params['opencast'] == '1') : false;
$concatChar = ($mm_id || $lang) ? '&': '?';
$parameters = array(
    'professor_email' => $pmkpersonalvideos->professor_email,
    'ticket' => pmkpersonalvideos_create_ticket($mm_id, $pmkpersonalvideos->professor_email)
);
$url = $pmkpersonalvideos->embed_url . $concatChar . http_build_query($parameters, '', '&');

if($opencast) {
    $iframe_width = '100%';
    $iframe_height = '600px' ;
}
else {
    $iframe_width = '600px';
    $iframe_height = '400px' ;
}
$iframe_html = '<iframe src="' . $url . '"' .
               '        style="border:0px #FFFFFF none; width:' . $iframe_width . '; height:' . $iframe_height . ';"' .
               '        scrolling="no" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" >'.
               '</iframe>';
echo $iframe_html;
// Finish the page
echo $OUTPUT->footer();
