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

// locallib.php

defined('MOODLE_INTERNAL') || die();

/**
 * Get the active environment: sandbox or production.
 *
 * @return string 'sandbox' or 'production'
 */
function nephilazip_get_environment(): string {
    $env = get_config('enrol_nephilazip', 'environment');
    return $env ?: 'sandbox';
}

/**
 * Get the base URL depending on environment.
 *
 * @return string API base URL
 */
function nephilazip_get_baseurl(): string {
    $env = nephilazip_get_environment();

    if ($env === 'production') {
        return trim(get_config('enrol_nephilazip', 'production_url'));
    }

    return trim(get_config('enrol_nephilazip', 'sandbox_url'));
}

/**
 * Get the API Key from plugin settings.
 *
 * @return string API key
 */
function nephilazip_get_api_key(): string {
    $key = get_config('enrol_nephilazip', 'apikey');
    return trim($key);
}

/**
 * Get the Secret Key used for webhook verification.
 *
 * @return string Secret key
 */
function nephilazip_get_secret_key(): string {
    $secret = get_config('enrol_nephilazip', 'secretkey');
    return trim($secret);
}

/**
 * Builds a secure checkout URL for Zip.
 *
 * @param object $course
 * @param object $user
 * @param float $amount
 * @return string Checkout URL
 * @throws moodle_exception If config is invalid
 */
function nephilazip_build_checkout_url($course, $user, float $amount): string {
    $baseurl = nephilazip_get_baseurl();
    if (empty($baseurl)) {
        throw new moodle_exception('err_baseurl', 'enrol_nephilazip');
    }

    if ($amount <= 0) {
        throw new moodle_exception('err_amount', 'enrol_nephilazip');
    }

    $client_reference_id = "course{$course->id}_user{$user->id}";

    $returnurl = new moodle_url('/enrol/nephilazip/return.php', [
        'courseid' => $course->id,
        'userid'   => $user->id,
        'ref'      => $client_reference_id
    ]);

    $params = [
        'client_reference_id' => $client_reference_id,
        'amount' => intval($amount * 100),
        'email' => $user->email,
        'name' => fullname($user),
         'return_url'=> $returnurl->out(false), // Send this to Zip
    ];

    return $baseurl . '/checkout?' . http_build_query($params);
}

/**
 * Verify Zip webhook signature using HMAC-SHA256.
 *
 * @param string $rawbody The raw POST body
 * @param string $received_signature The value from X-Zip-Signature header
 * @return bool True if valid
 */
function nephilazip_verify_webhook($rawbody, $received_signature) {
    if (empty($received_signature)) {
        return false;
    }

    $secret = get_config('enrol_nephilazip', 'secretkey');
    if (empty($secret)) {
        return false;
    }

    $computed = hash_hmac('sha256', $rawbody, $secret);
    return hash_equals($computed, $received_signature);
}

/**
 * Log API transaction into Moodle logstore.
 *
 * @param string $message
 * @param array $data
 */
function nephilazip_log(string $message, array $data = []) {
    debugging("Nephila ZIP: $message\n" . json_encode($data, JSON_PRETTY_PRINT), DEBUG_DEVELOPER);
}