<?php

namespace Tirreno\Rules\Core;

class D12 extends \Tirreno\Assets\Rule {
    public const NAME = 'Empty browser language';
    public const DESCRIPTION = 'The user made a request with empty browser language.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eup_empty_lang']->equalTo(true),
        );
    }
}
