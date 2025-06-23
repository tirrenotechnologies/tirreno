<?php

namespace Controllers\Admin\Rules\Set;

class B02 extends BaseRule {
    public const NAME = 'User has changed a password';
    public const DESCRIPTION = 'The user has changed their password.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_password_changed']->equalTo(true),
        );
    }
}
