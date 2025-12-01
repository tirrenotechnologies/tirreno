<?php

namespace CoreRules;

class B03 extends \Assets\Rule {
    public const NAME = 'User has changed an email';
    public const DESCRIPTION = 'The user has changed their email.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_email_changed']->equalTo(true),
        );
    }
}
