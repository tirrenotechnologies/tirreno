<?php

namespace Tirreno\Rules\Core;

class B25 extends \Tirreno\Assets\Rule {
    public const NAME = 'Unauthorized request';
    public const DESCRIPTION = 'The user made a successful request without authorization.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_userid']->equalTo(\Tirreno\Utils\Constants::get()->UNAUTHORIZED_USERID),
            $this->rb['event_2xx_http']->equalTo(true),
        );
    }
}
