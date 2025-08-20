<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_olympiads_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024073104) {

        // Определение таблицы
        $table = new xmldb_table('block_olympiads_participants');

        // Добавление таблицы
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2024073104) {
        $table = new xmldb_table('block_olympiads');
        $field = new xmldb_field('newfield', XMLDB_TYPE_TEXT, 'null', null, XMLDB_NULL, null, null, 'usermodified');

        // Проверка существования поля
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}