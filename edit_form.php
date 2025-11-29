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


// edit_form.php 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class enrol_nephila_zip_edit_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata['instance'] ?? null;

        // Cost
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_nephila_zip'));
        $mform->setType('cost', PARAM_FLOAT);
        $mform->setDefault($instance->cost ?? 0);

        // Currency
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_nephila_zip'), [
            'PHP' => 'PHP',
            'USD' => 'USD'
        ]);
        $mform->setDefault($instance->currency ?? 'PHP');

        // Role assignment
        $roles = get_default_enrol_roles(context_course::instance($instance->courseid));
        $mform->addElement('select', 'roleid', get_string('role', 'enrol_nephila_zip'), $roles);
        $mform->setDefault($instance->roleid ?? key($roles));

        $this->add_action_buttons(true, get_string('addinstance', 'enrol'));
    }
}
