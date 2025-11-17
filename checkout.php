<?php
require_once(__DIR__ . '/../../config.php');

$courseid = required_param('courseid', PARAM_INT);
require_login();
$course = get_course($courseid);
$context = context_course::instance($course->id);
require_capability('enrol/nephilazip:manage', $context);

$instance = $DB->get_record('enrol', [
    'courseid' => $courseid,
    'enrol' => 'nephilazip',
    'status' => 1
], '*', MUST_EXIST);

// if (empty($instance->customchar1)) {
//     throw new moodle_exception('apikeyrequired', 'enrol_nephilazip');
// }

// $apikey = $instance->customchar1;
$api_key = get_config('enrol_nephilazip', 'apikey');
if (empty($api_key)) {
    throw new moodle_exception('apikeyrequired', 'enrol_nephilazip');
}
$amount = (int)round($instance->cost * 100); // Convert to cents

$payload = [
    'currency' => 'PHP',
    'line_items' => [[
        'name' => format_string($course->fullname),
        'amount' => $amount,
        'quantity' => 1
    ]],
    'cancel_url' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),
    'success_url' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),
    'payment_method_types' => ['card', 'gcash', 'maya', 'bpi']
];

$env = get_config('enrol_nephilazip', 'environment', 'sandbox');
$api_base = ($env === 'production')
    ? 'https://api.zip.ph'
    : 'https://sandbox-api.zip.ph';

$ch = curl_init($api_base . '/v2/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $apikey . ':');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    throw new moodle_exception('zip_api_error', 'enrol_nephilazip');
}

$data = json_decode($response, true);
if (!isset($data['payment_url'])) {
    throw new moodle_exception('invalid_zip_response', 'enrol_nephilazip');
}

redirect($data['payment_url']);