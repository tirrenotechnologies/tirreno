<?php

namespace Controllers\Admin\Rules\Set;

class B23 extends BaseRule {
    public const NAME = 'User\'s full name contains space or hyphen';
    public const DESCRIPTION = 'Full name contains space or hyphen, which is a rare behaviour for regular users.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_fullname_has_spaces_hyphens']->equalTo(true),
        );
    }
}
