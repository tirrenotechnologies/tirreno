<?php

namespace CoreRules;

class E04 extends \Assets\Rule {
    public const NAME = 'Numeric email name';
    public const DESCRIPTION = 'The email\'s username consists entirely of numbers, which is uncommon for typical email addresses.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_has_numeric_only_local_part']->equalTo(true),
            $this->rb['le_local_part_len']->greaterThan(0),
        );
    }
}
