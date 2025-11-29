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

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$input = file_get_contents('php://input');
if (!$input) {
    http_response_code(400);
    exit('Empty payload');
}

$signature = $_SERVER['HTTP_X_ZIP_SIGNATURE'] ?? '';
$client = new \enrol_nephilazip\api\client();


if (!$client->verifyWebhook($input, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}


$event = json_decode($input, true);
if (!$event || empty($event['type'])) {
    http_response_code(400);
    exit('Invalid JSON');
}

if ($event['type'] !== 'payment.captured') {
    // acknowledge other events
    http_response_code(200);
    exit('Event ignored');
}
$data = $event['data'] ?? [];
$amount = (int)($data['amount'] ?? 0);
$client_ref = $data['client_reference_id'] ?? '';


if (!preg_match('/^course(\d+)_user(\d+)$/', $client_ref, $m)) {
    http_response_code(400);
    exit('Invalid client reference');
}


$courseid = (int)$m[1];
$userid = (int)$m[2];

$instance = $DB->get_record('enrol', [
    'enrol' => 'nephilazip',
    'courseid' => $courseid,
    'status' => ENROL_INSTANCE_ENABLED
], '*', IGNORE_MISSING);


if (!$instance) {
    http_response_code(404);
    exit('Enrol instance not found');
}


$user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0, 'suspended' => 0]);
if (!$user) {
    http_response_code(400);
    exit('User not found or suspended');
}


$expectedamount = (int)round($instance->cost * 100);
if ($amount !== $expectedamount) {
    http_response_code(400);
    exit('Amount mismatch');
}


$plugin = enrol_get_plugin('nephilazip');
if (!$plugin) {
    http_response_code(500);
    exit('Enrol plugin not available');
}


// Enrol the user (use roleid from instance if present)
$roleid = isset($instance->roleid) && $instance->roleid ? (int)$instance->roleid : $DB->get_field('role', 'id', ['shortname' => 'student']);
$plugin->enrol_user($instance, $userid, $roleid, time(), 0);


http_response_code(200);
echo 'OK';
exit;
