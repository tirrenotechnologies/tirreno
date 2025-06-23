<?php

namespace Controllers\Admin\Rules\Set;

class B21 extends BaseRule {
    public const NAME = 'Multiple devices in one session';
    public const DESCRIPTION = 'User\'s device was changed in less than 30 minutes.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_session_multiple_device']->equalTo(true),
        );
    }
}
