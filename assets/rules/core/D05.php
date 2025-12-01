<?php

namespace CoreRules;

class D05 extends \Assets\Rule {
    public const NAME = 'Rare OS device';
    public const DESCRIPTION = 'User operates device with uncommon OS.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_rare_os']->equalTo(true),
        );
    }
}
