<?php

namespace Controllers\Admin\Rules\Set;

class E02 extends BaseRule {
    public const NAME = 'New domain and no breaches';
    public const DESCRIPTION = 'Email belongs to recently created domain name and it doesn\'t appear in data breaches. Increased risk due to lack of authenticity.';
    public const ATTRIBUTES = ['email', 'domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->rb['ld_days_since_domain_creation']->lessThan(30),
            //$this->rb['le_has_no_profiles']->equalTo(true),
            $this->rb['le_has_no_data_breaches']->equalTo(true),
        );
    }
}
