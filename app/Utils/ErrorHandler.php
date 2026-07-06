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

namespace Tirreno\Utils;

class ErrorHandler {
    public static function getErrorDetails(): array {
        $errorTraceArray = [];

        $errorTraceString = tirreno('storage')->get('ERROR.trace');
        $errorTraceArray = preg_split('/$\R?^/m', $errorTraceString);
        $maximalStringIndex = 0;
        $maximalStringLength = 0;
        $iters = count($errorTraceArray);

        for ($i = 0; $i < $iters; ++$i) {
            $currentStringLength = strlen($errorTraceArray[$i]);
            if ($maximalStringLength < $currentStringLength) {
                $maximalStringIndex = $i;
                $maximalStringLength = $currentStringLength;
            }
        }

        if ($iters > 1) {
            array_splice($errorTraceArray, $maximalStringIndex, 1);
        }

        $iters = count($errorTraceArray);
        for ($i = 0; $i < $iters; ++$i) {
            $errorTraceArray[$i] = strip_tags($errorTraceArray[$i]);
            $errorTraceArray[$i] = str_replace(['&gt;', '&lt;'], ['>', '<'], $errorTraceArray[$i]);
        }

        $errorCode = tirreno('storage')->get('ERROR.code');
        $errorMessage = join(', ', ['ERROR_' . $errorCode, tirreno('storage')->get('ERROR.text')]);

        return [
            'ip'        => tirreno('request')->getIp(),
            'code'      => $errorCode,
            'message'   => $errorMessage,
            'trace'     => join('<br>', $errorTraceArray),
            'date'      => date('l jS \of F Y h:i:s A'),
            'post'      => tirreno('storage')->get('POST'),
            'get'       => tirreno('storage')->get('GET'),
        ];
    }

    public static function saveErrorInformation(array $errorData): void {
        tirreno('utils')->logger->log(null, $errorData['message']);

        $errorTraceArray = explode('<br>', $errorData['trace']);
        $printErrorTraceToLog = tirreno('storage')->get('PRINT_ERROR_TRACE_TO_LOG');
        if ($printErrorTraceToLog) {
            $iters = count($errorTraceArray);

            for ($i = 0; $i < $iters; ++$i) {
                tirreno('utils')->logger->log(null, $errorTraceArray[$i]);
            }
        }

        $database = tirreno('utils')->database->getDb();
        if ($database && tirreno('utils')->routes->getCurrentRequestOperator()->isLoggedIn()) {
            $errorData['sql_log'] = $database->log();
            tirreno('models')->log->insertRecord($errorData);

            tirreno('utils')->logger->log('SQL', $errorData['sql_log']);
        }

        if ($errorData['code'] === 500) {
            $toName = 'Admin';
            $toAddress = tirreno('utils')->variables->getAdminEmail();
            if ($toAddress === null) {
                tirreno('utils')->logger->log('Log mail error', 'ADMIN_EMAIL is not set');

                return;
            }

            $subject = tirreno('storage')->get('error_email_subject') ?? tirreno('utils')->constants->BASE_ERROR_EMAIL_SUBJECT;
            $subject = sprintf($subject, $errorData['code']);

            $currentTime = date('d-m-Y H:i:s');
            $errorMessage = $errorData['message'];
            $errorTrace = $errorData['trace'];

            $hosts = json_encode(tirreno('utils')->variables->getHosts());

            $message = tirreno('storage')->get('error_email_body_template') ?? tirreno('utils')->constants->BASE_ERROR_EMAIL_BODY_TEMPLATE;
            $message = sprintf($message, $currentTime, $hosts, $errorMessage, $errorTrace);

            tirreno('utils')->mailer->send($toName, $toAddress, $subject, $message, true);
        }
    }

    protected static function getAjaxErrorMessage(array $errorData): string|false {
        return json_encode(
            [
                'status' => false,
                'code' => $errorData['code'],
                'message' => sprintf('Request finished with code %s', $errorData['code']),
            ],
        );
    }

    public static function getOnErrorHandler(): callable {
        /**
         * Custom onError handler: http://stackoverflow.com/questions/19763414/fat-free-framework-f3-custom-404-page-and-others-errors, https://groups.google.com/forum/#!topic/f3-framework/BOIrLs5_aEA
         * We can can use $f3->get('ERROR.text'), and decide which template should be displayed.
         *
         */
        return function (): void {
            $errorData = self::getErrorDetails();

            // clean template if anything was rendered already
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            self::saveErrorInformation($errorData);

            if ($errorData['code'] === 403 && !tirreno('request')->isAjax()) {
                tirreno('response')->redirect('/logout');

                return;
            }

            // Add handling 404 error
            if ($errorData['code'] === 404) {
            }

            if (tirreno('request')->isAjax()) {
                echo self::getAjaxErrorMessage($errorData);

                return;
            }

            $errorData['message'] = 'ERROR_' . $errorData['code'];
            $errorData['raw'] = false;

            if ($errorData['code'] !== 404) {
                $errorData['extra_message'] = tirreno('storage')->get('ErrorPage_extra_message');
                $errorData['raw'] = true;
            }

            if ($errorData['code'] === 400) {
                $errorData['message'] = 'Error code ' . tirreno('utils')->errorCodes->INVALID_HOSTNAME;
                $errorData['extra_message'] = 'Visit page via correct hostname: ' . tirreno('utils')->variables->getHostWithProtocol() . tirreno('request')->getPath();
            }

            if ($errorData['code'] === 503) {
                $errorData['message'] = 'Error code ' . tirreno('utils')->errorCodes->FAILED_DB_CONNECT;
                $errorData['extra_message'] = 'Database connection failed.';
            }

            if ($errorData['code'] === 422) {
                $errorData['message'] = 'Error code ' . tirreno('utils')->errorCodes->INCOMPLETE_CONFIG;
                $errorData['extra_message'] = 'App configuration is incomplete. Check config/local/config.local.ini and possible environment overrides.';
            }

            if ($errorData['code'] === 500 && tirreno('utils')->variables->getDebugLevel() > 0) {
                $errorText = tirreno('storage')->get('ERROR.text');
                if ($errorText) {
                    $errorData['extra_message'] = strval($errorText);
                    $errorData['raw'] = false;
                }
            } else {
                unset($errorData['trace']);
            }

            $pageParams = tirreno('pages')->error->getPageParams($errorData);
            $response = new \Tirreno\Views\Frontend();

            $response->data = $pageParams;
            echo $response->render();
        };
    }

    public static function getCronErrorHandler(): callable {
        return function (): void {
            $errorData = self::getErrorDetails();
            self::saveErrorInformation($errorData);
        };
    }

    public static function exceptionErrorHandler(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
