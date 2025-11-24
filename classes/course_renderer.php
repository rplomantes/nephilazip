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


// classes/course_renderer.php

namespace enrol_nephilazip;

defined('MOODLE_INTERNAL') || die();

use core_course\output\coursecat_helper;
use html_writer;
use moodle_url;

class course_renderer extends \core_course_renderer {

    /**
     * Override course box output to add payment info if this enrolment is active.
     *
     * @param coursecat_helper $chelper
     * @param \stdClass $course
     * @param string $additionalclasses
     * @param bool $contentonly If true, only content without container is returned
     * @return string
     */
    protected function coursecat_coursebox(
        coursecat_helper $chelper,
        $course,
        $additionalclasses = '',
        $contentonly = false
    ) {
        global $DB, $USER;

        // Let parent render the default course box
        $output = parent::coursecat_coursebox($chelper, $course, $additionalclasses, $contentonly);

        // Check if Nephila ZIP enrolment is enabled for this course
        $instance = $DB->get_record('enrol', [
            'enrol' => 'nephilazip',
            'courseid' => $course->id,
            'status' => ENROL_INSTANCE_ENABLED
        ], '*', IGNORE_MISSING);

        if (!$instance) {
            return $output; // No modification needed
        }

        // Get cost and validate
        $cost = (float)$instance->cost;
        if ($cost <= 0) {
            return $output;
        }

        // Format price
        $formattedcost = format_float($cost, 2);
        $currency = get_string('currency', 'enrol_nephilazip'); // e.g., '₱'

        // Build purchase URL
        $buyurl = new moodle_url('/enrol/nephilazip/purchase.php', ['id' => $course->id]);

        // Generate accessible buy button
        $buylink = html_writer::link(
            $buyurl,
            get_string('buy', 'enrol_nephilazip'),
            [
                'class' => 'btn btn-sm btn-primary ms-2',
                'role' => 'button',
                'aria-label' => get_string('buy_course', 'enrol_nephilazip', format_string($course->fullname)),
                'target' => '_blank',
                'rel' => 'noopener noreferrer'
            ]
        );

        // Assemble display
        $displayprice = get_string('price', 'enrol_nephilazip') . ': ' . $currency . $formattedcost;

        $buybox = html_writer::div(
            $displayprice . ' ' . $buylink,
            'nephilazip-course-buy mt-2 d-flex align-items-center'
        );

        // Only append UI if not content-only mode
        if (!$contentonly) {
            $output .= $buybox;
        }

        return $output;
    }
}