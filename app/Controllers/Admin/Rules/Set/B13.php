<?php

namespace Controllers\Admin\Rules\Set;

class B13 extends BaseRule {
    public const NAME = 'New account (1 month)';
    public const DESCRIPTION = 'The account has been created this month.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_account_creation']->notEqualTo(-1),
            $this->rb['ea_days_since_account_creation']->lessThan(30),
            $this->rb['ea_days_since_account_creation']->greaterThanOrEqualTo(7),
        );
    }
}
