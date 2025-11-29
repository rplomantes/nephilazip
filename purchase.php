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


$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);


$instance = $DB->get_record('enrol', [
'enrol' => 'nephilazip',
'courseid' => $courseid,
'status' => ENROL_INSTANCE_ENABLED
], '*', IGNORE_MISSING);


if (!$instance) {
print_error('enrolmentinstance');
}


$amount = floatval($instance->cost);
if ($amount <= 0) {
print_error('Course cost must be greater than zero.');
}


$client = new \enrol_nephilazip\api\client();
$clientref = "course{$courseid}_user{$USER->id}";


// createCheckout signature: courseid, userid, clientRef, amount, email, name
$checkoutUrl = $client->createCheckout(
$courseid,
$USER->id,
$clientref,
$amount,
$USER->email,
fullname($USER)
);

redirect($checkoutUrl);
