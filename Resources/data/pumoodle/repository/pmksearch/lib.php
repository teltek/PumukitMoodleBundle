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
 * repository_pmksearch class
 * This is a subclass of repository class
 * http://docs.moodle.org/dev/Repository_plugins
 *
 * @package    repository_pmksearch
 * @category   repository
 * @copyright  Andres Perez <aperez@teltek.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// These should be customized for each repository instance at
// Site administration ► Plugins ► Repositories ► Manage repositories
define ('PMKSEARCHREPOSITORYURL', 'http://cmarautopub/pumoodle/');
define ('PMKSEARCHREPOSITORYSECRET', 'This is a PuMoodle secret!¡!');

class repository_pmksearch extends repository {

    /**
     * Constructor
     *
     * @param int $repositoryid
     * @param stdClass $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SITEID, $options = array('ajax' => false))
    {
        parent::__construct($repositoryid, $context, $options);
    }

    /**
     * Get file listing
     *
     * @param string $path
     * @param string $page
     */
    public function get_listing($path = '', $page = '')
    {

        // TO DO: implement user authentication between moodle and pmksearch
        global $COURSE;
        $list = array();
        $list['list'] = $this->retrieve_pmksearchs_and_create_list();
        // the management interface url (using the pumukit block).
        $manager_block = new moodle_url('/blocks/pmkbackoffice/view.php', array('course_id' => $COURSE->id, 'instance_id' => $this->instance->id));
        $list['manage'] = $manager_block->out(false); //Prints the url.
        // dynamically loading. False as the entire list is created in one query.
        $list['dynload'] = false;
        // the current path of this list.
        $list['path'] = array(
            array('name'=>'Course list', 'path'=>'')
                // array('name'=>'sub_dir', 'path'=>'/sub_dir')
        );
        // set to true, the login link will be removed
        $list['nologin'] = true;
        // set to true, the search button will be removed
        $list['nosearch'] = false;
        $list['norefresh'] = true;

        return $list;
    }

    /**
     * Check if user logged in
     */
    public function check_login()
    {
        global $SESSION;
        // if (!empty($SESSION->logged)) {
        // return true;
        // } else {
        // return false;
        // }
        return true;
    }

    /**
     * if check_login returns false,
     * this function will be called to print a login form.
     */
    public function print_login()
    {
        $user_field->label = get_string('username').': ';
        $user_field->id    = 'demo_username';
        $user_field->type  = 'text';
        $user_field->name  = 'demousername';
        $user_field->value = '';

        $passwd_field->label = get_string('password').': ';
        $passwd_field->id    = 'demo_password';
        $passwd_field->type  = 'password';
        $passwd_field->name  = 'demopassword';

        $form = array();
        $form['login'] = array($user_field, $passwd_field);
        return $form;
    }

    /**
     * Search in external repository
     *
     * @param string $text
     */
    public function search($text, $page = 0)
    {
        $search_result = array();
        // search result listing's format is the same as
        // file listing
        $search_result['list'] = $this->retrieve_pmksearchs_and_create_list($text);
        return $search_result;
    }
    /**
     * move file to local moodle
     * the default implementation will download the file by $url using curl,
     * that file will be saved as $file_name.
     *
     * @param string $url
     * @param string $filename
     */
    /*
       public function get_file($url, $file_name = '')
       {
       }
     */

    /**
     * when logout button on file picker is clicked, this function will be
     * called.
     */
    public function logout()
    {
        global $SESSION;
        unset($SESSION->logged);
        return true;
    }

    /**
     * this function must be static
     *
     * @return array
     */
    public static function get_instance_option_names()
    {
        return array('pmksearchrepositoryurl', 'pmksearchrepositorysecret', 'pmksearch_managerurl');
    }

    /**
     * Instance config form
     */
    public static function instance_config_form($mform)
    {
        $mform->addElement('text', 'pmksearchrepositoryurl',
                           get_string('pmksearchurl', 'repository_pmksearch'),
                           array('value' => '','size' => '40'));
	$mform->setType('pmksearchrepositoryurl', PARAM_TEXT);
        $mform->addElement('static', 'pmksearchurldefault', '', get_string('pmksearchurldefault', 'repository_pmksearch') . PMKSEARCHREPOSITORYURL);

        $mform->addElement('text', 'pmksearchrepositorysecret',
                           get_string('pmksearchsecret', 'repository_pmksearch'),
                           array('value' => '','size' => '40'));
	$mform->setType('pmksearchrepositorysecret', PARAM_TEXT);
        $mform->addElement('static', 'pmksearchsecretdefault', '', get_string('pmksearchsecretdefault', 'repository_pmksearch') . PMKSEARCHREPOSITORYSECRET);

        $mform->addElement('text', 'pmksearch_managerurl',
                           get_string('pmksearch_managerurl', 'repository_pmksearch'),
                           array('value' => '','size' => '40'));
	$mform->setType('pmksearch_managerurl', PARAM_TEXT);

        return true;
    }

    /**
     * Type option names - A common setting for all the moodle site (the same for all instances)
     * Not used.
     * @return array
     */
    // public static function get_type_option_names() {
    //     return array('api_key');
    // }

    /**
     * Type config form - A common setting for all the moodle site (the same for all instances)
     * Not used.
     */
    // public static function type_config_form($mform, $classname = 'repository_pmksearch') {
    //     $mform->addElement('text', 'api_key', get_string('api_key', 'repository_pmksearch'), array('value'=>'','size' => '40'));
    // }

    /**
     * will be called when installing a new plugin in admin panel
     *
     * @return bool
     */
    public static function plugin_init()
    {
        $result = true;
        // do nothing
        return $result;
    }

