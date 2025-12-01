<?php

namespace CoreRules;

class E01 extends \Assets\Rule {
    public const NAME = 'Invalid email format';
    public const DESCRIPTION = 'Invalid email format. Should be \'username@domain.com\'.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_is_invalid']->equalTo(true),
        );
    }
}
