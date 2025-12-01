<?php

namespace CoreRules;

class B12 extends \Assets\Rule {
    public const NAME = 'New account (1 week)';
    public const DESCRIPTION = 'The account has been created this week.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_account_creation']->notEqualTo(-1),
            $this->rb['ea_days_since_account_creation']->lessThan(7),
            $this->rb['ea_days_since_account_creation']->greaterThanOrEqualTo(1),
        );
    }
}
