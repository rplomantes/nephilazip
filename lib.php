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

        // Default value.
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
            'enrol'    => 'nephilazip',
            'courseid' => $courseid,
        ]);
    }

    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
    global $DB, $USER;

    // Use ENROL_USER_ACTIVE if $status is null (matching parent behavior)
    $final_status = is_null($status) ? ENROL_USER_ACTIVE : $status;

    // Optional: validate user exists (optional but safe)
    // $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    $timestart = $timestart ?: time();

    // Check if already enrolled
    if ($ue = $DB->get_record('user_enrolments', ['enrolid' => $instance->id, 'userid' => $userid])) {
        // Update only if something changed
        if ($ue->timestart != $timestart || $ue->timeend != $timeend || $ue->status != $final_status) {
            $ue->timestart    = $timestart;
            $ue->timeend      = $timeend;
            $ue->status       = $final_status;
            $ue->modifierid   = $USER->id ?? $userid;
            $ue->timemodified = time();
            $DB->update_record('user_enrolments', $ue);
        }
        // No need to return anything
        return;
    }

    // New enrolment
    $ue = new stdClass();
    $ue->enrolid      = $instance->id;
    $ue->userid       = $userid;
    $ue->timestart    = $timestart;
    $ue->timeend      = $timeend;
    $ue->status       = $final_status;
    $ue->modifierid   = $USER->id ?? $userid;
    $ue->timecreated  = time();
    $ue->timemodified = $ue->timecreated;

    $ue->id = $DB->insert_record('user_enrolments', $ue);

    // Optional: trigger event (recommended for full compatibility)
    // See parent method for event triggering if needed

    // Optional: assign role if $roleid provided (HIGHLY recommended)
    if ($roleid) {
        $course = get_course($instance->courseid);
        $context = \context_course::instance($course->id);
        if ($this->roles_protected()) {
            role_assign($roleid, $userid, $context->id, 'enrol_' . $this->get_name(), $instance->id);
        } else {
            role_assign($roleid, $userid, $context->id);
        }
    }
}
}
