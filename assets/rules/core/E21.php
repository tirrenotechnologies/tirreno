<?php

namespace Tirreno\Rules\Core;

class E21 extends \Tirreno\Assets\Rule {
    public const NAME = 'No vowels in email';
    public const DESCRIPTION = 'Email username does not contain any vowels.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_vowels']->equalTo(false),
            $this->rb['le_local_part_len']->greaterThan(0),
        );
    }
}
