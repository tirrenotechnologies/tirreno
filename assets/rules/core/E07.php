<?php

namespace CoreRules;

class E07 extends \Assets\Rule {
    public const NAME = 'Long email username';
    public const DESCRIPTION = 'The email\'s username exceeds the average length, which could suggest it\'s a temporary email address.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_with_long_local_part_length']->equalTo(true),
        );
    }
}
