<?php

namespace Tirreno\Rules\Core;

class I02 extends \Tirreno\Assets\Rule {
    public const NAME = 'IP hosting domain';
    public const DESCRIPTION = 'Higher risk of crawler bot. Such IP addresses are used only for hosting and are not provided to regular users by ISP.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_domains_count_len']->greaterThan(0),
        );
    }
}
