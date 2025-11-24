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

class client {

    protected string $apikey;
    protected string $secret;
    protected string $baseurl;

    public function __construct() {
        $env = get_config('enrol_nephilazip', 'environment', 'sandbox');
        $this->apikey  = get_config('enrol_nephilazip', 'apikey');
        $this->secret  = get_config('enrol_nephilazip', 'secretkey');
        $this->baseurl = $env === 'production'
            ? get_config('enrol_nephilazip', 'production_url')
            : get_config('enrol_nephilazip', 'sandbox_url');
    }

    /**
     * Create a checkout URL for the user to pay.
     */
   public function createCheckout(string $clientRef, float $amount, string $email, string $name): string {
    $payload = [
        'client_reference_id' => $clientRef,
        'amount'              => intval($amount * 100), // convert to cents
        'email'               => $email,
        'name'                => $name,
    ];

    // Initialize cURL
    $ch = curl_init($this->baseUrl . '/checkout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $this->apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Debugging + error check
    if ($httpCode !== 200) {
        debugging('ZIP API error (HTTP ' . $httpCode . '): ' . $response, DEBUG_DEVELOPER);
        throw new \moodle_exception('zip_api_error', 'enrol_nephilazip');
    }

    $data = json_decode($response, true);

    if (!isset($data['payment_url'])) {
        throw new \moodle_exception('invalid_zip_response', 'enrol_nephilazip');
    }

    return $data['payment_url'];
}


    /**
     * Verify incoming webhook using secret key (HMAC signature).
     */
    public function verifyWebhook(string $payload, string $signature): bool {
        if (empty($this->secret) || empty($signature)) {
            return false;
        }
        $hash = hash_hmac('sha256', $payload, $this->secret);
        return hash_equals($hash, $signature);
    }

    /**
     * Get payment details from ZIP (optional API call).
     */
    public function getPaymentDetails(string $paymentid): array {
        $url = $this->baseurl . '/payments/' . urlencode($paymentid);

        $opts = [
            'http' => [
                'header' => "Authorization: Bearer {$this->apikey}\r\n",
                'method' => 'GET',
            ],
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);

        return $response ? json_decode($response, true) : [];
    }
}
