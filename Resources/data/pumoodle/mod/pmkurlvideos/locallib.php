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
 * Internal library of functions for module pmkurlvideos
 *
 * All the pmkurlvideos specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage pmkurlvideos
 * @copyright  2012 Andres Perez
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// These should be customized at Site Administration block -> Plugins -> Activity modules -> Recorded lecture
define ('PMKURLVIDEOS', 'http://cmarautopub/pumoodle/');
define ('SECRET', 'This is a PuMoodle secret!¡!');

/**
 * Creates a daily ticket to authenticate the serials+videos and embed requests
 *
 * @param integer $id - person or video id to be authenticated.
 * @return string $ticket
 */
function pumukit_create_ticket($id, $email)
{
    global $CFG;

    $pumukitsecret = empty($CFG->pmkurlvideos_secret) ? SECRET : $CFG->pmkurlvideos_secret;
    $date   = date("Y-m-d");
    // At the moment, the IP is not checked on PuMuKit's side
    $ip     = $_SERVER["REMOTE_ADDR"];
    $ticket = md5($pumukitsecret . $date . $id . $email);
    return $ticket;
}

/**
 * Gets curl output for the pmkurlvideos host and the given url.
 * if $absoluteurl = true, it takes $action as final url and doesn't parse $parameters.
 *
 * @param string $action from pmkurlvideos module.
 * @param array $parameters (key => value)
 * @return string $output
 */
function pumukit_curl_action_parameters($action, $parameters = null, $absoluteurl = false)
{
    global $CFG;
    if ($absoluteurl) {
        $url = $action;
    } elseif (empty($CFG->pmkurlvideos_pmkurlvideos)){
        $url = PMKURLVIDEOS . $action . '?' . http_build_query($parameters, '', '&');
    } else{
        $url = trim($CFG->pmkurlvideos_pmkurlvideos);
        // Add the final slash if needed
        $url .= (substr($url, -1) == '/') ? '' : '/';
        $url .=  $action . '?' . http_build_query($parameters, '', '&');
    }

    // Debug - comment the next line.
    //  echo 'Debug - sending petition:<br/>['. $url . ']<br/>';
    $ch   = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // needed for html5 player capability detection
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $sal["var"]    = curl_exec($ch);
    $sal["error"]  = curl_error($ch);
    $sal["status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $sal;
}

/**
 *  Parses the embed_url field to return a proper embedded_url.
 */
function pumukit_parse_embed_url($url)
{
    global $CFG;

    // If the teacher does not change the course language, session->lang is not set.
    if (isset($SESSION->lang)) {
        $lang = $SESSION->lang;
    } else if (isset($USER->lang)) {
        $lang = $USER->lang;
    } else {
        $lang = 'en';
    }

    $embed_url= $CFG->pmkurlvideos_pmkurlvideos;

    //Parses the 'embed_url' field in case it is an url that contains an id.
    if (preg_match('~^http.*://~', $url)) {
        preg_match('/id\=(\w*)/i', $url, $result);
        $pmk_id = isset($result[1])?  $result[1] : null;
        if($pmk_id == null) {
            preg_match('~video/(\w*)$~i', $url, $result);
            $pmk_id = isset($result[1])?  $result[1] : null;
        }
        if($pmk_id != null) {
            $url= $embed_url .'embed?id=' . $pmk_id . "&lang=" . $lang ;
        }
        //If an id can't be found, we will just copy the url 'as is'.
    }
    else {
        //If preg match fails, we assume the embed_url contains an id
        $url = $embed_url.'embed?id=' . $url;
    }
    return $url;
}

/**
 *  Parses the embed_url field to return the id.
 */
function pumukit_parse_id($url)
{
    global $CFG;
    //Parses the 'embed_url' field in case it is an url that contains an id.
    if (preg_match('~^http.*://~', $url)) {
        preg_match('/id\=(\w*)/i', $url, $result);
        $pmk_id = isset($result[1])?  $result[1] : null;
        if($pmk_id == null) {
            preg_match('~video/(\w*)$~i', $url, $result);
            $pmk_id = isset($result[1])?  $result[1] : null;
        }
        if($pmk_id != null) {
            $url= $pmk_id;
        }
    }

    //If an id can't be found, we will just copy the url 'as is'.
    return $url;
}

function pumukit_get_iframe($embed_url, $prof_email)
{
    $link_params = array();
    parse_str(html_entity_decode(parse_url($embed_url, PHP_URL_QUERY)), $link_params);
    $mm_id = isset($link_params['id']) ? $link_params['id'] : null;
    $lang = isset($link_params['lang']) ? $link_params['lang'] : null;
    $opencast = isset($link_params['opencast']) ? ($link_params['opencast'] == '1') : false;
    $concatChar = ($mm_id || $lang) ? '&': '?';
    $parameters = array(
        'professor_email' => $prof_email,
        'ticket' => pumukit_create_ticket($mm_id, $prof_email)
    );
    $url = $embed_url . $concatChar . http_build_query($parameters, '', '&');
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
    return $iframe_html;
}

function pumukit_get_metadata($embed_url, $prof_email)
{
    $embed_id = pumukit_parse_id($embed_url);
    $ticket = pumukit_create_ticket($embed_id, $prof_email);

    if (isset($SESSION->lang)) {
        $lang = $SESSION->lang;
    } else if (isset($USER->lang)) {
        $lang = $USER->lang;
    } else {
        $lang = 'en';
    }

    $parameters = array('id' => $embed_id,
                        'ticket' => $ticket,
                        'professor_email' => $prof_email,
                        'lang' => $lang);

    $sal = pumukit_curl_action_parameters('metadata' , $parameters , false);
    $metadata = json_decode($sal['var'], true);

    $title = $metadata['out']['title'];
    $description = $metadata['out']['description'];
    $url = $metadata['out']['embed'];
    return array($title, $description, $url);
}
