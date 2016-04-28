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
        $search =  '/<a\\s[^>]*href=\"(https?:\\/\\/.*?\\/pumoodle\\/embed?.*?)\">.*?<\\/a>/is';
        $newtext = preg_replace_callback($search, 'filter_pumukit_callback', $text);

        if (empty($newtext) or $newtext === $text) {
            // error or not filtered
            unset($newtext);
            return $text;
        }

        return $newtext;
    }
}

function filter_pumukit_callback($link) {
    global $CFG;
    //Get arguments from url.
    $link_params = array();
    parse_str(html_entity_decode(parse_url($link[1], PHP_URL_QUERY)), $link_params);
    //Initialized needed arguments.
    $opencast = isset($link_params['opencast']) ? ($link_params['opencast'] == '1') : false;
    $mm_id = isset($link_params['id']) ? $link_params['id'] : null;
    $email =  isset($link_params['email']) ? $link_params['email'] : null;
    //Prepare new parameters.
    $extra_arguments = array(
        'professor_email' => $email,
        'ticket' => create_ticket($mm_id, $email)
    );
    $new_url_arguments = "?".http_build_query(array_merge($extra_arguments, $link_params), '', '&');
    //Create new url with ticket and correct email.
    $url = preg_replace("/(\?.*)/i", $new_url_arguments, $link[1]);
    //Prepare and return iframe with correct sizes to embed on webpage.
    if($opencast) {
        $iframe_width = $CFG->iframe_multivideo_width?:'100%';
        $iframe_height = $CFG->iframe_multivideo_height?:'400px';
    }
    else {
        $iframe_width = $CFG->iframe_singlevideo_width?:'600px';
        $iframe_height = $CFG->iframe_singlevideo_height?:'400px' ;
    }
    $iframe_html = '<iframe src="' . $url . '"' .
                   '        style="border:0px #FFFFFF none; width:' . $iframe_width . '; height:' . $iframe_height . ';"' .
                   '        scrolling="no" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" >'.
                   '</iframe>';
    return $iframe_html;
}


function create_ticket($id, $email) {
    global $CFG;

    $pumukitsecret = empty($CFG->filter_pumukit_secret) ? SECRET : $CFG->filter_pumukit_secret;
    $date   = date("Y-m-d");
    // At the moment, the IP is not checked on PuMuKit's side
    $ip     = $_SERVER["REMOTE_ADDR"];
    $ticket = md5($pumukitsecret . $date . $id . $email);

    return $ticket;
}
