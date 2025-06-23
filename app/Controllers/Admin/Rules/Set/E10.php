<?php

namespace Controllers\Admin\Rules\Set;

class E10 extends BaseRule {
    public const NAME = 'The website is unavailable';
    public const DESCRIPTION = 'Domain\'s website seems to be inactive, which could be a sign of fake mailbox.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_website_is_disabled']->equalTo(true),
            $this->rb['ld_domain_free_email_provider']->notEqualTo(true),
        );
    }
}
