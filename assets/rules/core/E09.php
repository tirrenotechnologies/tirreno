<?php

namespace CoreRules;

class E09 extends \Assets\Rule {
    public const NAME = 'Free email provider';
    public const DESCRIPTION = 'Email belongs to free provider. These mailboxes are the easiest to create.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_domain_free_email_provider']->equalTo(true),
        );
    }
}
