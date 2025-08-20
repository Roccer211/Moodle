<?php
defined('MOODLE_INTERNAL') || die();

class block_olympiads extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_olympiads');
    }

    public function get_content() {
        global $OUTPUT, $CFG, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $context = context_system::instance();

        // Для администраторов - ссылки на управление
        if (has_capability('block/olympiads:manage', $context)) {
            $url = new moodle_url('/blocks/olympiads/view.php');
            $this->content->text .= html_writer::link($url, 'Управление олимпиадами',
                array('class' => 'btn btn-primary mb-2 d-block'));

            $url = new moodle_url('/blocks/olympiads/edit.php');
            $this->content->text .= html_writer::link($url, 'Добавить олимпиаду',
                array('class' => 'btn btn-success mb-2 d-block'));
        }

        // Для всех пользователей - список олимпиад
        $olympiads = $DB->get_records('block_olympiads', null, 'startdate DESC');
        $now = time();

        $template_data = [];
        foreach ($olympiads as $olympiad) {
            $can_register = ($now >= $olympiad->startdate && $now <= $olympiad->enddate);
            $already_registered = $DB->record_exists('block_olympiads_participants', [
                'olympiadid' => $olympiad->id,
                'userid' => $USER->id
            ]);

            $template_data[] = [
                'id' => $olympiad->id,
                'name' => format_string($olympiad->name),
                'description' => format_text($olympiad->description, FORMAT_HTML),
                'startdate' => $olympiad->startdate,
                'enddate' => $olympiad->enddate,
                'can_register' => $can_register && !$already_registered,
                'already_registered' => $already_registered,
                'registration_closed' => $now > $olympiad->enddate,
                'time_not_come' => $now < $olympiad->startdate
            ];
        }

        // Рендерим шаблон
        $data = [
            'olympiads' => $template_data,
            'sesskey' => sesskey(),
            'config' => ['wwwroot' => $CFG->wwwroot] // Добавьте эту строку
        ];

        $this->content->text .= $OUTPUT->render_from_template('block_olympiads/olympiads_list', $data);

        return $this->content;
    }

    public function applicable_formats() {
        return array('my' => true);
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_multiple() {
        return false;
    }
}