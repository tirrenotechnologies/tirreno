<?php

return [
    'description'   => 'Bot detection',
    'rules'         => [
        // Extreme
        'B04'   => 'extreme',   // Multiple 5xx errors
        'B05'   => 'extreme',   // Multiple 4xx errors
        'B06'   => 'extreme',   // Potentially vulnerable URL
        'B19'   => 'extreme',   // Night time requests
        'D10'   => 'extreme',   // Potentially vulnerable User-Agent
        'I01'   => 'extreme',   // IP belongs to TOR
    ],
];
