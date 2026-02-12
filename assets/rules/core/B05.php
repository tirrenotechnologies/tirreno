<?php

namespace Tirreno\Rules\Core;

class B05 extends \Tirreno\Assets\Rule {
    public const NAME = 'Multiple 4xx errors';
    public const DESCRIPTION = 'The user made multiple requests which cannot be fulfilled.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_multiple_4xx_http']->greaterThanOrEqualTo(\Tirreno\Utils\Constants::get()->RULE_MAXIMUM_NUMBER_OF_404_CODES),
        );
    }
}
