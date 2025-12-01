<?php

namespace CoreRules;

class I07 extends \Assets\Rule {
    public const NAME = 'IP belongs to Apple Relay';
    public const DESCRIPTION = 'IP address belongs to iCloud Private Relay, part of an iCloud+ subscription.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_relay']->equalTo(true),
        );
    }
}
