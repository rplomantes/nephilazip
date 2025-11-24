<?php
require_once('../../config.php');
require_login();

global $USER, $DB;

// Get course ID from URL
$courseid = required_param('id', PARAM_INT);

// Load course
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Get active Nephila ZIP enrol instance
$instance = $DB->get_record('enrol', [
    'enrol' => 'nephilazip',
    'courseid' => $courseid,
    'status' => 1
], '*', MUST_EXIST);

// Validate cost
$amount = floatval($instance->cost);
if ($amount <= 0) {
    print_error('Course cost must be greater than zero.');
}

// Get environment
$env = get_config('enrol_nephilazip', 'environment', 'sandbox');

// Get base URL based on environment
$baseurl = ($env === 'production')
    ? get_config('enrol_nephilazip', 'production_url')
    : get_config('enrol_nephilazip', 'sandbox_url');

if (empty($baseurl)) {
    print_error('Base URL for Nephila Zip is not configured.');
}

// Dynamic client reference
$client_reference_id = "course{$courseid}_user{$USER->id}";

// Construct checkout URL
$checkouturl = $baseurl . '/checkout?' . http_build_query([
    'client_reference_id' => $client_reference_id,
    'amount' => intval($amount * 100), // amount in cents
    'email' => $USER->email,
    'name' => $USER->firstname . ' ' . $USER->lastname,
]);

// Redirect user to Zip checkout
redirect($checkouturl);

