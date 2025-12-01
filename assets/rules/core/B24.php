<?php

namespace CoreRules;

class B24 extends \Assets\Rule {
    public const NAME = 'Empty referer';
    public const DESCRIPTION = 'The user made a request without a referer.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_empty_referer']->equalTo(true),
        );
    }
}
