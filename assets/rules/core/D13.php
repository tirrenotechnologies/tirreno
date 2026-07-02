<?php

namespace Tirreno\Rules\Core;

class D13 extends \Tirreno\Assets\Rule {
    public const NAME = 'Device is AI bot';
    public const DESCRIPTION = 'The user made a request via AI bot.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eup_ai_bot']->equalTo(true),
        );
    }
}
