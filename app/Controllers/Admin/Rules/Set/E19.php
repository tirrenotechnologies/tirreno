<?php

namespace Controllers\Admin\Rules\Set;

class E19 extends BaseRule {
    public const NAME = 'Multiple emails changed';
    public const DESCRIPTION = 'User has changed their email.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $params['ee_email_count'] = count($params['ee_email']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ee_email_count']->greaterThan(1),
        );
    }
}
