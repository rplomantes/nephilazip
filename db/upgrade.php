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
 * Upgrade script for enrol_nephilazip.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_enrol_nephilazip_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // ===========================================================
    // Upgrade step: Create transactions and logs tables (v2025012500)
    // ===========================================================
    if ($oldversion < 2025012500) {

        // ----- Table: enrol_nephilazip_txns -----
        $table = new xmldb_table('enrol_nephilazip_txns');

        // Fields
        $table->add_field('id',            XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid',        XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('courseid',      XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('instanceid',    XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_field('zip_reference', XMLDB_TYPE_CHAR, '100', null, null);
        $table->add_field('status',        XMLDB_TYPE_CHAR, '20', null, null);

        $table->add_field('amount',        XMLDB_TYPE_NUMBER, '10,2', null, null);
        $table->add_field('currency',      XMLDB_TYPE_CHAR, '10', null, null, 'PHP');

        $table->add_field('timecreated',   XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified',  XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        // Keys
        $table->add_key('primary',       XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid_fk',     XMLDB_KEY_FOREIGN, ['userid'],   'user',  ['id']);
        $table->add_key('courseid_fk',   XMLDB_KEY_FOREIGN, ['courseid'], 'course',['id']);
        $table->add_key('instanceid_fk', XMLDB_KEY_FOREIGN, ['instanceid'], 'enrol', ['id']);

        // Create table if missing
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ----- Table: enrol_nephilazip_logs -----
        $table = new xmldb_table('enrol_nephilazip_logs');

        $table->add_field('id',         XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('reference',  XMLDB_TYPE_CHAR, '100', null, null);
        $table->add_field('payload',    XMLDB_TYPE_TEXT, null, null);
        $table->add_field('status',     XMLDB_TYPE_CHAR, '20', null, null);
        $table->add_field('timecreated',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint
        upgrade_plugin_savepoint(true, 2025012500, 'enrol', 'nephilazip');
    }

    return true;
}
