<?php

namespace Controllers\Admin\Rules\Set;

class B24 extends BaseRule {
    public const NAME = 'Empty referer';
    public const DESCRIPTION = 'The user made a request without a referer';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_empty_referer']->equalTo(true),
        );
    }
}
