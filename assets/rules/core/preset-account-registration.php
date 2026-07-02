<?php

return [
    'description'   => 'Account registration',
    'rules'         => [
        // Positive
        'E23'   => 'positive',  // Educational domain (.edu)
        'E24'   => 'positive',  // Government domain (.gov)
        'E25'   => 'positive',  // Military domain (.mil)
        'E26'   => 'positive',  // iCloud mailbox
        'I08'   => 'positive',  // IP belongs to Starlink
        'I10'   => 'positive',  // Only residential IPs
        // Medium
        'D08'   => 'medium',    // Two or more phone devices
        'D09'   => 'medium',    // Old browser
        'E07'   => 'medium',    // Long email username
        'E08'   => 'medium',    // Long domain name
        'E21'   => 'medium',    // No vowels in email
        'E22'   => 'medium',    // No consonants in email
        'I05'   => 'medium',    // IP belongs to commercial VPN
        'I06'   => 'medium',    // IP belongs to datacenter
        // High
        'B19'   => 'high',      // Night time requests
        'B21'   => 'high',      // Multiple devices in one session
        'B22'   => 'high',      // Multiple IP addresses in one session
        'B23'   => 'high',      // User's full name contains space or hyphen
        'D01'   => 'high',      // Device is unknown
        'D03'   => 'high',      // Device is bot
        'D04'   => 'high',      // Rare browser device
        'D07'   => 'high',      // Several desktop devices
        'D10'   => 'high',      // Potentially vulnerable User-Agent
        'E01'   => 'high',      // Invalid email format
        'E03'   => 'high',      // Suspicious words in email
        'E04'   => 'high',      // Numeric email name
        'E06'   => 'high',      // Consecutive digits in email
        'I02'   => 'high',      // IP hosting domain
        'I03'   => 'high',      // IP appears in spam list
        'I04'   => 'high',      // Shared IP
        // Extreme
        'B07'   => 'extreme',   // User's full name contains digits
        'B18'   => 'extreme',   // HEAD request
        'I01'   => 'extreme',   // IP belongs to TOR
        'R01'   => 'extreme',   // IP in blacklist
        'R03'   => 'extreme',   // Phone in blacklist
    ],
];
