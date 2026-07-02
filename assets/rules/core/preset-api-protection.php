<?php

return [
    'description'   => 'API protection',
    'rules'         => [
        // Medium
        'B24'   => 'medium',    // Empty referer
        // High
        'D01'   => 'high',      // Device is unknown
        // Extreme
        'B04'   => 'extreme',   // Multiple 5xx errors
        'B05'   => 'extreme',   // Multiple 4xx errors
        'B06'   => 'extreme',   // Potentially vulnerable URL
        'B18'   => 'extreme',   // HEAD request
        'D03'   => 'extreme',   // Device is bot
        'D10'   => 'extreme',   // Potentially vulnerable User-Agent
        'I01'   => 'extreme',   // IP belongs to TOR
        'R01'   => 'extreme',   // IP in blacklist
    ],
];
