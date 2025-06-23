<?php

namespace Controllers\Admin\Rules\Set;

class B03 extends BaseRule {
    public const NAME = 'User has changed an email';
    public const DESCRIPTION = 'The user has changed their email.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_email_changed']->equalTo(true),
        );
    }
}
