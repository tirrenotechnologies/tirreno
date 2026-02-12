<?php

namespace Tirreno\Rules\Core;

class D06 extends \Tirreno\Assets\Rule {
    public const NAME = 'Multiple devices per user';
    public const DESCRIPTION = 'User accesses the account using multiple devices. Account may be used by different people.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_device']->greaterThan(4),
        );
    }
}
