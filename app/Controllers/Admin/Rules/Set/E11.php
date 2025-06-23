<?php

namespace Controllers\Admin\Rules\Set;

class E11 extends BaseRule {
    public const NAME = 'Disposable email';
    public const DESCRIPTION = 'Disposable email addresses are temporary email addresses that users can create and use for a short period. They might use create fake accounts.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_is_disposable']->equalTo(true),
        );
    }
}
