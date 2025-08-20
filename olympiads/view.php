<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $OUTPUT;

require_login();

// ВРЕМЕННО убираем проверку прав для тестирования
// require_capability('block/olympiads:view', context_system::instance());

$context = context_system::instance();
$PAGE->set_url('/blocks/olympiads/view.php');
$PAGE->set_context($context);
$PAGE->set_title('Список олимпиад');
$PAGE->set_heading('Список олимпиад');

// Получаем список олимпиад
$olympiads = $DB->get_records('block_olympiads', null, 'startdate DESC');

echo $OUTPUT->header();

// Кнопка добавления для администраторов
if (is_siteadmin() || has_capability('block/olympiads:manage', $context)) {
    $url = new moodle_url('/blocks/olympiads/edit.php');
    echo html_writer::link($url, 'Добавить олимпиаду',
        array('class' => 'btn btn-primary mb-3'));
}

// Таблица олимпиад
if (empty($olympiads)) {
    echo $OUTPUT->notification('Нет доступных олимпиад', 'info');
} else {
    $table = new html_table();
    $table->head = array('Название', 'Начало', 'Окончание', 'Действия');

    foreach ($olympiads as $olympiad) {
        $actions = '';

        if (is_siteadmin() || has_capability('block/olympiads:manage', $context)) {
            // Кнопка редактирования
            $editurl = new moodle_url('/blocks/olympiads/edit.php', array('id' => $olympiad->id));
            $actions .= html_writer::link($editurl, '✏️', array('title' => 'Редактировать'));

            // Кнопка удаления
            $deleteurl = new moodle_url('/blocks/olympiads/edit.php',
                array('delete' => $olympiad->id, 'sesskey' => sesskey()));
            $actions .= ' ' . html_writer::link($deleteurl, '🗑️',
                    array('title' => 'Удалить', 'onclick' => 'return confirm("Вы уверены?");'));
        }

        $table->data[] = array(
            format_string($olympiad->name),
            userdate($olympiad->startdate),
            userdate($olympiad->enddate),
            $actions
        );
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();