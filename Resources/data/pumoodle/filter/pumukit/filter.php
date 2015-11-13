<?php

/**
 *  Pumukit link filtering
 *
 * This filter will replace any link generated with pumukit repository
 * with an iframe that will retrieve the content served by pumukit.
 *
 * It uses ideas from the mediaplugin filter and the helloworld filter template.
 *
 * @package    filter
 * @subpackage pumukit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define ('SECRET', 'This is a PuMoodle secret!¡!');

require_once($CFG->libdir.'/filelib.php');

class filter_pumukit extends moodle_text_filter {
   
 
    public function filter($text, array $options = array()) {
        global $CFG;

        // discard empty texts and texts without links
        if (!is_string($text) or empty($text)) {

            return $text;
        }
        if (stripos($text, '<a') === false) {

            return $text;
        }



        // Look for '/pumoodle/embed', replace the entire <a... </a> tag and send the url as $link[1]
        $search =  '/<a\\s[^>]*href=\"(https?:\\/\\/.*?\\/pumoodle\\/embed?.*?)\">.*<\\/a>/is';
        $newtext = $text; // we need to return the original value if regex fails!

        $newtext = preg_replace_callback($search, 'filter_pumukit_matterhorn_callback', $newtext);

        if (empty($newtext) or $newtext === $text) {
            // error or not filtered
            unset($newtext);
            return $text;
        }

        return $newtext;
    }
}

function filter_pumukit_matterhorn_callback($link) {

    $opencast = false;
    preg_match('/opencast=(\w)\&|opencast=(\w)$/i', $link[1], $result);
    $opencast = isset($result) ? ($result[1] == '1' ? true:false) : false;

    preg_match('/id=(\w*)\&|id=(\w*)$/i', $link[1], $result);
    $mm_id = isset($result)?  $result[1] : null;

    preg_match_all('/email=(.*)\\&|email=(.*)$/i', $link[1], $resultado, PREG_SET_ORDER);
    $email =  isset($resultado)?  $resultado[0][1] : null;

    $parameters = array(
                        'professor_email' => $email,
                        'ticket' => create_ticket($mm_id, $email)
                        );
    $url = preg_replace('/email=(.*)\\&|email=(.*)$/i', http_build_query($parameters, '', '&'), $link[1]);

    $iframe_opencast = '<iframe src="' . $url . '" style="border:0px #FFFFFF none; width:100%; height:850px;" scrolling="no" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" ></iframe>';

    $iframe_normal = '<iframe src="' . $url . '" style="border:0px #FFFFFF none; width:640px; height:480px;" scrolling="no" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" ></iframe>';
    
    $iframe=($opencast)? $iframe_opencast : $iframe_normal;

    return $iframe;
}


function create_ticket($id, $email) {
    global $CFG;

    $pumukitsecret = empty($CFG->pumukit_secret) ? SECRET : $CFG->pumukit_secret;
    $date   = date("Y-m-d");
    // At the moment, the IP is not checked on PuMuKit's side
    $ip     = $_SERVER["REMOTE_ADDR"];  
    $ticket = md5($pumukitsecret . $date . $id . $email);

    return $ticket;
}

