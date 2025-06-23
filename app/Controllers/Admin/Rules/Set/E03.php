<?php

namespace Controllers\Admin\Rules\Set;

class E03 extends BaseRule {
    public const NAME = 'Suspicious words in email';
    public const DESCRIPTION = 'Email contains word parts that usually found in automatically generated mailboxes.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_has_suspicious_str']->equalTo(true),
        );
    }
}
