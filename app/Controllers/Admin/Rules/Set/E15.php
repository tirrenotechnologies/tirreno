<?php

namespace Controllers\Admin\Rules\Set;

class E15 extends BaseRule {
    public const NAME = 'No breaches for email';
    public const DESCRIPTION = 'The email was not involved in any data breaches, which could suggest it\'s a newly created or less frequently used mailbox.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_has_no_data_breaches']->equalTo(true),
        );
    }
}
