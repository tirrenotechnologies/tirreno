<?php

namespace Tirreno\Rules\Core;

class B06 extends \Tirreno\Assets\Rule {
    public const NAME = 'Potentially vulnerable URL';
    public const DESCRIPTION = 'The user made a request to suspicious URL.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_vulnerable_url']->equalTo(true),
        );
    }
}
