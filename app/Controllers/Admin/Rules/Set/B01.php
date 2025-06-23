<?php

namespace Controllers\Admin\Rules\Set;

class B01 extends BaseRule {
    public const NAME = 'Multiple countries';
    public const DESCRIPTION = 'IP addresses are located in diverse countries, which is a rare behaviour for regular users.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_country']->greaterThan(3),
        );
    }
}
