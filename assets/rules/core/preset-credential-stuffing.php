<?php

return [
    'description'   => 'Credential stuffing',
    'rules'         => [
        // High
        'A01'   => 'high',      // Multiple login fail
        'A02'   => 'high',      // Login failed on new device
        'B04'   => 'high',      // Multiple 5xx errors
        'B05'   => 'high',      // Multiple 4xx errors
        'B06'   => 'high',      // Potentially vulnerable URL
        'I02'   => 'high',      // IP hosting domain
        'I03'   => 'high',      // IP appears in spam list
        'I06'   => 'high',      // IP belongs to datacenter
        // Extreme
        'R01'   => 'extreme',   // IP in blacklist
    ],
];
