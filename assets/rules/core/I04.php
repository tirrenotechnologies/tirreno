<?php

namespace CoreRules;

class I04 extends \Assets\Rule {
    public const NAME = 'Shared IP';
    public const DESCRIPTION = 'Multiple users detected on the same IP address. High risk of multi-accounting.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_shared']->greaterThan(1),
        );
    }
}
