<?php

return [
    'description'   => 'Account takeover',
    'rules'         => [
        // Medium
        'A03'   => 'medium',    // New device and new country
        'A04'   => 'medium',    // New device and new subnet
        'A08'   => 'medium',    // Browser language changed
        'B01'   => 'medium',    // Multiple countries
        'B02'   => 'medium',    // User has changed a password
        'B03'   => 'medium',    // User has changed an email
        'B21'   => 'medium',    // Multiple devices in one session
        'D04'   => 'medium',    // Rare browser device
        'D05'   => 'medium',    // Rare OS device
        'I03'   => 'medium',    // IP appears in spam list
        'I09'   => 'medium',    // Numerous IPs
        // High
        'B04'   => 'high',      // Multiple 5xx errors
        'B05'   => 'high',      // Multiple 4xx errors
        'B19'   => 'high',      // Night time requests
        'B20'   => 'high',      // Multiple countries in one session
        'D01'   => 'high',      // Device is unknown
        // Extreme
        'A01'   => 'extreme',   // Multiple login fail
        'A02'   => 'extreme',   // Login failed on new device
        'A05'   => 'extreme',   // Password change on new device
        'A06'   => 'extreme',   // Password change in new country
        'B06'   => 'extreme',   // Potentially vulnerable URL
        'E19'   => 'extreme',   // Multiple emails changed
        'I01'   => 'extreme',   // IP belongs to TOR
        'I04'   => 'extreme',   // Shared IP
        'R01'   => 'extreme',   // IP in blacklist
    ],
];
