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
 * Plugin version and other meta-data.
 *
 * @package     enrol_nephilazip
 * @copyright   2024 Roy Ploamntes <rplomantes@nephilaweb.com.ph>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // -----------------------------
    // API Settings Heading
    // -----------------------------
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/api',
        get_string('api_settings', 'enrol_nephilazip'),
        get_string('api_settings_desc', 'enrol_nephilazip')
    ));

    // API Key
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_nephilazip/apikey',
        get_string('apikey', 'enrol_nephilazip'),
        get_string('apikey_desc', 'enrol_nephilazip'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // Secret Key
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_nephilazip/secretkey',
        get_string('secretkey', 'enrol_nephilazip'),
        get_string('secretkey_desc', 'enrol_nephilazip'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // Sandbox Base URL
    $settings->add(new admin_setting_configtext(
        'enrol_nephilazip/sandbox_url',
        get_string('sandbox_url', 'enrol_nephilazip'),
        get_string('sandbox_url_desc', 'enrol_nephilazip'),
        'https://sandbox-api.nephila_zip.ph',
        PARAM_URL
    ));

    // Production Base URL
    $settings->add(new admin_setting_configtext(
        'enrol_nephilazip/production_url',
        get_string('production_url', 'enrol_nephilazip'),
        get_string('production_url_desc', 'enrol_nephilazip'),
        'https://api.nephila_zip.ph',
        PARAM_URL
    ));

    // Environment selector
    $envoptions = [
        'sandbox' => get_string('sandbox', 'enrol_nephilazip'),
        'production' => get_string('production', 'enrol_nephilazip')
    ];

    $settings->add(new admin_setting_configselect(
        'enrol_nephilazip/environment',
        get_string('environment', 'enrol_nephilazip'),
        get_string('environment_desc', 'enrol_nephilazip'),
        'sandbox',
        $envoptions
    ));

    // Webhook URL
    $webhookurl = new moodle_url('/enrol/nephilazip/webhook.php');

    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/webhook',
        get_string('webhookurl', 'enrol_nephilazip'),
        get_string('webhookurl_desc', 'enrol_nephilazip', $webhookurl->out(false))
    ));
}
