<?php

namespace Controllers\Admin\Rules\Set;

class P03 extends BaseRule {
    public const NAME = 'Shared phone number';
    public const DESCRIPTION = 'User provided a phone number shared with another user.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ep_shared_phone']->equalTo(true),
        );
    }
}
