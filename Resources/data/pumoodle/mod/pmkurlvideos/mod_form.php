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
 * The main pmkurlvideos configuration form.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/course/moodleform_mod.php';
require_once dirname(__FILE__).'/locallib.php';

/**
 * Module instance settings form.
 */
class mod_pmkurlvideos_mod_form extends moodleform_mod
{
    /**
     * Defines forms elements.
     */
    public function definition()
    {
        global $USER;       // to obtain email
        global $SESSION;    // to obtain page language

        $this->standard_coursemodule_elements();
        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('pumukitname_', 'pmkurlvideos'), array('size' => '64'));

        //$mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'pumukitname', 'pmkurlvideos');

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
        $this->standard_intro_elements();

        $mform->addElement('hidden', 'professor_email', $USER->email);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_RAW);
        }
        $mform->setType('professor_email', PARAM_RAW);

        $pmkid_arr = array();
        $scriptattrs = 'style="position:relative; top:-49px; left:443px;"';

        $mform->addElement('text', 'embed_url', get_string('pumukitidorurl', 'pmkurlvideos'), array('size' => 64));
        $mform->addElement('submit', 'updatemetadata', get_string('updatemetadata_b', 'pmkurlvideos'), $scriptattrs);
        $mform->addHelpButton('embed_url', 'pumukitidorurl', 'pmkurlvideos');
        $mform->addRule('embed_url', get_string('form_rule_insert_idorurl', 'pmkurlvideos'), 'required');
        $mform->setType('embed_url', PARAM_RAW);

        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    public function definition_after_data()
    {
        global $CFG, $COURSE;
        $mform = &$this->_form;
        parent::definition_after_data();

        $embed_url = &$mform->getElement('embed_url');
        $embed_url->setValue(pumukit_parse_id($embed_url->getValue()));
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        $mform = &$this->_form;
        list($title_text, $description_text, $url) = pumukit_get_metadata($data['embed_url'], $data['professor_email']);
        if (!$url) {
            $errors['embed_url'] = ' Error. The URL/ID given is not valid.';

            return $errors;
        }
        if (isset($data['updatemetadata'])) {
            $title = &$mform->getElement('name');
            $description = &$mform->getElement('introeditor');
            $title->setValue($title_text);
            $desc_value = $description->getValue();
            $desc_value['text'] = $description_text;
            $description->setValue($desc_value);
            $errors[''] = '';
            //Unset name error, since we added it ourselves now.
            if (isset($errors['name'])) {
                unset($errors['name']);
            }
        }

        return $errors;
    }
}
