<?php
define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
require(__DIR__ . '/../../config.php');

$input = file_get_contents('php://input');
$event = json_decode($input, true);

if (!$event || $event['type'] !== 'payment.captured') {
    http_response_code(400);
    exit;
}

$payment = $event['data'] ?? [];
$amount = $payment['amount'] ?? 0;
$client_ref = $payment['client_reference_id'] ?? '';

if (preg_match('/^course(\d+)_user(\d+)$/', $client_ref, $matches)) {
    $courseid = (int)$matches[1];
    $userid = (int)$matches[2];

    $instance = $DB->get_record('enrol', [
        'enrol' => 'nephilazip',
        'courseid' => $courseid,
        'status' => 1
    ]);

    if ($instance && $amount === (int)round($instance->cost * 100)) {
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $plugin = enrol_get_plugin('nephilazip');
        $plugin->user_enrolment($instance, $user);
        echo 'OK';
        exit;
    }
}

http_response_code(400);
exit('Invalid payment');