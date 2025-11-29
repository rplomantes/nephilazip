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

namespace enrol_nephilazip\api;


defined('MOODLE_INTERNAL') || die();


class client
{
    protected string $apikey;
    protected string $secret;
    protected string $baseurl;


    public function __construct()
    {
        $env = get_config('enrol_nephilazip', 'environment', 'sandbox');
        $this->apikey = get_config('enrol_nephilazip', 'apikey');
        $this->secret = get_config('enrol_nephilazip', 'secretkey');
        $this->baseurl = $env === 'production'
            ? get_config('enrol_nephilazip', 'production_url')
            : get_config('enrol_nephilazip', 'sandbox_url');
    }
    public function createCheckout(int $courseid, int $userid, string $clientRef, float $amount, string $email, string $name): string
    {
        global $CFG;


        $amountint = intval(round($amount * 100)); // centavos


        $payload = [
            'client_reference_id' => $clientRef,
            'amount' => $amountint,
            'currency' => 'PHP',
            'email' => $email,
            'name' => $name,
            'success_url' => $CFG->wwwroot . "/enrol/nephilazip/return.php?courseid={$courseid}&userid={$userid}&ref={$clientRef}",
            'cancel_url' => $CFG->wwwroot . "/course/view.php?id={$courseid}",
        ];


        $ch = curl_init(rtrim($this->baseurl, '/') . '/checkout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apikey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));


        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);


        if ($response === false) {
            debugging('ZIP API curl error: ' . $curlErr, DEBUG_DEVELOPER);
            throw new \moodle_exception('zip_api_error', 'enrol_nephilazip');
        }


        if ($httpCode < 200 || $httpCode >= 300) {
            debugging('ZIP API error (HTTP ' . $httpCode . '): ' . $response, DEBUG_DEVELOPER);
            throw new \moodle_exception('zip_api_error', 'enrol_nephilazip');
        }


        $data = json_decode($response, true);
        if (empty($data['payment_url'])) {
            debugging('Invalid ZIP response: ' . $response, DEBUG_DEVELOPER);
            throw new \moodle_exception('invalid_zip_response', 'enrol_nephilazip');
        }


        return $data['payment_url'];
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        if (empty($this->secret) || empty($signature)) {
            return false;
        }
        $hash = hash_hmac('sha256', $payload, $this->secret);
        return hash_equals($hash, $signature);
    }

    public function getPaymentDetails(string $paymentid): array
    {
        $url = rtrim($this->baseurl, '/') . '/payments/' . urlencode($paymentid);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apikey,
        ]);


        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return [];
        }


        return json_decode($response, true) ?? [];
    }
    public function getPaymentByReference(string $clientRef): array
    {
        $url = rtrim($this->baseurl, '/') . '/payments?client_reference_id=' . urlencode($clientRef);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apikey,
        ]);


        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return [];
        }


        $data = json_decode($response, true);
        // Expecting an array of payments or an object with data[] depending on provider
        if (isset($data['data'])) {
            return $data['data'];
        }
        return is_array($data) ? $data : [];
    }
}
