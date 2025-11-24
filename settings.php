<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // -------------------------------
    // API Settings Heading
    // -------------------------------
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/api',
        get_string('api_settings', 'enrol_nephilazip'),
        ''
    ));

    // API Key (password unmask)
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_nephilazip/apikey',
        get_string('apikey', 'enrol_nephilazip'),
        get_string('apikey_desc', 'enrol_nephilazip'),
        '',
        PARAM_RAW_TRIMMED
    ));
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/apikey_help',
        '',
        get_string('apikey_help', 'enrol_nephilazip')
    ));
   ///checkout url
    $settings->add(new admin_setting_configtext(
    'enrol_nephilazip/baseurl',
    get_string('baseurl', 'enrol_nephilazip'),
    get_string('baseurl_desc', 'enrol_nephilazip'),
    'https://sandbox-api.nephila_zip.co',
    PARAM_URL
));

    // -------------------------------
    // Environment Selector
    // -------------------------------
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

    // -------------------------------
    // Webhook URL Display
    // -------------------------------
    $webhookurl = new moodle_url('/enrol/nephilazip/webhook.php');
    $settings->add(new admin_setting_heading(
        'enrol_nephilazip/webhook',
        get_string('webhookurl', 'enrol_nephilazip'),
        get_string('webhookurl_desc', 'enrol_nephilazip', $webhookurl->out(false))
    ));
}
