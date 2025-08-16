<?php
defined('MOODLE_INTERNAL') || die();

$plugin = new stdClass(); // Добавлено объявление переменной

$plugin->version   = 2024060100;       // YYYYMMDDXX
$plugin->requires  = 2020061500;       // Требуемая версия Moodle (3.9+)
$plugin->component = 'block_olympiads'; // Тип и название плагина
$plugin->maturity  = MATURITY_ALPHA;    // Стадия разработки
$plugin->release   = 'v0.1';            // Версия релиза