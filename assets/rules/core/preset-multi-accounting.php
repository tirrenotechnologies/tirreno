<?php

return [
    'description'   => 'Multi-accounting',
    'rules'         => [
        // Medium
        'D07'   => 'medium',    // Several desktop devices
        'D08'   => 'medium',    // Two or more phone devices
        'I09'   => 'medium',    // Numerous IPs
        // High
        'D06'   => 'high',      // Multiple devices per user
        'B22'   => 'high',      // Multiple IP addresses in one session
        // Extreme
        'I04'   => 'extreme',   // Shared IP
        'P03'   => 'extreme',   // Shared phone number
        'R01'   => 'extreme',   // IP in blacklist
        'R02'   => 'extreme',   // Email in blacklist
        'R03'   => 'extreme',   // Phone in blacklist
    ],
];
