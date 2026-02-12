<?php

namespace Tirreno\Rules\Core;

class B03 extends \Tirreno\Assets\Rule {
    public const NAME = 'User has changed an email';
    public const DESCRIPTION = 'The user has changed their email.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_email_changed']->equalTo(true),
        );
    }
}
