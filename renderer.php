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

// File: enrol/nephilazip/renderer.php

namespace enrol_nephilazip\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_writer;

class renderer extends plugin_renderer_base {

    /**
     * Renders a secure "Pay Now" button linking to Zip checkout.
     *
     * @param int $courseid
     * @param object $user
     * @param string $paymenturl
     * @return string HTML
     */
    public function display_checkout_button($courseid, $user, $paymenturl) {
        // Validate URL
        if (empty($paymenturl) || !filter_var($paymenturl, FILTER_VALIDATE_URL)) {
            return '';
        }

        // Escape URL and create safe link
        $safeurl = clean_param($paymenturl, PARAM_LOCALURL);
        if (!$safeurl) {
            debugging('Invalid payment URL detected', DEBUG_DEVELOPER);
            return '';
        }

        $label = get_string('paynow', 'enrol_nephilazip');

        $attributes = [
            'class' => 'btn btn-primary',
            'role'  => 'button',
            'aria-label' => $label,
            'target' => '_blank', // Open in new tab
            'rel'   => 'noopener noreferrer'
        ];

        return html_writer::link($safeurl, $label, $attributes);
    }
}

