<?php

namespace CoreRules;

class I02 extends \Assets\Rule {
    public const NAME = 'IP hosting domain';
    public const DESCRIPTION = 'Higher risk of crawler bot. Such IP addresses are used only for hosting and are not provided to regular users by ISP.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_domains_count_len']->greaterThan(0),
        );
    }
}
