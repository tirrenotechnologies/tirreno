<?php

namespace Tirreno\Rules\Core;

class D11 extends \Tirreno\Assets\Rule {
    public const NAME = 'Empty User-Agent';
    public const DESCRIPTION = 'The user made a request with empty User-Agent.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eup_empty_ua']->equalTo(true),
        );
    }
}
