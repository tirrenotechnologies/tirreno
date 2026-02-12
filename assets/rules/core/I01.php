<?php

namespace Tirreno\Rules\Core;

class I01 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP belongs to TOR';
    public const DESCRIPTION = 'IP address is assigned to The Onion Router network. Very few people use TOR, mainly used for anonymization and accessing censored resources.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_tor']->equalTo(true),
        );
    }
}
