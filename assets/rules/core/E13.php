<?php

namespace Tirreno\Rules\Core;

class E13 extends \Tirreno\Assets\Rule {
    public const NAME = 'New domain';
    public const DESCRIPTION = 'Domain name was registered recently, which is rare for average users.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->rb['ld_days_since_domain_creation']->lessThan(90),
        );
    }
}
