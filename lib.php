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

defined('MOODLE_INTERNAL') || die();

class enrol_nephilazip_plugin extends enrol_plugin {

    public function get_newinstance_defaults() {
        return [
            'status' => ENROL_INSTANCE_ENABLED,
            'cost'   => '0.00',
        ];
    }

    public function allow_enrol(stdClass $instance) {
        return false;
    }

    public function allow_manage(stdClass $instance) {
        return true;
    }

    public function use_standard_editing_ui() {
        return true;
    }

    public function can_hide_show_instance($instance) {
        return true;
    }

    public function can_delete_instance($instance) {
        return true;
    }

    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_nephilazip'), ['size' => 6]);
        $mform->setType('cost', PARAM_RAW_TRIMMED);
        $mform->addRule('cost', null, 'numeric', null, 'client');
        $mform->setDefault('cost', $instance->cost ?? '0.00');
    }

    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];
        if (!is_numeric($data['cost']) || $data['cost'] < 0) {
            $errors['cost'] = get_string('invalidcost', 'enrol_nephilazip');
        }
        return $errors;
    }

    public function can_add_instance($courseid) {
        global $DB;
        return !$DB->record_exists('enrol', [
            'enrol' => 'nephilazip',
            'courseid' => $courseid,
        ]);
    }

    /**
     * THIS IS WHAT SHOWS THE BUTTON ON THE COURSE ENROLMENT PAGE
     */
    public function enrol_page_hook($instance) {
        global $USER, $OUTPUT;

        $courseid = $instance->courseid;
        $userid   = $USER->id;
        $amount   = $instance->cost ?? 0;

        // Already enrolled?
        if (is_enrolled(\context_course::instance($courseid), $USER)) {
            return $OUTPUT->notification(get_string('alreadyenrolled', 'enrol_nephilazip'), 'notifysuccess');
        }

        $formatted = format_float($amount, 2);

        $payurl = new \moodle_url('/enrol/nephilazip/purchase.php', [
            'courseid' => $courseid,
            'userid' => $userid,
            'amount' => $amount
        ]);

        $output  = \html_writer::start_div('nephilazip-enrol-box');
        $output .= \html_writer::tag('h4', 'Course Fee: ₱ ' . $formatted);
        $output .= \html_writer::tag('p', 'Click below to pay via ZIP.');
        $output .= \html_writer::link($payurl, 'Pay and Enrol', ['class' => 'btn btn-primary']);
        $output .= \html_writer::end_div();

        return $output;
    }
}
