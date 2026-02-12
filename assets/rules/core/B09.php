<?php

namespace Tirreno\Rules\Core;

class B09 extends \Tirreno\Assets\Rule {
    public const NAME = 'Dormant account (90 days)';
    public const DESCRIPTION = 'The account has been inactive for 90 days.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_days_since_last_visit']->greaterThan(90),
        );
    }
}
