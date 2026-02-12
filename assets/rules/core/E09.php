<?php

namespace Tirreno\Rules\Core;

class E09 extends \Tirreno\Assets\Rule {
    public const NAME = 'Free email provider';
    public const DESCRIPTION = 'Email belongs to free provider. These mailboxes are the easiest to create.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ld_domain_free_email_provider']->equalTo(true),
        );
    }
}
