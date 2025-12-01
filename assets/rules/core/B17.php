<?php

namespace CoreRules;

class B17 extends \Assets\Rule {
    public const NAME = 'Single country';
    public const DESCRIPTION = 'IP addresses are located in a single country.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_country']->equalTo(1),
        );
    }
}
