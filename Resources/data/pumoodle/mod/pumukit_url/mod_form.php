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
 * The main pumukit configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage pumukit
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__).'/locallib.php');

/**
 * Module instance settings form
 */
class mod_pumukit_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER;       // to obtain email
        global $SESSION;    // to obtain page language
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
        $mform->addElement('text', 'name', get_string('pumukitname_', 'pumukit'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'pumukitname', 'pumukit');

/*        // Adding a js function to update the name element when a video is selected
        $mform->addElement('static', null, '', 
            '<script type="text/javascript">
            //<![CDATA[
            function updateName(id,valor) {
                var nombre = document.getElementById(id);
                nombre.value = valor;
            }
            //]]>
            </script>');
*/
        // Adding the standard "intro" and "introformat" fields (the intro description).
        $this->add_intro_editor();

        $mform->addElement('text', 'embed_url', get_string('pumukitidorurl', 'pumukit'), array('size'=>'64'));
        $mform->addHelpButton('embed_url', 'pumukitidorurl', 'pumukit');

        $mform->addRule( 'embed_url', get_string('form_rule_insert_idorurl','pumukit'), 'required' );
        $mform->addElement('hidden', 'professor_email', $USER->email);

        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    /**
     * Creates the select field of the HTML_QuickForm with 
     * disabled select options for the serial titles and 
     * standard select options for the lectures.
     */
    function create_serial_select($pumukit_list)
    {
        // http://stackoverflow.com/a/2150275
/*        $maxrows = 25;
        $mform = $this->_form;    
        $select = $mform->createElement( 'text');     
        $select->setName('embed_url');

/*        $rows = 0;
          foreach ($pumukit_list as $serial_title => $mms){
            $rows++;
            $select->addOption( $serial_title, '', array( 'disabled' => 'disabled' ) );
            if (!$mms) continue;
            foreach ( $mms as $name => $id ) {
                $rows++;
                $display_name = '  · '.$name;

                // Debug - for simplicity's sake, video date precedes the real title
                // returned by pumukit. Each option updates the resource name 
                // with the real title.
                $reg_date='/[0-9]{4}-[0-9]{2}-[0-9]{2} /';
                $title = (preg_match($reg_date, $name))? substr($name,11):$name;
                $select->addOption($display_name, $id, array ('onclick'=>'updateName(\'id_name\',\''.$title.'\')') );
            }
        }*/

        $label = get_string('select_a_video','pumukit');
        $select->setLabel($label);
/*        if (1 == $rows) $rows = 2; // A select with 1 row is unreadable
        $select->setSize(min($rows,$maxrows));*/

        return $select;
    }
}
