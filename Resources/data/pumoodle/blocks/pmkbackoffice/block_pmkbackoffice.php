<?php
class block_pmkbackoffice extends block_base {
    public function init() {
        $this->title = get_string('pmkbackoffice', 'block_pmkbackoffice');
    }
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text   = 'The content of our SimpleHTML block!';
        $this->content->footer = 'Footer here...';
        global $COURSE;

        // The other code.

        $url = new moodle_url('/blocks/pmkbackoffice/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        $this->content->footer = html_writer::link($url, get_string('addpage', 'block_simplehtml'));
        return $this->content;
    }
}
