<?php

namespace Controllers\Admin\Rules\Set;

class E27 extends BaseRule {
    public const NAME = 'Breaches';
    public const DESCRIPTION = 'Email appears in data breaches.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            //$this->rb['le_has_no_profiles']->equalTo(false),
            // do not trigger if le_data_breach is null,
            $this->rb['le_data_breach']->equalTo(true),
        );
    }
}
