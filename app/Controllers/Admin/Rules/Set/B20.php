<?php

namespace Controllers\Admin\Rules\Set;

class B20 extends BaseRule {
    public const NAME = 'Multiple countries in one session';
    public const DESCRIPTION = 'User\'s country was changed in less than 30 minutes.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_country']->equalTo(true),
        );
    }
}
