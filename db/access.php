<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // This is REQUIRED for editing enrol instances
    'enrol/nephilazip:config' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],

    // Optional: keep manage if you use it elsewhere
    'enrol/nephilazip:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];