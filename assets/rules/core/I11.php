<?php

namespace CoreRules;

class I11 extends \Assets\Rule {
    public const NAME = 'Single network';
    public const DESCRIPTION = 'IP addresses belong to one network.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_unique_cidrs']->equalTo(1),
        );
    }
}
