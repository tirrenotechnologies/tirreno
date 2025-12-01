<?php

namespace CoreRules;

class B16 extends \Assets\Rule {
    public const NAME = 'Aged account (>180 days)';
    public const DESCRIPTION = 'The account has been created over 180 days ago.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_account_creation']->notEqualTo(-1),
            $this->rb['ea_days_since_account_creation']->greaterThanOrEqualTo(180),
        );
    }
}
