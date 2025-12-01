<?php

namespace CoreRules;

class B25 extends \Assets\Rule {
    public const NAME = 'Unauthorized request';
    public const DESCRIPTION = 'The user made a successful request without authorization.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_userid']->equalTo(\Utils\Constants::get('UNAUTHORIZED_USERID')),
            $this->rb['event_2xx_http']->equalTo(true),
        );
    }
}
