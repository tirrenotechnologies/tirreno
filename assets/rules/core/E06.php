<?php

namespace Tirreno\Rules\Core;

class E06 extends \Tirreno\Assets\Rule {
    public const NAME = 'Consecutive digits in email';
    public const DESCRIPTION = 'The email address includes at least two consecutive digits, which is a characteristic sometimes associated with temporary or fake email accounts.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_consec_nums']->equalTo(true),
            $this->rb['le_local_part_len']->greaterThan(0),
        );
    }
}
