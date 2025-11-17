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
            // 'customchar1' => '' // API Key
        ];
    }

    public function allow_enrol($instance) {
        return false; // Block manual enrol button
    }

    public function allow_manage($instance) {
        return true; // Allow teachers to configure
    }

    public function use_standard_editing_ui() {
        return true;
    }

    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_nephilazip'), ['size' => 6]);
        $mform->setType('cost', PARAM_FLOAT);
        $mform->addRule('cost', null, 'numeric', null, 'client');

        // $mform->addElement('text', 'customchar1', get_string('apikey', 'enrol_nephilazip'));
        // $mform->setType('customchar1', PARAM_TEXT);
        // $mform->addHelpButton('customchar1', 'apikey', 'enrol_nephilazip');

        // Show webhook URL
        // $webhookurl = new moodle_url('/enrol/nephilazip/webhook.php');
        // $mform->addElement('static', 'webhookinfo', get_string('webhookurl', 'enrol_nephilazip'),
        //     get_string('webhookurl_desc', 'enrol_nephilazip', $webhookurl->out(false)));
    }

    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];
        if (!is_numeric($data['cost']) || $data['cost'] < 0) {
            $errors['cost'] = get_string('invalidcost', 'enrol_nephilazip');
        }
        // if (empty($data['customchar1'])) {
        //     $errors['customchar1'] = get_string('apikeyrequired', 'enrol_nephilazip');
        // }
        return $errors;
    }

    // public function can_add_instance($courseid) {
    //     return true;
    // }
    public function can_add_instance($courseid) {
    global $DB;
    // Only allow if no instance exists yet
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