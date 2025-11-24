<?php
require_once('../../config.php');
require_login();

global $USER, $DB;

// Get course ID from URL
$courseid = required_param('id', PARAM_INT);

// Get course and enrol instance
$course = $DB->get_record('course', ['id'=>$courseid], '*', MUST_EXIST);
$instance = $DB->get_record('enrol', [
    'enrol' => 'nephilazip',
    'courseid' => $courseid,
    'status' => 1
]);

$amount = $instance->cost ?? 0;

// Get base URL from plugin settings
$baseurl = get_config('enrol_nephilazip', 'baseurl');

// Dynamic client reference
$client_reference_id = "course{$courseid}_user{$USER->id}";

// Construct checkout URL
$checkouturl = $baseurl . '/checkout?' . http_build_query([
    'client_reference_id' => $client_reference_id,
    'amount' => intval($amount * 100), // in cents if required
    'email' => $USER->email,
    'name' => $USER->firstname . ' ' . $USER->lastname,
]);

// Redirect user to checkout
redirect($checkouturl);

