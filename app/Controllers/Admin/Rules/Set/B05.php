<?php

namespace Controllers\Admin\Rules\Set;

class B05 extends BaseRule {
    public const NAME = 'Multiple 4xx errors';
    public const DESCRIPTION = 'The user made multiple requests which cannot be fulfilled.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_multiple_4xx_http']->greaterThanOrEqualTo(\Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_404_CODES')),
        );
    }
}
