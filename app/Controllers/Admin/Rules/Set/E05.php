<?php

namespace Controllers\Admin\Rules\Set;

class E05 extends BaseRule {
    public const NAME = 'Special characters in email';
    public const DESCRIPTION = 'The email address features an unusually high number of special characters, which is atypical for standard email addresses.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['le_email_has_consec_s_chars']->equalTo(true),
        );
    }
}
