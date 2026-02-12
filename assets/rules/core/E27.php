<?php

namespace Tirreno\Rules\Core;

class E27 extends \Tirreno\Assets\Rule {
    public const NAME = 'Email breaches';
    public const DESCRIPTION = 'Email appears in data breaches.';
    public const ATTRIBUTES = ['email'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            //$this->rb['le_has_no_profiles']->equalTo(false),
            // do not trigger if le_data_breach is null,
            $this->rb['le_data_breach']->equalTo(true),
        );
    }
}
