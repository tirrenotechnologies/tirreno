<?php

namespace Tirreno\Rules\Core;

class E28 extends \Tirreno\Assets\Rule {
    public const NAME = 'No digits in email';
    public const DESCRIPTION = 'The email address does not include digits.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_no_digits']->equalTo(true),
            $this->rb['le_local_part_len']->greaterThan(0),
        );
    }
}
