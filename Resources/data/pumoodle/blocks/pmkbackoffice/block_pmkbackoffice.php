<?php
class block_pmkbackoffice extends block_base {
    public function init() {
        $this->title = get_string('pmkbackoffice', 'block_pmkbackoffice');
    }
    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $url = new moodle_url('/blocks/pmkbackoffice/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        $this->content         =  new stdClass;
        $this->content->footer = html_writer::link($url, sprintf('--> %s <--',get_string('linktopage', 'block_pmkbackoffice')));
        return $this->content;
    }
    //Prevents the block from appearing anywhere.
    public function applicable_formats() {
        return array();
    }
}
