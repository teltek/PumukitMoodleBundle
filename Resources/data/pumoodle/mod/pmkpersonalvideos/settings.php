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
 * Administration settings definitions for the quiz module.
 * Settings can be accessed from:
 * Site Administration block -> Plugins -> Activity modules -> Recorded lecture
 * This form stores general settings into the site wide $CFG object.
 *
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('pmkpersonalvideos_pmkpersonalvideosurl',
        get_string('pmkpersonalvideosurl', 'pmkpersonalvideos'),
        get_string('configpmkpersonalvideosurl', 'pmkpersonalvideos'), 'http://cmarautopub/pumoodle/'));

    $settings->add(new admin_setting_configtext('pmkpersonalvideos_secret',
        get_string('pmkpersonalvideossecret', 'pmkpersonalvideos'),
        get_string('configpmkpersonalvideossecret', 'pmkpersonalvideos'), 'This is a PuMoodle secret!¡!'));
}
