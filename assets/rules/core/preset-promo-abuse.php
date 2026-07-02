<?php

return [
    'description'   => 'Promo abuse',
    'rules'         => [
        // Medium
        'E06'   => 'medium',    // Consecutive digits in email
        // High
        'B12'   => 'high',      // New account (1 week)
        'D06'   => 'high',      // Multiple devices per user
        'E03'   => 'high',      // Suspicious words in email
        'E04'   => 'high',      // Numeric email name
        'I02'   => 'high',      // IP hosting domain
        'I05'   => 'high',      // IP belongs to commercial VPN
        'I06'   => 'high',      // IP belongs to datacenter
        // Extreme
        'I04'   => 'extreme',   // Shared IP
        'P03'   => 'extreme',   // Shared phone number
        'R01'   => 'extreme',   // IP in blacklist
        'R02'   => 'extreme',   // Email in blacklist
    ],
];
