<?php

namespace Controllers\Admin\Rules\Set;

class D06 extends BaseRule {
    public const NAME = 'Multiple devices per user';
    public const DESCRIPTION = 'User accesses the account using multiple devices. Account may be used by different people.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_device']->greaterThan(4),
        );
    }
}
