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
 * Library of interface functions and constants for module pmkpersonalvideos
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the pmkpersonalvideos specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage pmkpersonalvideos
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function pmkpersonalvideos_supports($feature) {
    switch($feature) {
        // To make this module a resource instead of an activity
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        default:                              return null;
    }
}

/**
 * Saves a new instance of the pmkpersonalvideos into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $pmkpersonalvideos An object from the form in mod_form.php
 * @param mod_pmkpersonalvideos_mod_form $mform
 * @return int The id of the newly inserted pmkpersonalvideos record
 */
function pmkpersonalvideos_add_instance(stdClass $pmkpersonalvideos, mod_pmkpersonalvideos_mod_form $mform = null) {
    global $DB;

    $pmkpersonalvideos->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('pmkpersonalvideos', $pmkpersonalvideos);
}

/**
 * Updates an instance of the pmkpersonalvideos in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $pmkpersonalvideos An object from the form in mod_form.php
 * @param mod_pmkpersonalvideos_mod_form $mform
 * @return boolean Success/Fail
 */
function pmkpersonalvideos_update_instance(stdClass $pmkpersonalvideos, mod_pmkpersonalvideos_mod_form $mform = null) {
    global $DB;

    $pmkpersonalvideos->timemodified = time();
    $pmkpersonalvideos->id = $pmkpersonalvideos->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('pmkpersonalvideos', $pmkpersonalvideos);
}

/**
 * Removes an instance of the pmkpersonalvideos from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function pmkpersonalvideos_delete_instance($id) {
    global $DB;

    if (! $pmkpersonalvideos = $DB->get_record('pmkpersonalvideos', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('pmkpersonalvideos', array('id' => $pmkpersonalvideos->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function pmkpersonalvideos_user_outline($course, $user, $mod, $pmkpersonalvideos) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $pmkpersonalvideos the module instance record
 * @return void, is supposed to echp directly
 */
function pmkpersonalvideos_user_complete($course, $user, $mod, $pmkpersonalvideos) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in pmkpersonalvideos activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function pmkpersonalvideos_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link pmkpersonalvideos_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function pmkpersonalvideos_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see pmkpersonalvideos_get_recent_mod_activity()}
 *
 * @return void
 */
function pmkpersonalvideos_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function pmkpersonalvideos_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function pmkpersonalvideos_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of pmkpersonalvideos?
 *
 * This function returns if a scale is being used by one pmkpersonalvideos
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $pmkpersonalvideosid ID of an instance of this module
 * @return bool true if the scale is used by the given pmkpersonalvideos instance
 */
function pmkpersonalvideos_scale_used($pmkpersonalvideosid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of pmkpersonalvideos.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any pmkpersonalvideos instance
 */
function pmkpersonalvideos_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Creates or updates grade item for the give pmkpersonalvideos instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $pmkpersonalvideos instance object with extra cmidnumber and modname property
 * @return void
 */
function pmkpersonalvideos_grade_item_update(stdClass $pmkpersonalvideos) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($pmkpersonalvideos->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = 10;
    $item['grademin']  = 0;

    grade_update('mod/pmkpersonalvideos', $pmkpersonalvideos->course, 'mod', 'pmkpersonalvideos', $pmkpersonalvideos->id, 0, null, $item);
}

/**
 * Update pmkpersonalvideos grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $pmkpersonalvideos instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function pmkpersonalvideos_update_grades(stdClass $pmkpersonalvideos, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/pmkpersonalvideos', $pmkpersonalvideos->course, 'mod', 'pmkpersonalvideos', $pmkpersonalvideos->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function pmkpersonalvideos_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for pmkpersonalvideos file areas
 *
 * @package mod_pmkpersonalvideos
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function pmkpersonalvideos_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the pmkpersonalvideos file areas
 *
 * @package mod_pmkpersonalvideos
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the pmkpersonalvideos's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function pmkpersonalvideos_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding pmkpersonalvideos nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the pmkpersonalvideos module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function pmkpersonalvideos_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the pmkpersonalvideos settings
 *
 * This function is called when the context for the page is a pmkpersonalvideos module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $pmkpersonalvideosnode {@link navigation_node}
 */
function pmkpersonalvideos_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $pmkpersonalvideosnode=null) {
}
