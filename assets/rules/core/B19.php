<?php

namespace CoreRules;

class B19 extends \Assets\Rule {
    public const NAME = 'Night time requests';
    public const DESCRIPTION = 'User was active from midnight till 5 a. m.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_night_time']->equalTo(true),
        );
    }
}
