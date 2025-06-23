<?php

namespace Controllers\Admin\Rules\Set;

class E16 extends BaseRule {
    public const NAME = 'Domain appears in spam lists';
    public const DESCRIPTION = 'Email appears in spam lists, so the user may have spammed before.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_from_blockdomains']->equalTo(true),
        );
    }
}
