<?php

namespace Tirreno\Rules\Core;

class P03 extends \Tirreno\Assets\Rule {
    public const NAME = 'Shared phone number';
    public const DESCRIPTION = 'User provided a phone number shared with another user.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ep_shared_phone']->equalTo(true),
        );
    }
}
