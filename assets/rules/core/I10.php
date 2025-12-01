<?php

namespace CoreRules;

class I10 extends \Assets\Rule {
    public const NAME = 'Only residential IPs';
    public const DESCRIPTION = 'User uses only residential IP addresses.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_only_residential']->equalTo(true),
        );
    }
}
