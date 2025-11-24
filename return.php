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


// return.php - Handles redirect from Zip after payment

require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$userid   = required_param('userid', PARAM_INT);
$clientref = required_param('ref', PARAM_RAW);

// Validate current user
if ($USER->id != $userid) {
    throw new moodle_exception('notyourpayment', 'enrol_nephilazip');
}

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_course_login($course);

// Set up page
$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_url(new moodle_url('/enrol/nephilazip/return.php', [
    'courseid' => $courseid, 'userid' => $userid, 'ref' => $clientref
]));

echo $OUTPUT->header();

// Check enrolment status
$plugin = enrol_get_plugin('nephilazip');
$instance = $DB->get_record('enrol', [
    'courseid' => $courseid,
    'enrol' => 'nephilazip'
]);

$is_enrolled = false;
if ($instance && is_enrolled($context, $USER, '', true)) {
    $is_enrolled = true;
}

// Display result
if ($is_enrolled) {
    echo $OUTPUT->box_start('generalbox success');
    echo '<h3>🎉 Success! You’re enrolled!</h3>';
    echo '<p>Thank you for your payment. You now have full access to the course.</p>';
    echo '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '" class="btn btn-primary">Go to Course</a>';
    echo $OUTPUT->box_end();
} else {
    // Enrolment may still be pending (webhook not arrived)
    echo $OUTPUT->box_start('generalbox warning');
    echo '<h3>⏳ We’re confirming your payment…</h3>';
    echo '<p>Your transaction is being processed. Please wait a few moments.</p>';
    echo '<p>If access is not granted within 5 minutes, click below to check manually:</p>';
    echo '<a href="' . $CFG->wwwroot . '/enrol/nephilazip/check_status.php?courseid=' . $courseid . '" class="btn btn-info">Check Payment Status</a>';
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();