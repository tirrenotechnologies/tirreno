<?php

namespace Controllers\Admin\Rules\Set;

class E28 extends BaseRule {
    public const NAME = 'No digits in email';
    public const DESCRIPTION = 'The email address does not include digits.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_no_digits']->equalTo(true),
        );
    }
}
