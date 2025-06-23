<?php

namespace Controllers\Admin\Rules\Set;

class B14 extends BaseRule {
    public const NAME = 'Aged account (>30 days)';
    public const DESCRIPTION = 'The account has been created over 30 days ago.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_account_creation']->notEqualTo(-1),
            $this->rb['ea_days_since_account_creation']->lessThan(90),
            $this->rb['ea_days_since_account_creation']->greaterThanOrEqualTo(30),
        );
    }
}
