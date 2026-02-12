<?php

namespace Tirreno\Rules\Core;

class B20 extends \Tirreno\Assets\Rule {
    public const NAME = 'Multiple countries in one session';
    public const DESCRIPTION = 'User\'s country was changed in less than 30 minutes.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_country']->equalTo(true),
        );
    }
}
