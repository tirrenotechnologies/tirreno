<?php

namespace Controllers\Admin\Rules\Set;

class B07 extends BaseRule {
    public const NAME = 'User\'s full name contains digits';
    public const DESCRIPTION = 'Full name contains digits, which is a rare behaviour for regular users.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_fullname_has_numbers']->equalTo(true),
        );
    }
}
