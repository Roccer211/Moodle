<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $OUTPUT;

require_login();

// ВРЕМЕННО убираем проверку прав для тестирования
// require_capability('block/olympiads:manage', context_system::instance());

$context = context_system::instance();
$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

// Проверяем права через is_siteadmin() вместо capability
if (!is_siteadmin()) {
    throw new moodle_exception('nopermissions', 'error', '', 'Управление олимпиадами');
}

// Обработка удаления
if ($delete && confirm_sesskey()) {
    $DB->delete_records('block_olympiads', array('id' => $delete));
    redirect(new moodle_url('/blocks/olympiads/view.php'));
}

$PAGE->set_url('/blocks/olympiads/edit.php');
$PAGE->set_context($context);
$PAGE->set_title($id ? 'Редактирование олимпиады' : 'Добавление олимпиады');
$PAGE->set_heading($id ? 'Редактирование олимпиады' : 'Добавление олимпиады');

// Получаем данные олимпиады
if ($id) {
    $olympiad = $DB->get_record('block_olympiads', array('id' => $id), '*', MUST_EXIST);
} else {
    $olympiad = new stdClass();
    $olympiad->id = 0;
    $olympiad->name = '';
    $olympiad->description = '';
    $olympiad->startdate = time() + 86400;
    $olympiad->enddate = time() + 604800;
}

// Простая форма (временно без classes/form)
echo $OUTPUT->header();

if ($id) {
    echo $OUTPUT->heading('Редактирование олимпиады');
} else {
    echo $OUTPUT->heading('Добавление олимпиады');
}

// Простая HTML форма
?>
    <form method="post" action="<?php echo $PAGE->url; ?>">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="form-group">
            <label for="name">Название олимпиады:</label>
            <input type="text" id="name" name="name" value="<?php echo format_string($olympiad->name); ?>"
                   class="form-control" required maxlength="255">
        </div>

        <div class="form-group">
            <label for="description">Описание:</label>
            <textarea id="description" name="description" class="form-control" rows="5"><?php
                echo format_text($olympiad->description, FORMAT_HTML);
                ?></textarea>
        </div>

        <div class="form-group">
            <label for="startdate">Начало регистрации:</label>
            <?php
            echo html_writer::select_time('days', 'startdate_day', $olympiad->startdate);
            echo html_writer::select_time('months', 'startdate_month', $olympiad->startdate);
            echo html_writer::select_time('years', 'startdate_year', $olympiad->startdate);
            echo html_writer::select_time('hours', 'startdate_hour', $olympiad->startdate);
            echo html_writer::select_time('minutes', 'startdate_minute', $olympiad->startdate);
            ?>
        </div>

        <div class="form-group">
            <label for="enddate">Окончание регистрации:</label>
            <?php
            echo html_writer::select_time('days', 'enddate_day', $olympiad->enddate);
            echo html_writer::select_time('months', 'enddate_month', $olympiad->enddate);
            echo html_writer::select_time('years', 'enddate_year', $olympiad->enddate);
            echo html_writer::select_time('hours', 'enddate_hour', $olympiad->enddate);
            echo html_writer::select_time('minutes', 'enddate_minute', $olympiad->enddate);
            ?>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="<?php echo new moodle_url('/blocks/olympiads/view.php'); ?>" class="btn btn-secondary">Отмена</a>
    </form>

<?php
// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = new stdClass();
    $data->name = required_param('name', PARAM_TEXT);
    $data->description = optional_param('description', '', PARAM_RAW);

    // Собираем дату из отдельных полей
    $startdate = make_timestamp(
        required_param('startdate_year', PARAM_INT),
        required_param('startdate_month', PARAM_INT),
        required_param('startdate_day', PARAM_INT),
        required_param('startdate_hour', PARAM_INT),
        required_param('startdate_minute', PARAM_INT)
    );

    $enddate = make_timestamp(
        required_param('enddate_year', PARAM_INT),
        required_param('enddate_month', PARAM_INT),
        required_param('enddate_day', PARAM_INT),
        required_param('enddate_hour', PARAM_INT),
        required_param('enddate_minute', PARAM_INT)
    );

    $data->startdate = $startdate;
    $data->enddate = $enddate;

    if ($id) {
        $data->id = $id;
        $DB->update_record('block_olympiads', $data);
    } else {
        $DB->insert_record('block_olympiads', $data);
    }

    redirect(new moodle_url('/blocks/olympiads/view.php'));
}

echo $OUTPUT->footer();