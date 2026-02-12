<?php

namespace Tirreno\Rules\Core;

class E20 extends \Tirreno\Assets\Rule {
    public const NAME = 'Established domain (> 3 year old)';
    public const DESCRIPTION = 'Email belongs to long-established domain name registered at least 3 years ago.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->rb['ld_days_since_domain_creation']->greaterThan(365 * 3),
            $this->rb['ld_disposable_domains']->notEqualTo(true),
            $this->rb['ld_free_email_provider']->notEqualTo(true),
        );
    }
}
