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
 * Language strings.
 *
 * @package     enrol_nephilazip
 * @copyright   2024 Roy Ploamntes <rplomantes@nephilaweb.com.ph>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ZIP Pay Enrolment';

$string['nephilazip:config'] = 'Configure Nephila ZIP enrol instances';
$string['enrol:manage'] = 'Manage ZIP enrol instances';

$string['cost'] = 'Cost';
$string['invalidcost'] = 'Invalid cost';

$string['api_settings'] = 'Nephila Zip API Settings';
$string['api_settings_desc'] = 'Configure the API credentials and environment for Nephila Zip payment gateway.';

$string['apikey'] = 'API Key';
$string['apikey_desc'] = 'Your API key provided by ZIP. Required to create checkout sessions.';

$string['secretkey'] = 'Secret Key';
$string['secretkey_desc'] = 'Your secret key provided by ZIP, used to verify webhook events.';

$string['environment'] = 'Environment';
$string['environment_desc'] = 'Select whether to use Sandbox (Test) or Production (Live) environment.';
$string['sandbox'] = 'Sandbox';
$string['production'] = 'Production';

$string['sandbox_url'] = 'Sandbox Base URL';
$string['sandbox_url_desc'] = 'The base URL for sandbox/test environment. Provided by ZIP.';

$string['production_url'] = 'Production Base URL';
$string['production_url_desc'] = 'The base URL for live/production environment. Provided by ZIP.';

$string['webhookurl'] = 'Webhook URL';
$string['webhookurl_desc'] = 'Set this URL in your ZIP dashboard to receive payment notifications: <br><strong>{$a}</strong>';

$string['paynow'] = 'Pay Now';
$string['paymentamount'] = 'Amount to pay: {$a} PHP';

$string['currency'] = '₱';
$string['buy'] = 'Buy';
$string['buy_course'] = 'Buy course {$a}';
$string['price'] = 'Price';

