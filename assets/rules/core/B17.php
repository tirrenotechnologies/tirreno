<?php

namespace Tirreno\Rules\Core;

class B17 extends \Tirreno\Assets\Rule {
    public const NAME = 'Single country';
    public const DESCRIPTION = 'IP addresses are located in a single country.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_country']->equalTo(1),
        );
    }
}
