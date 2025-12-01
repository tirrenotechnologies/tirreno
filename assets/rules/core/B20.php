<?php

namespace CoreRules;

class B20 extends \Assets\Rule {
    public const NAME = 'Multiple countries in one session';
    public const DESCRIPTION = 'User\'s country was changed in less than 30 minutes.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_country']->equalTo(true),
        );
    }
}
