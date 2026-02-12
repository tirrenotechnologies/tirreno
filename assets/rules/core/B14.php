<?php

namespace Tirreno\Rules\Core;

class B14 extends \Tirreno\Assets\Rule {
    public const NAME = 'Aged account (>30 days)';
    public const DESCRIPTION = 'The account has been created over 30 days ago.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_account_creation']->notEqualTo(-1),
            $this->rb['ea_days_since_account_creation']->lessThan(90),
            $this->rb['ea_days_since_account_creation']->greaterThanOrEqualTo(30),
        );
    }
}
