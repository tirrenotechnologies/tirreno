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

include './app/tirreno.php';

//Load configuration file with all project variables
tirreno('router')->config('config/config.ini');

//Load specific configuration only for local development
$localConfigFile = tirreno('utils')->variables->getConfigFile();
$localConfigFile = sprintf('config/%s', $localConfigFile);

//Load local configuration file
if (file_exists($localConfigFile)) {
    tirreno('router')->config($localConfigFile);
}

//Use custom onError function
tirreno('storage')->set('ONERROR', tirreno('utils')->errorHandler->getOnErrorHandler());

if (tirreno('utils')->variables->getForceHttps() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    ini_set('session.cookie_secure', '1');
}

if (!tirreno('utils')->variables->completedConfig()) {
    if (is_file('./install/index.php')) {
        if ((tirreno('request')->getPath() === '/' || tirreno('request')->getPath() === '/index.php')) {
            tirreno('response')->redirect('./install/index.php');
        } else {
            header('HTTP/1.1 404 Page Not Found');
            echo 'Error ' . tirreno('utils')->errorCodes->INCOMPLETE_CONFIG . ' Configuration is missing. Please visit /install/ to continue.';
            exit(0);
        }
    } else {
        header('HTTP/1.1 404 Page Not Found');
        echo 'Error ' . tirreno('utils')->errorCodes->INCOMPLETE_CONFIG . ' Configuration and install/index.php are missing.';
        exit(0);
    }
}

//Load routes configuration
tirreno('router')->config('config/routes.ini');
tirreno('router')->config('config/apiEndpoints.ini');

//Override F3 host
tirreno('utils')->access->cleanHost();

if (tirreno('utils')->variables->getDB()) {
    //Load dictionary file
    tirreno('storage')->set('LOCALES', 'app/Dictionary/');
    tirreno('storage')->set('LANGUAGE', 'en');

    // tmp load all assets pages
    $pages = tirreno('assets')->pages->getAllPagesObjects();
}

tirreno('router')->run();
