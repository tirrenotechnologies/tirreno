<?php

namespace Tirreno\Rules\Core;

class B19 extends \Tirreno\Assets\Rule {
    public const NAME = 'Night time requests';
    public const DESCRIPTION = 'User was active from midnight till 5 a. m.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_session_night_time']->equalTo(true),
        );
    }
}
