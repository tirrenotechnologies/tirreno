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

namespace Tirreno\Core\Services;

class Utils extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Utils\\%s';
    // skipping assets, http
    protected array $objectsMap = [
        'access'            => 'Access',
        'apiKeys'           => 'ApiKeys',
        'responseFormats'   => 'ApiResponseFormats',    //!
        'constants'         => 'Constants',
        'conversion'        => 'Conversion',
        'cron'              => 'Cron',
        'database'          => 'Database',
        'dateRange'         => 'DateRange',
        'dictManager'       => 'DictManager',
        'elapsedDate'       => 'ElapsedDate',
        'enrichment'        => 'Enrichment',
        'errorCodes'        => 'ErrorCodes',
        'errorHandler'      => 'ErrorHandler',
        'logger'            => 'Logger',
        'mailer'            => 'Mailer',
        'network'           => 'Network',
        'operatorAccess'    => 'OperatorAccess',
        'render'            => 'Render',
        'router'            => 'Router',
        'routes'            => 'Routes',
        'rules'             => 'Rules',
        'sort'              => 'Sort',
        'systemMessages'    => 'SystemMessages',
        'timezones'         => 'Timezones',
        'updates'           => 'Updates',
        'validators'        => 'Validators',
        'variables'         => 'Variables',
        'versionControl'    => 'VersionControl',

        'httpClient'        => 'Http\\HttpClient',
    ];

    protected function createObject(string $name, string $className, bool $getFullClass): object {
        if ($name === 'constants') {
            return new \Tirreno\Core\Services\StaticProxyConstants($this->getClassName($className, $getFullClass));
        }

        return new \Tirreno\Core\Services\StaticProxy($this->getClassName($className, $getFullClass));
    }

    public function nowForCurrentOperator(): string {
        $time = $this->nowUtc();
        tirreno('utils')->timezones->localizeForActiveOperator($time);

        return $time;
    }

    public function nowUtc(): string {
        return gmdate(tirreno('utils')->timezones->FORMAT);
    }
}
