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
 * pumukit settings.
 *
 * @package   atto_pumukit
 * @copyright COPYRIGHTINFO
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_pumukit', new lang_string('pluginname', 'atto_pumukit')));

$settings = new admin_settingpage('atto_pumukit_settings', new lang_string('settings', 'atto_pumukit'));
if ($ADMIN->fulltree) {
    // An option setting
    $settings->add(new admin_setting_configtext('atto_pumukit/pumukiturl',
        get_string('pumukiturl', 'atto_pumukit'), get_string('pumukiturldesc', 'atto_pumukit'), 'https://snf-683722.vm.okeanos.grnet.gr/pumoodle/searchmultimediaobjects', PARAM_URL));

    $settings->add(new admin_setting_configtext('atto_pumukit/dialogtitle',
        get_string('dialogtitle', 'atto_pumukit'), get_string('dialogtitledesc', 'atto_pumukit'), get_string('dialogtitledefval', 'atto_pumukit'), PARAM_TEXT));
}
