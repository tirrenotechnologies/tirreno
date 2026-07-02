<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/tirreno.php';

$f3 = \Base::instance();

$runtimeDir = sys_get_temp_dir() . '/dashboard-tests';

if (!is_dir($runtimeDir)) {
    mkdir($runtimeDir, 0777, true);
}

$f3->set('LOGS', $runtimeDir . '/');
$f3->set('TEMP', $runtimeDir . '/');
$f3->set('CACHE', false);

$f3->set('LOG_FILE', 'phpunit.log');
$f3->set('LOG_SQL_FILE', 'phpunit-sql.log');
$f3->set('LOG_DELIMITER', PHP_EOL);
