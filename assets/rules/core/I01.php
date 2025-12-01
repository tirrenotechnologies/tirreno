<?php

namespace CoreRules;

class I01 extends \Assets\Rule {
    public const NAME = 'IP belongs to TOR';
    public const DESCRIPTION = 'IP address is assigned to The Onion Router network. Very few people use TOR, mainly used for anonymization and accessing censored resources.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_tor']->equalTo(true),
        );
    }
}
