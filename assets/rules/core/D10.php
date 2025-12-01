<?php

namespace CoreRules;

class D10 extends \Assets\Rule {
    public const NAME = 'Potentially vulnerable User-Agent';
    public const DESCRIPTION = 'The user made a request with potentially vulnerable User-Agent.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_vulnerable_ua']->equalTo(true),
        );
    }
}
