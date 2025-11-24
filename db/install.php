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

/**
 * Post-install script for enrol_nephilazip.
 *
 * This sets initial default configuration values when the plugin
 * is first installed.
 *
 * @return void
 */
function xmldb_enrol_nephilazip_install() {
    global $DB;

    // Default sandbox and production URLs (should match your settings.php).
    $defaults = [
        'apikey'         => '', // Admin must enter this manually.
        'secretkey'      => '', // Add this if your plugin uses it.
        'sandbox_url'    => 'https://sandbox-api.nephila_zip.ph',
        'production_url' => 'https://api.nephila_zip.ph',
        'environment'    => 'sandbox', // Default using sandbox.
    ];

    foreach ($defaults as $key => $value) {
        // Insert only if not already present (to avoid overwriting upgrades).
        if (!$DB->record_exists('config_plugins', [
            'plugin' => 'enrol_nephilazip',
            'name'   => $key
        ])) {
            $record = new stdClass();
            $record->plugin = 'enrol_nephilazip';
            $record->name   = $key;
            $record->value  = $value;
            $DB->insert_record('config_plugins', $record);
        }
    }
}
