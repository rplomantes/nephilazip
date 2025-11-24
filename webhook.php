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


define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

global $DB;

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Disable compression/buffering
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('implicit_flush', 'On');
ob_implicit_flush(true);

// Read raw input
$input = file_get_contents('php://input');
if (!$input) {
    http_response_code(400);
    exit('Empty payload');
}

// Get signature
$signature = $_SERVER['HTTP_X_ZIP_SIGNATURE'] ?? '';
require_once(__DIR__ . '/locallib.php');

if (!nephilazip_verify_webhook($input, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

// Decode JSON
$event = json_decode($input, true);
if (!$event || empty($event['type'])) {
    http_response_code(400);
    exit('Invalid JSON');
}

if ($event['type'] !== 'payment.captured') {
    http_response_code(200); // Acknowledge other events
    exit('Not a payment capture event');
}

nephilazip_log("Processing payment webhook", $event);

// Extract data
$payment = $event['data'] ?? [];
$amount = (int)($payment['amount'] ?? 0);

$client_ref = $payment['client_reference_id'] ?? '';
if (!preg_match('/^course(\d+)_user(\d+)$/', $client_ref, $matches)) {
    http_response_code(400);
    nephilazip_log("Invalid client reference format", ['ref' => $client_ref]);
    exit('Invalid client reference');
}

$courseid = (int)$matches[1];
$userid   = (int)$matches[2];

// Validate course and user
$instance = $DB->get_record('enrol', [
    'enrol' => 'nephilazip',
    'courseid' => $courseid,
    'status' => ENROL_INSTANCE_ENABLED
]);

if (!$instance) {
    http_response_code(404);
    nephilazip_log("Enrol instance not found", ['courseid' => $courseid]);
    exit('Enrolment instance not found');
}

$user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0, 'suspended' => 0]);
if (!$user) {
    http_response_code(400);
    exit('User not found or suspended');
}

// Verify amount
$expectedamount = (int)round($instance->cost * 100);
if ($amount !== $expectedamount) {
    http_response_code(400);
    nephilazip_log("Amount mismatch", compact('amount', 'expectedamount', 'courseid', 'userid'));
    exit('Amount mismatch');
}

// Perform enrolment
$plugin = enrol_get_plugin('nephilazip');
if (!$plugin) {
    http_response_code(500);
    exit('Enrol plugin not available');
}

$plugin->enrol_user($instance, $userid);

echo 'OK';
exit;