<?php

namespace CoreRules;

class I03 extends \Assets\Rule {
    public const NAME = 'IP appears in spam list';
    public const DESCRIPTION = 'User may have exhibited unwanted activity before at other web services.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_blocklist']->equalTo(true),
        );
    }
}
