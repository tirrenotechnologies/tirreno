<?php

namespace Controllers\Admin\Rules\Set;

class B04 extends BaseRule {
    public const NAME = 'Multiple 5xx errors';
    public const DESCRIPTION = 'The user made multiple requests which evoked internal server error.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_multiple_5xx_http']->greaterThan(\Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_500_CODES')),
        );
    }
}
