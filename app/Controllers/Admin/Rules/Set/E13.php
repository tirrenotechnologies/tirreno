<?php

namespace Controllers\Admin\Rules\Set;

class E13 extends BaseRule {
    public const NAME = 'New domain';
    public const DESCRIPTION = 'Domain name was registered recently, which is rare for average users.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->rb['ld_days_since_domain_creation']->lessThan(90),
        );
    }
}
