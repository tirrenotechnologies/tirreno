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

class HotDebug {
    public static function e($value, bool $shouldExit = false): void {
        $html = '
            <style type="text/css">TABLE{border-collapse: collapse;} TH {text-align: right; background-color: lightgrey;} TH, TD{ border: 1px solid #000; padding: 3px;}</style>
            <table>
             <caption><b>Debug</b></caption>
            <tr>
                <th>File:</th>
                <td>%s</td>
            </tr>
            <tr>
                <th>Line:</th>
                <td>%s</td>
            </tr>
            <tr>
                <th>Message:</th>
                <td><pre>%s</pre></td>
            </tr>
        </table>';

        $bt = debug_backtrace();

        $caller = $bt[2]; //bt[0] - is \use \Traits\\Debug

        $isVariableRecursive = self::isRecursive($value);
        $data = $isVariableRecursive ? var_dump($value) : var_export($value, true);

        $html = sprintf($html, $caller['file'], $caller['line'], $data);

        echo $html;

        if ($shouldExit) {
            exit;
        }
    }

    //https://stackoverflow.com/questions/17181375/test-if-variable-contains-circular-references
    private static function isRecursive($array): bool {
        $dump = print_r($array, true);
        return strpos($dump, '*RECURSION*') !== false;
    }
}
