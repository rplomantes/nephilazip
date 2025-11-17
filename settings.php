<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/api',
        get_string('api_settings', 'enrol_nephilazip'),
        ''
    ));

    // ✅ SINGLE API KEY FIELD (with description)
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_nephilazip/apikey',
        get_string('apikey', 'enrol_nephilazip'),
        get_string('apikey_desc', 'enrol_nephilazip'),
        '',
        PARAM_TEXT
    ));

    // Environment selector
    $envoptions = [
        'sandbox' => get_string('sandbox', 'enrol_nephilazip'),
        'production' => get_string('production', 'enrol_nephilazip')
    ];
    $settings->add(new admin_setting_configselect(
        'enrol_nephilazip/environment',
        get_string('environment', 'enrol_nephilazip'),
        '',
        'sandbox',
        $envoptions
    ));

    // Webhook URL info (read-only)
    $webhookurl = new moodle_url('/enrol/nephilazip/webhook.php');
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/webhook',
        get_string('webhookurl', 'enrol_nephilazip'),
        get_string('webhookurl_desc', 'enrol_nephilazip', $webhookurl->out(false))
    ));
}