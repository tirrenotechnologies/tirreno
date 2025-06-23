<?php

namespace Controllers\Admin\Rules\Set;

class D05 extends BaseRule {
    public const NAME = 'Rare OS device';
    public const DESCRIPTION = 'User operates device with uncommon OS.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_rare_os']->equalTo(true),
        );
    }
}
