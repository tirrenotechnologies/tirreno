<?php

namespace Tirreno\Rules\Core;

class D05 extends \Tirreno\Assets\Rule {
    public const NAME = 'Rare OS device';
    public const DESCRIPTION = 'User operates device with uncommon OS.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_rare_os']->equalTo(true),
        );
    }
}
