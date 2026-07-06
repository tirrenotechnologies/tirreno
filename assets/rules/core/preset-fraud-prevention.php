<?php

return [
    'description'   => 'Fraud prevention',
    'rules'         => [
        // Positive
        'E23'   => 'positive',  // Educational domain (.edu)
        'E24'   => 'positive',  // Government domain (.gov)
        'E25'   => 'positive',  // Military domain (.mil)
        'E26'   => 'positive',  // iCloud mailbox
        // Medium
        'D07'   => 'medium',    // Several desktop devices
        'D08'   => 'medium',    // Two or more phone devices
        // High
        'B19'   => 'high',      // Night time requests
        'B20'   => 'high',      // Multiple countries in one session
        'B21'   => 'high',      // Multiple devices in one session
        'B22'   => 'high',      // Multiple IP addresses in one session
        'E03'   => 'high',      // Suspicious words in email
        'E04'   => 'high',      // Numeric email name
        'E06'   => 'high',      // Consecutive digits in email
        'E07'   => 'high',      // Long email username
        'E21'   => 'high',      // No vowels in email
        'I02'   => 'high',      // IP hosting domain
        'I03'   => 'high',      // IP appears in spam list
        'I04'   => 'high',      // Shared IP
        'I05'   => 'high',      // IP belongs to commercial VPN
        'I06'   => 'high',      // IP belongs to datacenter
        'I09'   => 'high',      // Numerous IPs
        'P03'   => 'high',      // Shared phone number
        // Extreme
        'I01'   => 'extreme',   // IP belongs to TOR
        'R01'   => 'extreme',   // IP in blacklist
        'R03'   => 'extreme',   // Phone in blacklist
    ],
];
