<?php

namespace CoreRules;

class B02 extends \Assets\Rule {
    public const NAME = 'User has changed a password';
    public const DESCRIPTION = 'The user has changed their password.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_password_changed']->equalTo(true),
        );
    }
}
