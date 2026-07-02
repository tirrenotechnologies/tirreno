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

class Assets extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Utils\\Assets\\%s';
    // skipping assets, http
    protected array $objectsMap = [
        'fileExtensionsList'    => 'Lists\\FileExtensions',
        'emailList'             => 'Lists\\Email',
        'urlList'               => 'Lists\\Url',
        'userAgentList'         => 'Lists\\UserAgent',
        'asnList'               => 'Lists\\Asn',
        'aiBotList'             => 'Lists\\AiBot',

        'uiConstants'           => 'ClientConstantsClass',
        'serverConstants'       => 'ServerConstantsClass',
        'context'               => 'ContextClass',
        'pages'                 => 'PagesClasses',
        'rules'                 => 'RulesClasses',
        'rulesPresets'          => 'RulesPresets',
    ];

    protected function createObject(string $name, string $className, bool $getFullClass): object {
        return new \Tirreno\Core\Services\StaticProxy($this->getClassName($className, $getFullClass));
    }
}
