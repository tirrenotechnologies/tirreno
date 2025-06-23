<?php

namespace Controllers\Admin\Rules\Set;

class B18 extends BaseRule {
    public const NAME = 'HEAD request';
    public const DESCRIPTION = 'HTTP request HEAD method is oftenly used by bots.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_http_method_head']->equalTo(true),
        );
    }
}
