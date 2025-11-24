<?php
require_once('../../config.php');
require_login();

global $USER, $DB;

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id'=>$courseid], '*', MUST_EXIST);
$instance = $DB->get_record('enrol', [
    'enrol' => 'nephilazip',
    'courseid' => $courseid,
    'status' => 1
]);

if (!$instance) {
    print_error('No Nephila Zip enrolment instance found for this course.');
}

$amount = floatval($instance->cost);
if ($amount <= 0) {
    print_error('Course cost must be greater than zero.');
}

$baseurl = get_config('enrol_nephilazip', 'baseurl');
if (empty($baseurl)) {
    print_error('Base URL for Nephila Zip is not configured.');
}

$client_reference_id = "course{$courseid}_user{$USER->id}";

$checkouturl = $baseurl . '/checkout?' . http_build_query([
    'client_reference_id' => $client_reference_id,
    'amount' => intval($amount * 100),
    'email' => $USER->email,
    'name' => $USER->firstname . ' ' . $USER->lastname,
]);

redirect($checkouturl);
