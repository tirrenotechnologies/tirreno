<?php

namespace CoreRules;

class D04 extends \Assets\Rule {
    public const NAME = 'Rare browser device';
    public const DESCRIPTION = 'User operates device with uncommon browser.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_rare_browser']->equalTo(true),
        );
    }
}
