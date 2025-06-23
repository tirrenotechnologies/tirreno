<?php

namespace Controllers\Admin\Rules\Set;

class I09 extends BaseRule {
    public const NAME = 'Numerous IPs';
    public const DESCRIPTION = 'User accesses the account with numerous IP addresses. This behavior occurs in less than one percent of desktop users.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_ip']->greaterThan(9),
        );
    }
}
