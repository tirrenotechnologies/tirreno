<?php

namespace CoreRules;

class I08 extends \Assets\Rule {
    public const NAME = 'IP belongs to Starlink';
    public const DESCRIPTION = 'IP address belongs to SpaceX satellite network.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_starlink']->equalTo(true),
        );
    }
}
