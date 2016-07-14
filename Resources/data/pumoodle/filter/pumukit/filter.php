<?php

/**
 *  Pumukit link filtering.
 *
 * This filter will replace any link generated with pumukit repository
 * with an iframe that will retrieve the content served by pumukit.
 *
 * It uses ideas from the mediaplugin filter and the helloworld filter template.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
defined('SECRET') || define('SECRET', 'This is a PuMoodle secret!¡!');

require_once $CFG->libdir.'/filelib.php';

class filter_pumukit extends moodle_text_filter
{
    public function filter($text, array $options = array())
    {
        global $CFG;

        // discard empty texts and texts without links
        if (!is_string($text) or empty($text)) {
            return $text;
        }
        if (stripos($text, '<a') === false) {
            return $text;
        }
        // Look for '/pumoodle/embed', replace the entire <a... </a> tag and send the url as $link[1]
        $search = '/<a\\s[^>]*href=\"(https?:\\/\\/[^>]*?\\/pumoodle\\/embed.*?)\">.*?<\\/a>/is';
        $newtext = preg_replace_callback($search, array($this, 'replaceUrlsWithIframes'), $text);

        if (empty($newtext) or $newtext === $text) {
            // error or not filtered
            unset($newtext);

            return $text;
        }

        return $newtext;
    }

    /**
     * Callback function used to return an iframe for each URL found with preg_replace_callback().
     */
    private function replaceUrlsWithIframes($regexResults)
    {
        global $CFG;
        $originalUrl = $regexResults[1];
        $iframeUrl = $this->generateIframeUrl($originalUrl);
        list($iframeWidth, $iframeHeight) = $this->calcIframeSize($originalUrl);

        $iframeHtml = '<iframe src="'.$iframeUrl.'"'.
                      '        style="border:0px #FFFFFF none; width:'.$iframeWidth.'; height:'.$iframeHeight.';"'.
                      '        scrolling="no" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" >'.
                      '</iframe>';
        return $iframeHtml;
    }

    /**
     * This function is used to generate an iframe given a valid url.
     *
     * @return string The iframe to add to the page.
     */
    private function generateIframeUrl($originalUrl)
    {
        //Saves the parameters originally in the url (if any)
        $originalParams = array();
        parse_str(html_entity_decode(parse_url($originalUrl, PHP_URL_QUERY)), $originalParams);
        //Sets the new parameters necessary for the iframe to work (email + ticket)
        $mmId = isset($originalParams['id']) ? $originalParams['id'] : null;
        $email = isset($originalParams['email']) ? $originalParams['email'] : null;
        $iframeParams = array(
            'professor_email' => $email,
            'ticket' => $this->createTicket($mmId, $email),
        );
        //Merges both parameters and creates an url
        $urlParams = '?'.http_build_query(array_merge($iframeParams, $originalParams), '', '&');

        //Replaces old url params with the merged ones.
        $iframeUrl = preg_replace("/(\?.*)/i", $urlParams, $originalUrl);

        return $iframeUrl;
    }

    /**
     * 'Calculates' the generated iframe size for a given url.
     *
     * @return array() An array with the format (width, height)
     */
    private function calcIframeSize($url)
    {
        $urlParams = array();
        parse_str(html_entity_decode(parse_url($url, PHP_URL_QUERY)), $urlParams);
        $isOpencast = isset($urlParams['opencast']) ? ($urlParams['opencast'] == '1') : false;

        global $CFG;
        if ($isOpencast) {
            $iframeWidth = $CFG->iframe_multivideo_width ?: '100%';
            $iframeHeight = $CFG->iframe_multivideo_height ?: '400px';
        } else {
            $iframeWidth = $CFG->iframe_singlevideo_width ?: '600px';
            $iframeHeight = $CFG->iframe_singlevideo_height ?: '400px';
        }

        return array($iframeWidth, $iframeHeight);
    }

    /**
     * Generates a valid PuMuKIT ticket to add to the iframe url.
     *
     * @param string $id    The id of the mmobj
     * @param string $email The email of the professor with an account in pmk (if any)
     *
     * @return string
     */
    private function createTicket($id, $email = null)
    {
        global $CFG;
        $pumukitsecret = empty($CFG->filter_pumukit_secret) ? SECRET : $CFG->filter_pumukit_secret;
        $date = date('Y-m-d');
        // At the moment, the IP is not checked on PuMuKit's side
        //$ip     = $_SERVER["REMOTE_ADDR"];
        $ticket = md5($pumukitsecret.$date.$id.$email);

        return $ticket;
    }
}
