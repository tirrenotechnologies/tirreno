<?php

namespace Tirreno\Rules\Core;

class E08 extends \Tirreno\Assets\Rule {
    public const NAME = 'Long domain name';
    public const DESCRIPTION = 'Email\'s domain name is too long. Long domain names are cheaply registered and rarely used for email addresses by regular users.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['le_with_long_domain_length']->equalTo(true),
        );
    }
}
