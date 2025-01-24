<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Utils;

class ErrorHandler {
    public static function getErrorDetails(\Base $f3): array {
        $errorTraceArray = [];

        $errorTraceString = $f3->get('ERROR.trace');
        $errorTraceArray = preg_split('/$\R?^/m', $errorTraceString);
        $maximalStringIndex = 0;
        $maximalStringLength = 0;

        for ($i = 0, $l = count($errorTraceArray); $i < $l; ++$i) {
            $currentStringLength = strlen($errorTraceArray[$i]);
            if ($maximalStringLength < $currentStringLength) {
                $maximalStringIndex = $i;
                $maximalStringLength = $currentStringLength;
            }
        }

        if (count($errorTraceArray) > 1) {
            array_splice($errorTraceArray, $maximalStringIndex, 1);
        }

        for ($i = 0, $l = count($errorTraceArray); $i < $l; ++$i) {
            $errorTraceArray[$i] = strip_tags($errorTraceArray[$i]);
            $errorTraceArray[$i] = str_replace(['&gt;', '&lt;'], ['>', '<'], $errorTraceArray[$i]);
        }

        $errorCode = $f3->get('ERROR.code');
        $errorMessage = join(', ', ['ERROR_' . $errorCode, $f3->get('ERROR.text')]);

        return [
            'ip' => $f3->IP,
            'code' => $errorCode,
            'message' => $errorMessage,
            'trace' => join('<br>', $errorTraceArray),
            'date' => date('l jS \of F Y h:i:s A'),
            'post' => $f3->get('POST'),
            'get' => $f3->get('GET'),
        ];
    }

    public static function saveErrorInformation(\Base $f3, array $errorData): void {
        \Utils\Logger::log(null, $errorData['message']);

        $errorTraceArray = explode('<br>', $errorData['trace']);
        $printErrorTraceToLog = $f3->get('PRINT_ERROR_TRACE_TO_LOG');
        if ($printErrorTraceToLog) {
            for ($i = 0, $l = count($errorTraceArray); $i < $l; ++$i) {
                \Utils\Logger::log(null, $errorTraceArray[$i]);
            }
        }

        if ($errorData['code'] === 500) {
            $toName = 'Admin';
            $toAddress = \Utils\Variables::getAdminEmail();

            $subject = $f3->get('error_email_subject');
            $subject = sprintf($subject, $errorData['code']);

            $currentTime = date('d-m-Y H:i:s');
            $errorMessage = $errorData['message'];
            $errorTrace = $errorData['trace'];

            $message = $f3->get('error_email_body_template');
            $message = sprintf($message, $currentTime, $errorMessage, $errorTrace);

            \Utils\Mailer::send($toName, $toAddress, $subject, $message);
        }

        $db = $f3->get('API_DATABASE');
        if ($db) {
            $errorData['sql_log'] = $db->log();
            $logModel = new \Models\Log();
            $logModel->add($errorData);

            \Utils\Logger::log('SQL', $errorData['sql_log']);
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
         * @param $f3
         */
        return function (\Base $f3): void {
            $hive = $f3->hive();
            $isAjax = $hive['AJAX'];

            $errorData = self::getErrorDetails($f3);
            self::saveErrorInformation($f3, $errorData);

            if ($errorData['code'] === 403 && $isAjax) {
                echo self::getAjaxErrorMessage($errorData);

                return;
            }

            if ($errorData['code'] === 403 && !$isAjax) {
                $f3->reroute('/logout');

                return;
            }

            // Add handling 404 error
            if ($errorData['code'] === 404) {
            }

            if ($isAjax) {
                echo self::getAjaxErrorMessage($errorData);

                return;
            }

            $response = new \Views\Frontend();
            $pageController = new \Controllers\Pages\Error();

            $errorData['message'] = 'ERROR_' . $errorData['code'];
            unset($errorData['trace']);
            $pageParams = $pageController->getPageParams($errorData);

            $response->data = $pageParams;
            echo $response->render();
        };
    }

    public static function getCronErrorHandler(): callable {
        return function (\Base $f3): void {
            $errorData = self::getErrorDetails($f3);
            self::saveErrorInformation($f3, $errorData);
        };
    }

    public static function exceptionErrorHandler(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);

        return true;
    }
}
