<?php

namespace Controllers\Admin\Rules\Set;

class E29 extends BaseRule {
    public const NAME = 'Old breach (>3 years)';
    public const DESCRIPTION = 'The earliest data breach associated with the email appeared more than 3 years ago. Can be used as sign of aged email.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ee_days_since_first_breach']->greaterThan(365 * 3),
        );
    }
}
