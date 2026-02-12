<?php

namespace Tirreno\Rules\Core;

class I04 extends \Tirreno\Assets\Rule {
    public const NAME = 'Shared IP';
    public const DESCRIPTION = 'Multiple users detected on the same IP address. High risk of multi-accounting.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_shared']->greaterThan(1),
        );
    }
}
