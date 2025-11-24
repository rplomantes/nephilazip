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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Event observer for enrol_nephilazip.
 *
 * @package     enrol_nephilazip
 * @copyright   2024 Roy Ploamntes <rplomantes@nephilaweb.com.ph>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_nephilazip;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Handle payment received event.
     *
     * @param \enrol_nephilazip\event\payment_received $event
     */
    public static function payment_received_handler(\enrol_nephilazip\event\payment_received $event) {
        global $DB;

        // Log event reception
        mtrace("Nephila ZIP: Processing payment_received event for objectid={$event->objectid}, userid={$event->userid}");

        try {
            // Validate required data
            if (empty($event->objectid)) {
                mtrace("Error: Missing objectid in event");
                return;
            }

            if (empty($event->userid)) {
                mtrace("Error: Missing userid in event");
                return;
            }

            // Get enrol instance
            $instance = $DB->get_record('enrol', [
                'id' => $event->objectid,
                'enrol' => 'nephilazip',
                'status' => ENROL_INSTANCE_ENABLED
            ], '*', MUST_EXIST);

            // Get user
            $user = $DB->get_record('user', ['id' => $event->userid, 'deleted' => 0, 'suspended' => 0], '*', MUST_EXIST);

            // Get plugin and enrol user
            $plugin = enrol_get_plugin('nephilazip');
            if (!$plugin) {
                mtrace("Error: Unable to load enrol_nephilazip plugin");
                return;
            }

            // ✅ Use correct method name: enrol_user()
            $plugin->enrol_user($instance, $user);

            mtrace("User {$event->userid} successfully enrolled in course {$instance->courseid}");
        } catch (\dml_missing_record_exception $e) {
            mtrace("Record not found: " . $e->getMessage());
        } catch (\Exception $e) {
            mtrace("Unexpected error in payment_received_handler: " . $e->getMessage());
        }
    }
}