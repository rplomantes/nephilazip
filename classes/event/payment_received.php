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
namespace enrol_nephilazip\event;

defined('MOODLE_INTERNAL') || die();

class payment_received extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // Create
        $this->data['level'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'enrol';
    }

    public static function get_name(): string {
        return get_string('eventpaymentreceived', 'enrol_nephilazip');
    }

    public function get_description(): string {
        return "Payment was received for enrolment ID {$this->objectid}.";
    }

    
    public function get_url(): \moodle_url {
        return new \moodle_url('/enrol/nephilazip/view.php', [
            'id' => $this->courseid
        ]);
    }

    // Optional: validate_data()
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->objectid)) {
            throw new \coding_exception('The objectid must be set.');
        }
        if (empty($this->courseid)) {
            throw new \coding_exception('The courseid must be set.');
        }
        if (empty($this->userid)) {
            throw new \coding_exception('The userid must be set.');
        }
    }
}
