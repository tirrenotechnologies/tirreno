<?php

namespace Controllers\Admin\Rules\Set;

class B08 extends BaseRule {
    public const NAME = 'Dormant account (30 days)';
    public const DESCRIPTION = 'The account has been inactive for 30 days.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_last_visit']->greaterThan(30),
        );
    }
}
