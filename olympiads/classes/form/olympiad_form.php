<?php
defined('MOODLE_INTERNAL') || die();

// Подключаем config.php для доступа к $CFG
require_once(__DIR__ . '/../../../config.php');

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class olympiad_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        // Название олимпиады
        $mform->addElement('text', 'name', 'Название олимпиады');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', 'Обязательное поле', 'required', null, 'client');

        // Описание
        $mform->addElement('editor', 'description', 'Описание олимпиады', null, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 0));
        $mform->setType('description', PARAM_RAW);

        // Дата начала регистрации
        $mform->addElement('date_time_selector', 'startdate', 'Начало регистрации');

        // Дата окончания регистрации
        $mform->addElement('date_time_selector', 'enddate', 'Окончание регистрации');

        // Кнопки
        $this->add_action_buttons(true, 'Сохранить олимпиаду');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Проверка дат
        if ($data['enddate'] <= $data['startdate']) {
            $errors['enddate'] = 'Дата окончания должна быть позже даты начала';
        }

        return $errors;
    }
}