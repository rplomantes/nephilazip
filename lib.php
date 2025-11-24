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

class enrol_nephilazip_plugin extends enrol_plugin {

    public function get_newinstance_defaults() {
        return [
            'status' => 0,
            'cost' => '0.00',
        ];
    }

    public function allow_enrol($instance) {
        return false; // Disable manual enrol button
    }

    public function allow_manage($instance) {
        return true; // Teachers may configure
    }

    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * REQUIRED IN MOODLE 4.5
     */
    public function can_hide_show_instance($instance) {
        // Allow hiding and showing the instance in course enrolment methods
        return true;
    }

    /**
     * ALTERNATIVE REQUIRED METHOD (optional)
     * If your plugin does not support suspend/resume
     */
    public function can_delete_instance($instance) {
        return true; // Or false if you want to block deletion
    }

    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_nephilazip'), ['size' => 6]);
        $mform->setType('cost', PARAM_FLOAT);
        $mform->addRule('cost', null, 'numeric', null, 'client');
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
            'courseid' => $courseid
        ]);
    }

    public function user_enrolment($instance, $user, $timestart = 0, $timeend = 0,
                                   $status = ENROL_USER_ACTIVE, $recovergrades = null) {
        global $DB;

        $ue = new stdClass();
        $ue->enrolid = $instance->id;
        $ue->userid = $user->id;
        $ue->timestart = $timestart ?: time();
        $ue->timeend = $timeend;
        $ue->status = $status;
        $ue->modifierid = $user->id;
        $ue->timecreated = time();
        $ue->timemodified = time();
        $ue->id = $DB->insert_record('user_enrolments', $ue);

        $context = context_course::instance($instance->courseid);

        $event = \core\event\user_enrolment_created::create([
            'objectid' => $ue->id,
            'context' => $context,
            'relateduserid' => $user->id,
            'other' => ['enrol' => $instance->enrol]
        ]);

        $event->add_record_snapshot('user_enrolments', $ue);
        $event->trigger();

        return $ue;
    }
}
