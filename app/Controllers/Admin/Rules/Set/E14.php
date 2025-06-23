<?php

namespace Controllers\Admin\Rules\Set;

class E14 extends BaseRule {
    public const NAME = 'No MX record';
    public const DESCRIPTION = 'Email\'s domain name has no MX record, so domain is not able to have any mailboxes. It is a sign of fake mailbox.';
    public const ATTRIBUTES = ['domain'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ld_domain_without_mx_record']->equalTo(true),
        );
    }
}
