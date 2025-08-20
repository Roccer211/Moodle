<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $OUTPUT;

require_login();

// Проверяем права - только администраторы могут смотреть участников
$context = context_system::instance();
if (!has_capability('block/olympiads:manage', $context)) {
    throw new moodle_exception('nopermissions', 'error', '', 'Просмотр участников');
}

$olympiadid = optional_param('id', 0, PARAM_INT);

if (!$olympiadid) {
    redirect(new moodle_url('/blocks/olympiads/view.php'));
}

// Получаем данные об олимпиаде
$olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);

$PAGE->set_url('/blocks/olympiads/participants.php', ['id' => $olympiadid]);
$PAGE->set_context($context);
$PAGE->set_title('Участники олимпиады: ' . format_string($olympiad->name));
$PAGE->set_heading('Участники олимпиады: ' . format_string($olympiad->name));

// Получаем список участников
$participants = $DB->get_records_sql("
    SELECT p.*, u.firstname, u.lastname, u.email, u.timecreated as user_created
    FROM {block_olympiads_participants} p
    JOIN {user} u ON u.id = p.userid
    WHERE p.olympiadid = :olympiadid
    ORDER BY u.lastname, u.firstname
", ['olympiadid' => $olympiadid]);

echo $OUTPUT->header();

// Хлебные крошки
echo html_writer::link(new moodle_url('/blocks/olympiads/view.php'), '← Назад к списку олимпиад', ['class' => 'btn btn-secondary mb-3']);
echo $OUTPUT->heading('Участники олимпиады: ' . format_string($olympiad->name));

// Информация об олимпиаде
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', 'Информация об олимпиаде', ['class' => 'card-title']);

echo html_writer::start_div('row');
echo html_writer::start_div('col-md-6');
echo html_writer::tag('p', html_writer::tag('strong', 'Название: ') . format_string($olympiad->name));
echo html_writer::tag('p', html_writer::tag('strong', 'Описание: ') . format_text($olympiad->description, FORMAT_HTML));
echo html_writer::end_div();

echo html_writer::start_div('col-md-6');
echo html_writer::tag('p', html_writer::tag('strong', 'Регистрация: ') .
    userdate($olympiad->startdate, '%d.%m.%Y %H:%M') . ' - ' .
    userdate($olympiad->enddate, '%d.%m.%Y %H:%M'));
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div();
echo html_writer::end_div();

// Таблица участников
if (empty($participants)) {
    echo $OUTPUT->notification('Нет зарегистрированных участников', 'info');
} else {
    $table = new html_table();
    $table->head = ['ФИО', 'Email', 'Дата регистрации'];
    $table->attributes['class'] = 'table table-striped table-bordered';

    foreach ($participants as $participant) {
        $table->data[] = [
            fullname($participant),
            $participant->email,
            userdate($participant->timecreated, '%d.%m.%Y %H:%M')
        ];
    }

    echo html_writer::table($table);
    echo html_writer::tag('p', 'Всего участников: ' . count($participants), ['class' => 'text-muted mt-3']);
}

echo $OUTPUT->footer();