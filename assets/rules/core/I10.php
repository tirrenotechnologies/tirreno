<?php

namespace Tirreno\Rules\Core;

class I10 extends \Tirreno\Assets\Rule {
    public const NAME = 'Only residential IPs';
    public const DESCRIPTION = 'User uses only residential IP addresses.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_only_residential']->equalTo(true),
        );
    }
}
