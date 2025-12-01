<?php

namespace CoreRules;

class I12 extends \Assets\Rule {
    public const NAME = 'IP belongs to LAN';
    public const DESCRIPTION = 'IP address belongs to local access network.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_lan']->equalTo(true),
        );
    }
}
