<?php

namespace Controllers\Admin\Rules\Set;

class E30 extends BaseRule {
    public const NAME = 'Domain with average rank';
    public const DESCRIPTION = 'Email domain has tranco rank between 100.000 and 4.000.000';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_tranco_rank']->greaterThan(100000),
            $this->rb['ld_tranco_rank']->lessThan(4000000),
            $this->rb['ld_domain_free_email_provider']->notEqualTo(true),
        );
    }
}
