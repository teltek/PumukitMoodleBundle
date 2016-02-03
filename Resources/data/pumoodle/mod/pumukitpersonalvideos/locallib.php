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
 * Internal library of functions for module pumukit
 *
 * All the pumukit specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage pumukit
 * @copyright  2012 Andres Perez
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// These should be customized at Site Administration block -> Plugins -> Activity modules -> Recorded lecture
define ('PUMUKITURL', 'http://cmarautopub/pumoodle/');
define ('SECRET', 'This is a PuMoodle secret!¡!');

/**
 * Creates a daily ticket to authenticate the serials+videos and embed requests
 *
 * @param integer $id - person or video id to be authenticated.
 * @return string $ticket
 */
function pumukit_create_ticket($id, $email) {
    global $CFG;

    $pumukitsecret = empty($CFG->pumukit_secret) ? SECRET : $CFG->pumukit_secret;
    $date   = date("Y-m-d");
    // At the moment, the IP is not checked on PuMuKit's side
    $ip     = $_SERVER["REMOTE_ADDR"];  
    $ticket = md5($pumukitsecret . $date . $id . $email);
    return $ticket;
}

/**
 * Gets curl output for the pumukit host and the given url.
 * if $absoluteurl = true, it takes $action as final url and doesn't parse $parameters.
 *
 * @param string $action from pumukit module.
 * @param array $parameters (key => value)
 * @return string $output
 */
function pumukit_curl_action_parameters($action, $parameters = null, $absoluteurl = false){
    global $CFG;
    if ($absoluteurl) {
        $url = $action;
    } elseif (empty($CFG->pumukit_pumukiturl)){
        $url = PUMUKITURL . $action . '?' . http_build_query($parameters, '', '&');
    } else{       
        $url = trim($CFG->pumukit_pumukiturl);
        // Add the final slash if needed
        $url .= (substr($url, -1) == '/') ? '' : '/';
        $url .=  $action . '?' . http_build_query($parameters, '', '&');
    }

// Debug - comment the next line.
// echo 'Debug - sending petition:<br/>['. $url . ']<br/>';

    $ch   = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // needed for html5 player capability detection
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $sal["var"]    = curl_exec($ch); 
    $sal["error"]  = curl_error($ch);
    $sal["status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($sal["status"] !== 200 && !isset($sal["var"])){
        var_dump($sal);
        die ("\nError - review http status\n"); // to do excepcion
    }
    
    return $sal["var"];
}