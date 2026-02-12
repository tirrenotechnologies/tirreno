<?php

namespace Tirreno\Rules\Core;

class E22 extends \Tirreno\Assets\Rule {
    public const NAME = 'No consonants in email';
    public const DESCRIPTION = 'Email username does not contain any consonants.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_consonants']->equalTo(false),
            $this->rb['le_local_part_len']->greaterThan(0),
        );
    }
}
