<?php
defined('MOODLE_INTERNAL') || die();

class block_olympiads extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_olympiads');
    }

    public function get_content() {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $this->content->text = html_writer::div(
            get_string('helloworld', 'block_olympiads'),
            'block_olympiads_hello'
        );

        return $this->content;
    }

    public function applicable_formats() {
        return [
            'my' => true,   // Доступен на "Моей странице"
            'site' => false  // Не доступен на главной странице
        ];
    }

    public function has_config() {
        return false;
    }
}