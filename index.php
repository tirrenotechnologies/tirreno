<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

session_name('CONSOLESESSION');

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

chdir(dirname(__FILE__));

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/libs/bcosca/fatfree-core/base.php';

    // PSR-4 autoloader
    spl_autoload_register(function (string $className): void {
        $libs = [
            'Ruler\\' => '/libs/ruler/ruler/src/',
            'PHPMailer\\PHPMailer\\' => '/libs/phpmailer/phpmailer/src/',
            'Tirreno\\' => '/app/',
        ];

        foreach ($libs as $namespace => $path) {
            if (str_starts_with($className, $namespace)) {
                require __DIR__ . $path . str_replace([$namespace, '\\'], ['', '/'], $className) . '.php';
                break;
            }
        }
    });
}

$f3 = \Base::instance();

//Load configuration file with all project variables
$f3->config('config/config.ini');

//Load specific configuration only for local development
$localConfigFile = \Tirreno\Utils\Variables::getConfigFile();
$localConfigFile = sprintf('config/%s', $localConfigFile);

//Load local configuration file
if (file_exists($localConfigFile)) {
    $f3->config($localConfigFile);
}

//Use custom onError function
$f3->set('ONERROR', \Tirreno\Utils\ErrorHandler::getOnErrorHandler());

if (\Tirreno\Utils\Variables::getForceHttps() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    ini_set('session.cookie_secure', '1');
}

if (!\Tirreno\Utils\Variables::completedConfig()) {
    if (is_file("./install/index.php")) {
        if (($f3->get('PATH') === '/' || $f3->get('PATH') === '/index.php')) {
            $f3->reroute('./install/index.php');
        } else {
            header('HTTP/1.1 404 Page Not Found');
            echo 'Error ' . \Tirreno\Utils\ErrorCodes::INCOMPLETE_CONFIG . ' Configuration is missing. Please visit /install/ to continue.';
            exit(0);
        }
    } else {
        header('HTTP/1.1 404 Page Not Found');
        echo 'Error ' . \Tirreno\Utils\ErrorCodes::INCOMPLETE_CONFIG . ' Configuration and install/index.php are missing.';
        exit(0);
    }
}

//Load routes configuration
$f3->config('config/routes.ini');
$f3->config('config/apiEndpoints.ini');

//Override F3 host
\Tirreno\Utils\Access::cleanHost();

if (\Tirreno\Utils\Variables::getDB()) {
    //Load dictionary file
    $f3->set('LOCALES', 'app/Dictionary/');
    $f3->set('LANGUAGE', 'en');

    $constants = \Tirreno\Utils\Constants::get();
    $cron = \Tirreno\Controllers\Cron::instance();

    $f3->set('CONSTANTS', $constants);
}

$f3->run();
