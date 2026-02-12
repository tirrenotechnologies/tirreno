<?php

namespace Tirreno\Rules\Core;

class I08 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP belongs to Starlink';
    public const DESCRIPTION = 'IP address belongs to SpaceX satellite network.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_starlink']->equalTo(true),
        );
    }
}
