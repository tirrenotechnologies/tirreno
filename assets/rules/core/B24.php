<?php

namespace Tirreno\Rules\Core;

class B24 extends \Tirreno\Assets\Rule {
    public const NAME = 'Empty referer';
    public const DESCRIPTION = 'The user made a request without a referer.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_empty_referer']->equalTo(true),
        );
    }
}
