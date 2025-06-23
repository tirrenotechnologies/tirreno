<?php

namespace Controllers\Admin\Rules\Set;

class B22 extends BaseRule {
    public const NAME = 'Multiple IP addresses in one session';
    public const DESCRIPTION = 'User\'s IP address was changed in less than 30 minutes.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_ip']->equalTo(true),
        );
    }
}
