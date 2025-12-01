<?php

namespace CoreRules;

class I06 extends \Assets\Rule {
    public const NAME = 'IP belongs to datacenter';
    public const DESCRIPTION = 'The user is utilizing an ISP datacenter, which highly suggests the use of a VPN, script, or privacy software.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_data_center']->equalTo(true),
        );
    }
}
