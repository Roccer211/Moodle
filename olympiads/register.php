<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $OUTPUT, $USER;

require_login();

$olympiadid = required_param('id', PARAM_INT);
$context = context_system::instance();

// Получаем данные об олимпиаде
$olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);

$PAGE->set_url('/blocks/olympiads/register.php', ['id' => $olympiadid]);
$PAGE->set_context($context);
$PAGE->set_title('Запись на олимпиаду: ' . format_string($olympiad->name));
$PAGE->set_heading('Запись на олимпиаду');

$now = time();
$can_register = ($now >= $olympiad->startdate && $now <= $olympiad->enddate);
$already_registered = $DB->record_exists('block_olympiads_participants', [
    'olympiadid' => $olympiadid,
    'userid' => $USER->id
]);

// Обработка записи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $confirm = optional_param('confirm', 0, PARAM_BOOL);

    if ($confirm && $can_register && !$already_registered) {
        $record = new stdClass();
        $record->olympiadid = $olympiadid;
        $record->userid = $USER->id;
        $record->timecreated = time();

        $DB->insert_record('block_olympiads_participants', $record);

        redirect(new moodle_url('/my/'), 'Вы успешно записаны на олимпиаду!', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

// Данные для шаблона
$template_data = [
    'id' => $olympiad->id,
    'name' => format_string($olympiad->name),
    'description' => format_text($olympiad->description, FORMAT_HTML),
    'startdate' => $olympiad->startdate,
    'enddate' => $olympiad->enddate,
    'can_register' => $can_register && !$already_registered,
    'already_registered' => $already_registered,
    'registration_closed' => $now > $olympiad->enddate,
    'time_not_come' => $now < $olympiad->startdate,
    'sesskey' => sesskey(),
    'back_url' => new moodle_url('/my/')
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_olympiads/olympiad_register', $template_data);
echo $OUTPUT->footer();