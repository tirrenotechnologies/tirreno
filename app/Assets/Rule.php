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

namespace Tirreno\Assets;

abstract class Rule {
    protected \Ruler\RuleBuilder $rb;
    protected \Ruler\Context $context;
    protected array $params;
    protected \Ruler\Operator\LogicalOperator $condition;

    public string $uid;

    public function __construct(?\Ruler\RuleBuilder $rb = null, array $params = []) {
        $parts = explode('\\', get_class($this));
        $this->uid = end($parts);
        $this->rb = $rb ? $rb : (new \Ruler\RuleBuilder());
        $this->params = $params;
    }

    abstract protected function defineCondition(): \Ruler\Operator\LogicalOperator;

    protected function prepareParams(array $params): array {
        return $params;
    }

    public function execute(): bool {
        $this->context = $this->buildContext();
        $this->condition = $this->defineCondition();
        return $this->rb->create($this->condition)->evaluate($this->context);
    }

    private function buildContext(): \Ruler\Context {
        return new \Ruler\Context($this->prepareParams($this->params));
    }

    public function updateParams(array $params): void {
        $this->params = $params;
    }
}
