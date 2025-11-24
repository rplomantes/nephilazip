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

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_course_login($course);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/enrol/nephilazip/check_status.php', ['courseid' => $courseid]));
$PAGE->set_title('Check Payment Status');

echo $OUTPUT->header();

$clientref = "course{$courseid}_user{$USER->id}";

// Call Zip API to get latest status
$status = \enrol_nephilazip\api\client::get_payment_status($clientref);

if ($status === 'captured') {
    $plugin = enrol_get_plugin('nephilazip');
    $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'nephilazip']);
    if ($instance) {
        $plugin->enrol_user($instance, $USER);
        echo $OUTPUT->notification('Payment confirmed! You are now enrolled.', 'success');
        redirect(new moodle_url('/course/view.php?id=' . $courseid), 'Loading course...', 3);
    }
} elseif (in_array($status, ['failed', 'declined', 'cancelled'])) {
    echo $OUTPUT->notification("Payment was {$status}. Please try again.", 'error');
} else {
    echo $OUTPUT->notification('Payment is still pending. Try again later.', 'info');
}

echo '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '" class="btn btn-secondary">Back to Course</a>';

echo $OUTPUT->footer();