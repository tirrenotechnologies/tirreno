<?php

namespace Tirreno\Rules\Core;

class B23 extends \Tirreno\Assets\Rule {
    public const NAME = 'User\'s full name contains space or hyphen';
    public const DESCRIPTION = 'Full name contains space or hyphen, which is a rare behaviour for regular users.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ea_fullname_has_spaces_hyphens']->equalTo(true),
        );
    }
}
