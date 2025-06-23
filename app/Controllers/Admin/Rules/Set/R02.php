<?php

namespace Controllers\Admin\Rules\Set;

class R02 extends BaseRule {
    public const NAME = 'Email in blacklist';
    public const DESCRIPTION = 'This email address appears in the blacklist.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_fraud_detected']->equalTo(true),
        );
    }
}
