<?php

namespace Controllers\Admin\Rules\Set;

class B10 extends BaseRule {
    public const NAME = 'Dormant account (1 year)';
    public const DESCRIPTION = 'The account has been inactive for a year.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_last_visit']->greaterThan(365),
        );
    }
}
