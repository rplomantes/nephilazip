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

    $defaults = [
        'apikey'         => '',
        'secretkey'      => '',
        'sandbox_url'    => 'https://sandbox-api.nephila_zip.ph',
        'production_url' => 'https://api.nephilazip.ph',
        'environment'    => 'sandbox',
    ];

    foreach ($defaults as $name => $value) {
        if (!$DB->record_exists('config_plugins', ['plugin' => 'enrol_nephilazip', 'name' => $name])) {
            $DB->insert_record('config_plugins', (object)[
                'plugin' => 'enrol_nephilazip',
                'name'   => $name,
                'value'  => $value
            ]);
        }
    }
}

