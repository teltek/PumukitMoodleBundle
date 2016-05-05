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
 * The main pmkpersonalvideos configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage pmkpersonalvideos
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__).'/locallib.php');

/**
 * Module instance settings form
 */
class mod_pmkpersonalvideos_mod_form extends moodleform_mod
{
    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER;       // to obtain email
        global $SESSION;    // to obtain page language
        global $CFG;        // To obtain Moodle Version.
        // If the teacher does not change the course language, session->lang is not set.
        if (isset($SESSION->lang)) {
            $lang = $SESSION->lang;
        } else if (isset($USER->lang)) {
            $lang = $USER->lang;
        } else {
            $lang = 'en';
        }

        $this->standard_coursemodule_elements();
        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('pmkpersonalvideosname_', 'pmkpersonalvideos'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'pmkpersonalvideosname', 'pmkpersonalvideos');

        // Adding a js function to update the name element when a video is selected
        $mform->addElement('static', null, '',
                           '<script type="text/javascript">
            //<![CDATA[
            function updateName(id,valor) {
                var nombre = document.getElementById(id);
                nombre.value = valor;
            }
            //]]>
            </script>');

        // Adding the standard "intro" and "introformat" fields (the intro description).
        if($CFG->version < 2015033000.00){ //Moodle version < 2.9dev (Build: 20150309)
            $this->add_intro_editor();
        }else{
	    $this->standard_intro_elements();
        }

        //-------------------------------------------------------------------------------
        // Adding the rest of pmkpersonalvideos settings, spreading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        // $mform->addElement('static', 'label1', 'pmkpersonalvideossetting1', 'Your pmkpersonalvideos fields go here. Replace me!');
        // $mform->addElement('header', 'pmkpersonalvideosfieldset', get_string('pmkpersonalvideosfieldset', 'pmkpersonalvideos'));
        // $mform->addElement('static', 'label2', 'pmukitsetting2', 'Your pmkpersonalvideos fields go here. Replace me!');

        // TO DO: implement user authentication between moodle and pmkpersonalvideos (LDAP?)

        // maybe some data_preprocessing before definition() would be more elegant...
        $pmkpersonalvideos_out = json_decode (pmkpersonalvideos_curl_action_parameters('index',
                                                                                       array('professor_email' => $USER->email,
                                                                                             'ticket'          => pmkpersonalvideos_create_ticket('', $USER->email),
                                                                                             'lang'            => $lang )), true);

        // TO DO: improve error processing, now it is only displayed in the select form.
        if (!$pmkpersonalvideos_out) {
            $pmkpersonalvideos_list = array(get_string('error_no_pmkpersonalvideos_output', 'pmkpersonalvideos') => null);
        } else if ($pmkpersonalvideos_out['status'] == "ERROR"){
            $pmkpersonalvideos_list = array($pmkpersonalvideos_out['status_txt'] => null);
        } else {
            $pmkpersonalvideos_list = $pmkpersonalvideos_out['out'];
        }

        $mform->addElement( $this->create_serial_select($pmkpersonalvideos_list));
        $mform->addRule( 'embed_url', get_string('form_rule_select_a_lecture','pmkpersonalvideos'), 'required' );
        $mform->addElement('hidden', 'professor_email', $USER->email);
	$mform->setType('professor_email', PARAM_TEXT);
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    /**
     * Creates the select field of the HTML_QuickForm with
     * disabled select options for the serial titles and
     * standard select options for the lectures.
     */
    function create_serial_select($pmkpersonalvideos_list)
    {
        // http://stackoverflow.com/a/2150275
        $maxrows = 25;
        $mform = $this->_form;
        $select = $mform->createElement( 'select');
        $select->setName('embed_url');

        $rows = 0;
        foreach ($pmkpersonalvideos_list as $serial_title => $mms){
            $rows++;
            $select->addOption( $serial_title, '', array( 'disabled' => 'disabled' ) );
            if (!$mms) continue;
            foreach ( $mms as $name => $id ) {
                $rows++;
                $display_name = '  · '.$name;

                // Debug - for simplicity's sake, video date precedes the real title
                // returned by pmkpersonalvideos. Each option updates the resource name
                // with the real title.
                $reg_date='/[0-9]{4}-[0-9]{2}-[0-9]{2} /';
                $title = (preg_match($reg_date, $name))? substr($name,11):$name;
                $select->addOption($display_name, $id, array ('onclick'=>'updateName(\'id_name\',\''.$title.'\')') );
            }
        }

        $label = get_string('select_a_video','pmkpersonalvideos');
        $select->setLabel($label);
        if (1 == $rows) $rows = 2; // A select with 1 row is unreadable
        $select->setSize(min($rows,$maxrows));

        return $select;
    }
}
