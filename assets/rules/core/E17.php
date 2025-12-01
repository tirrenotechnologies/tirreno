<?php

namespace CoreRules;

class E17 extends \Assets\Rule {
    public const NAME = 'Free email and spam';
    public const DESCRIPTION = 'Email appears in spam lists and registered by free provider. Increased risk of spamming.';
    public const ATTRIBUTES = ['email', 'domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_domain_free_email_provider']->equalTo(true),
            $this->rb['le_email_in_blockemails']->equalTo(true),
        );
    }
}
