<?php

namespace Tirreno\Rules\Core;

class I05 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP belongs to commercial VPN';
    public const DESCRIPTION = 'User tries to hide their real location or bypass regional blocking.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_vpn']->equalTo(true),
        );
    }
}
