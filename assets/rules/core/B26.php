<?php

namespace CoreRules;

class B26 extends \Assets\Rule {
    public const NAME = 'Single event sessions';
    public const DESCRIPTION = 'User had sessions with only one event.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_single_event']->equalTo(true),
        );
    }
}
