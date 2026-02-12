<?php

namespace Tirreno\Rules\Core;

class B18 extends \Tirreno\Assets\Rule {
    public const NAME = 'HEAD request';
    public const DESCRIPTION = 'HTTP request HEAD method is oftenly used by bots.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_http_method_head']->equalTo(true),
        );
    }
}
