<?php

namespace Controllers\Admin\Rules\Set;

class E12 extends BaseRule {
    public const NAME = 'Free email and no breaches';
    public const DESCRIPTION = 'Email belongs to free provider and it doesn\'t appear in data breaches. It may be a sign of a throwaway mailbox.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_domain_free_email_provider']->equalTo(true),
            //$this->rb['le_has_no_profiles']->equalTo(true),
            $this->rb['le_has_no_data_breaches']->equalTo(true),
        );
    }
}
