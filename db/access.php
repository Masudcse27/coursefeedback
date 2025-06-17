<?php
$capabilities = [
    'block/coursefeedback:givefeedback' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => ['student' => CAP_ALLOW],
    ],
    'block/coursefeedback:viewdashboard' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ],
    ],
];