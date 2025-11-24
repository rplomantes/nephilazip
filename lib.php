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

    /**
     * Default values when a new enrolment instance is created.
     */
    public function get_newinstance_defaults() {
        return [
            'status' => ENROL_INSTANCE_ENABLED,
            'cost'   => '0.00',
        ];
    }

    /**
     * Disable manual self-enrol button (always false).
     */
    public function allow_enrol(stdClass $instance) {
        return false;
    }

    /**
     * Allow managers/teachers to configure this method.
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Use Moodle's standard form for editing instances.
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * (Required in Moodle 4.5)  
     * Allow hiding or showing this instance.
     */
    public function can_hide_show_instance($instance) {
        return true;
    }

    /**
     * Allows deleting an instance.
     */
    public function can_delete_instance($instance) {
        return true;
    }

    /**
     * Extra custom fields for the enrol instance.
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_nephilazip'), ['size' => 6]);
        $mform->setType('cost', PARAM_RAW_TRIMMED);
        $mform->addRule('cost', null, 'numeric', null, 'client');
    }

    /**
     * Validate instance editing form fields.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];

        if (!is_numeric($data['cost']) || $data['cost'] < 0) {
            $errors['cost'] = get_string('invalidcost', 'enrol_nephilazip');
        }

        return $errors;
    }

    /**
     * Only allow one enrol instance per course.
     */
    public function can_add_instance($courseid) {
        global $DB;

        return !$DB->record_exists('enrol', [
            'enrol'    => 'nephilazip',
            'courseid' => $courseid,
        ]);
    }

    /**
     * Enrol a user after successful Zip payment.
     * 
     * This function is usually called by webhook script in locallib.php
     */
    public function enrol_user($instance, $user, $timestart = 0, $timeend = 0,
                           $status = ENROL_USER_ACTIVE, $recovergrades = null)  {
        global $DB;

        $timestart = $timestart ?: time();

        // Create record.
        $ue = new stdClass();
        $ue->enrolid      = $instance->id;
        $ue->userid       = $user->id;
        $ue->timestart    = $timestart;
        $ue->timeend      = $timeend;
        $ue->status       = $status;
        $ue->modifierid   = $user->id;
        $ue->timecreated  = time();
        $ue->timemodified = time();

        // Insert enrolment.
        $ue->id = $DB->insert_record('user_enrolments', $ue);


