<?php
defined('MOODLE_INTERNAL') || die();

class block_olympiads extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_olympiads');
    }

    public function get_content() {
        global $OUTPUT, $CFG, $DB;

        if ($this->content !== null) return $this->content;

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) return $this->content;

        $context = context_system::instance();

        $url = new moodle_url('/blocks/olympiads/view.php');
        $this->content->text .= html_writer::link($url, 'Просмотр олимпиад',
            array('class' => 'btn btn-primary'));

        if (has_capability('block/olympiads:manage', $context)) {
            $url = new moodle_url('/blocks/olympiads/edit.php');
            $this->content->text .= html_writer::link($url, 'Добавить олимпиаду',
                array('class' => 'btn btn-success', 'style' => 'margin-left: 10px;'));
        }

        return $this->content;
    }

    public function applicable_formats() {
        return array('my' => true);
    }

    public function has_config() {
        return true;
    }
}