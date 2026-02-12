<?php

namespace Tirreno\Rules\Core;

class I12 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP belongs to LAN';
    public const DESCRIPTION = 'IP address belongs to local access network.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_lan']->equalTo(true),
        );
    }
}
