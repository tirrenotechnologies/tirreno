<?php

namespace Tirreno\Rules\Core;

class I13 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP belongs to suspicious ASN';
    public const DESCRIPTION = 'IP address belongs to ASN marked as suspicious.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_suspicious_asn']->equalTo(true),
        );
    }
}
