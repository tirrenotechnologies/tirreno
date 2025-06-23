<?php

namespace Controllers\Admin\Rules\Set;

class R01 extends BaseRule {
    public const NAME = 'IP in blacklist';
    public const DESCRIPTION = 'This IP address appears in the blacklist.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_fraud']->equalTo(true),
        );
    }
}
