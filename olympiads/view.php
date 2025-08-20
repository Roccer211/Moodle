<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $OUTPUT;

require_login();

$context = context_system::instance();

// ПРОВЕРКА ПРАВ - Альтернативный способ
$has_view_access = false;
$has_manage_access = false;

// 1. Проверяем является ли пользователь администратором
if (is_siteadmin()) {
    $has_view_access = true;
    $has_manage_access = true;
} else {
    // 2. Проверяем права через прямую проверку в базе данных
    $user_roles = get_user_roles($context);

    foreach ($user_roles as $role) {
        // Проверяем право на просмотр
        $view_right = $DB->get_record('role_capabilities', [
            'roleid' => $role->roleid,
            'capability' => 'block/olympiads:view',
            'permission' => 1
        ]);

        if ($view_right) {
            $has_view_access = true;
        }

        // Проверяем право на управление
        $manage_right = $DB->get_record('role_capabilities', [
            'roleid' => $role->roleid,
            'capability' => 'block/olympiads:manage',
            'permission' => 1
        ]);

        if ($manage_right) {
            $has_manage_access = true;
        }
    }
}

// Если нет прав на просмотр - показываем ошибку
if (!$has_view_access) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Доступ запрещен');
    echo '<div class="alert alert-danger">';
    echo '<p>У вас нет прав для просмотра олимпиад.</p>';
    echo '<p>Обратитесь к администратору для получения прав доступа.</p>';

    // Отладочная информация
    if (is_siteadmin()) {
        echo '<hr>';
        echo '<h5>Отладочная информация (только для админов):</h5>';
        echo '<p>Роли пользователя: ';
        $roles = get_user_roles($context);
        foreach ($roles as $role) {
            echo $role->shortname . ' (' . $role->roleid . '), ';
        }
        echo '</p>';

        echo '<p>Права в базе для роли manager: ';
        $rights = $DB->get_records_sql("
            SELECT capability, permission 
            FROM {role_capabilities} 
            WHERE roleid = 1 AND capability LIKE 'block/olympiads%'
        ");
        foreach ($rights as $right) {
            echo $right->capability . '=' . $right->permission . ', ';
        }
        echo '</p>';
    }

    echo '</div>';
    echo $OUTPUT->footer();
    exit();
}

// НАСТРОЙКА СТРАНИЦЫ
$PAGE->set_url('/blocks/olympiads/view.php');
$PAGE->set_context($context);
$PAGE->set_title('Список олимпиад');
$PAGE->set_heading('Список олимпиад');

// Получаем список олимпиад
$olympiads = $DB->get_records('block_olympiads', null, 'startdate DESC');

echo $OUTPUT->header();

// Кнопка добавления для тех, у кого есть права на управление
if ($has_manage_access) {
    $url = new moodle_url('/blocks/olympiads/edit.php');
    echo html_writer::link($url, 'Добавить олимпиаду',
        array('class' => 'btn btn-success mb-3'));
}

// Таблица олимпиад
if (empty($olympiads)) {
    echo $OUTPUT->notification('Нет доступных олимпиад', 'info');
} else {
    $table = new html_table();
    $table->head = array('Название', 'Регистрация', 'Действия');
    $table->attributes['class'] = 'table table-striped table-hover';

    foreach ($olympiads as $olympiad) {
        $actions = '';

        // Кнопки действий только для тех, у кого есть права на управление
        if ($has_manage_access) {
            // Кнопка редактирования
            $editurl = new moodle_url('/blocks/olympiads/edit.php', array('id' => $olympiad->id));
            $actions .= html_writer::link($editurl, '✏️', array(
                'title' => 'Редактировать',
                'class' => 'btn btn-sm btn-outline-primary mr-1'
            ));

            // Кнопка участников
            $participantsurl = new moodle_url('/blocks/olympiads/participants.php', array('id' => $olympiad->id));
            $actions .= html_writer::link($participantsurl, '👥', array(
                'title' => 'Просмотр участников',
                'class' => 'btn btn-sm btn-outline-info mr-1'
            ));

            // Кнопка удаления
            $deleteurl = new moodle_url('/blocks/olympiads/edit.php',
                array('delete' => $olympiad->id, 'sesskey' => sesskey()));
            $actions .= html_writer::link($deleteurl, '🗑️', array(
                'title' => 'Удалить',
                'class' => 'btn btn-sm btn-outline-danger',
                'onclick' => 'return confirm("Вы уверены, что хотите удалить эту олимпиаду?");'
            ));
        }

        $table->data[] = array(
            html_writer::tag('strong', format_string($olympiad->name)),
            userdate($olympiad->startdate, '%d.%m.%Y') . ' - ' . userdate($olympiad->enddate, '%d.%m.%Y'),
            $actions
        );
    }

    echo html_writer::table($table);

    // Статистика
    echo html_writer::tag('p', 'Всего олимпиад: ' . count($olympiads),
        array('class' => 'text-muted mt-3'));
}

// Отладочная информация для админов
if (is_siteadmin()) {
    echo '<div class="mt-4 p-3 bg-light border rounded">';
    echo '<h6>Отладочная информация:</h6>';
    echo '<p>Права просмотра: ' . ($has_view_access ? '✅ Есть' : '❌ Нет') . '</p>';
    echo '<p>Права управления: ' . ($has_manage_access ? '✅ Есть' : '❌ Нет') . '</p>';

    echo '<p>Роли пользователя: ';
    $roles = get_user_roles($context);
    foreach ($roles as $role) {
        echo $role->shortname . ' (' . $role->roleid . '), ';
    }
    echo '</p>';
    echo '</div>';
}

echo $OUTPUT->footer();