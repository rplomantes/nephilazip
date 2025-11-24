<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data.
 *
 * @package     enrol_nephilazip
 * @copyright   2024 Roy Ploamntes <rplomantes@nephilaweb.com.ph>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
$client = new \enrol_nephilazip\api\client();
$checkoutUrl = $client->createCheckout(
    $courseid,
    "course{$courseid}_user{$USER->id}",
    $USER->email,
    $USER->firstname . ' ' . $USER->lastname,
    $amount
);
redirect($checkoutUrl);


// Redirect user to Zip checkout
//redirect($checkouturl);

