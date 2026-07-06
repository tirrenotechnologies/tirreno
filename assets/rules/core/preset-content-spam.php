<?php

return [
    'description'   => 'Content spam',
    'rules'         => [
        'B11'   => 'high',      // New account (1 day)
        'B26'   => 'high',      // Single event sessions
        'E03'   => 'high',      // Suspicious words in email
        'E04'   => 'high',      // Numeric email name
        'E21'   => 'high',      // No vowels in email
        'I02'   => 'high',      // IP hosting domain
        'I03'   => 'high',      // IP appears in spam list
        // Extreme
        'R01'   => 'extreme',   // IP in blacklist
        'R02'   => 'extreme',   // Email in blacklist
    ],
];
