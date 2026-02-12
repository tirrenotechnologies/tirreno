<?php

namespace Tirreno\Rules\Core;

class B22 extends \Tirreno\Assets\Rule {
    public const NAME = 'Multiple IP addresses in one session';
    public const DESCRIPTION = 'User\'s IP address was changed in less than 30 minutes.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_ip']->equalTo(true),
        );
    }
}
