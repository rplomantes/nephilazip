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
 * The enrol plugin nephilazip.
 *
 * @package     enrol_nephilazip
 * @copyright   2024 Roy Ploamntes <rplomantes@nephilaweb.com.ph>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// check_status.php - Manually verify payment status

require_once(__DIR__ . '/../../config.php');
require_login();


global $DB, $USER;


$courseid = required_param('courseid', PARAM_INT);
$clientref = required_param('ref', PARAM_RAW);


// Attempt to find payment by reference via API
$client = new \enrol_nephilazip\api\client();
$payments = $client->getPaymentByReference($clientref);


$foundPaid = false;
$paymentid = null;
$status = 'not_found';


foreach ($payments as $p) {
    // Typical provider response fields may vary; try common keys
    $pstatus = $p['status'] ?? ($p['payment_status'] ?? '');
    $pid = $p['id'] ?? ($p['payment_id'] ?? null);
    if ($pstatus === 'paid' || $pstatus === 'captured' || $pstatus === 'succeeded') {
        $foundPaid = true;
        $paymentid = $pid;
        $status = $pstatus;
        break;
    }
}


if ($foundPaid) {
    // Enrol user now using webhook-like logic
    if (preg_match('/^course(\d+)_user(\d+)$/', $clientref, $m)) {
        $courseid = (int)$m[1];
        $userid = (int)$m[2];


        $instance = $DB->get_record('enrol', [
            'enrol' => 'nephilazip',
            'courseid' => $courseid,
            'status' => ENROL_INSTANCE_ENABLED
        ], '*', IGNORE_MISSING);


        if ($instance) {
            $plugin = enrol_get_plugin('nephilazip');
            $roleid = isset($instance->roleid) && $instance->roleid ? (int)$instance->roleid : $DB->get_field('role', 'id', ['shortname' => 'student']);
            $plugin->enrol_user($instance, $userid, $roleid, time(), 0);
            redirect(new moodle_url('/course/view.php', ['id' => $courseid]), 'Payment confirmed — you are now enrolled');
        }
    }
}


// Not paid or not found
redirect(new moodle_url('/enrol/nephilazip/return.php', ['courseid' => $courseid, 'userid' => $USER->id, 'ref' => $clientref]));
