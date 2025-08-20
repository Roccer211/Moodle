<?php
require_once('../../config.php');
global $DB, $USER;

require_login();
require_sesskey();

$action = required_param('action', PARAM_ALPHA);
$response = ['success' => false];

try {
    switch ($action) {
        case 'unregister':
            $olympiadid = required_param('olympiadid', PARAM_INT);
            $DB->delete_records('block_olympiads_participants', [
                'olympiadid' => $olympiadid,
                'userid' => $USER->id
            ]);
            $response['success'] = true;
            break;

        default:
            $response['error'] = 'Неизвестное действие';
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);