    /**
     * Only supports external file linking
     * see http://docs.moodle.org/dev/Repository_plugins#supported_returntypes.28.29
     * @return int
     */
    public function supported_returntypes()
    {

        return FILE_EXTERNAL;
    }
    // Only use this repository with moodle media, not images
    public function supported_filetypes()
    {
        // return array('web_video');
        return array('web_file','web_video');
        // return '*';
    }

    /**
     * Creates a daily ticket to authenticate the serials+videos and embed requests
     *
     * @param integer $id - person or video id to be authenticated.
     * @return string $ticket
     */
    private function pmksearch_create_ticket($id)
    {

        $instancesecret = $this->options['pmksearchrepositorysecret'];
        $secret = empty($instancesecret) ? PMKSEARCHREPOSITORYSECRET : $instancesecret;

        $date   = date("Y-m-d");
        // At the moment, the IP is not checked on Pmksearch's side
        $ip     = $_SERVER["REMOTE_ADDR"];
        $ticket = md5($secret . $date . $id);

        return $ticket;
    }

    /**
     * Gets curl output for the pmksearch host and the given url.
     *
     * @param string $action from pmksearch module.
     * @param array $parameters (key => value)
     * @return string $output
     */
    private function pmksearch_curl_action_parameters($action, array $parameters = null,
                                                      $absoluteurl = false)
    {
        $pmksearchrepositoryurl = $this->options['pmksearchrepositoryurl'];
        if ($absoluteurl) {
            $url = $action;
        } elseif (empty($pmksearchrepositoryurl)){
            $url = PMKSEARCHREPOSITORYURL . $action . '?' . http_build_query($parameters, '', '&');
        } else{
            $url = trim($pmksearchrepositoryurl);
            // Add the final slash if needed
            $url .= (substr($url, -1) == '/') ? '' : '/';
            $url .=  $action . '?' . http_build_query($parameters, '', '&');
        }
        // Debug - uncomment the next line to view the query sent to pmksearch.
        // echo 'Debug - sending petition:<br/>['. $url . ']<br/>';
        $ch   = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // needed for html5 player capability detection
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $sal["var"]    = curl_exec($ch);
        $sal["error"]  = curl_error($ch);
        $sal["status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$sal["url"] = $url;
        curl_close($ch);
        if ($sal["status"] !== 200  && !isset($sal['var'])){
            var_dump($sal);
            die ("\nError - review http status\n"); // to do excepcion

        }

        return $sal["var"];
    }

    /**
     * Queries a pmksearch server and processes the result in the moodle list format
     * The authentication is done with a ticket with current user's email.
     *
     */
    private function retrieve_pmksearchs_and_create_list($text = '') {
        global $USER;       // To get email for authentication
        global $SESSION;    // To get page language
        // If the teacher does not change the course language, session->lang is not set.
        if (isset($SESSION->lang)) {
            $lang = $SESSION->lang;
        } else if (isset($USER->lang)) {
            $lang = $USER->lang;
        } else {
            $lang = 'en';
        }

        // There is more real state avaliable in the new file picker (moodle 2.3 onwards)
        // and thumbnails are resized.
        $width  = 140;
        $height = 105;

        // TO DO: implement some kind of ldap authentication with user (teacher) instead of email check.

        $pmksearch_out = json_decode ($this->pmksearch_curl_action_parameters('search_repository',
                                                                              array('professor_email' => $USER->email,
                                                                                    'ticket'    => $this->pmksearch_create_ticket($USER->email),
                                                                                    'lang' => $lang ,
                                                                                    'search' => $text)), true);
        if (!$pmksearch_out) {
            // get_string('error_no_pmksearch_output', 'pmksearch'); has a descriptive error status
            return array(array('title' => 'Unknown error.'));

        } else if ($pmksearch_out['status'] == "ERROR"){
            // $pmksearch_out['status_txt'] has a descriptive error status
            return array(array('title' => $pmksearch_out['status_txt']));

        } else {
            $pmksearch_list = $pmksearch_out['out'];
        }

        $list = array();
        foreach ($pmksearch_list as $serial){
            // create the "children files" with the multimedia objects
            $children = array();
            $children[] = array(
                'title' => $serial['playlist']['title'] . '.mp4',
                'shorttitle' => $serial['playlist']['title'],
                'thumbnail' => $serial['playlist']['thumbnail'],
                'thumbnail_width' => $width,
                'thumbnail_height' => $height,
                'source' => $serial['playlist']['embed'] . '&email=' . $USER->email . '#' . $serial['playlist']['title'],
            );
            foreach ($serial['mms'] as $mm) {
                $shorttitle = $mm['date'] . " " . $mm['title'];
                // hack to accept this file by extension - see /repository/youtube/lib.php line 127
                // There is a check in /repository/repository_ajax.php line 167  that
                // throws an "invalidfiletype" exception if title has no video extension.
                $children[] = array( 'title' => $shorttitle . ".avi",
                                     'shorttitle' => $shorttitle,
                                     'thumbnail' => $mm['pic'],
                                     'thumbnail_width' => $width,
                                     'thumbnail_height' => $height,
                                     'source' => $mm['embed'] . '&email=' . $USER->email . '#' . $mm['title']);
            }

            // create a "folder" with the serial title
            $list[]= array( 'title' => $serial['title'],
                            'thumbnail' => $serial['pic'],
                            'thumbnail_width' => $width,
                            'thumbnail_height' => $height,
                            'children' => $children );
        }


        return $list;
    }
}
