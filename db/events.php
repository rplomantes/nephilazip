<?php

// db/events.php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\enrol_nephilazip\event\payment_received',
        'callback' => \enrol_nephilazip\observer::class . '::payment_received_handler',
        'includefile' => null, // Not needed — autoloader handles it
        'internal' => false,
        'priority' => 9999,
    ],

    // Optional: Observe core events
    [
        'eventname' => '\core\event\course_deleted',
        'callback' => \enrol_nephilazip\observer::class . '::course_deleted',
        'internal' => true,
        'priority' => 1000,
    ],
];
