<?php

namespace Tirreno\Rules\Core;

class B07 extends \Tirreno\Assets\Rule {
    public const NAME = 'User\'s full name contains digits';
    public const DESCRIPTION = 'Full name contains digits, which is a rare behaviour for regular users.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_fullname_has_numbers']->equalTo(true),
        );
    }
}
