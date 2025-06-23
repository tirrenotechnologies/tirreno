<?php

namespace Controllers\Admin\Rules\Set;

class E24 extends BaseRule {
    public const NAME = 'Government domain (.gov)';
    public const DESCRIPTION = 'Email belongs to government domain.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $emailHasGov = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '.gov')) {
                $emailHasGov = true;
                break;
            }
        }

        $params['ee_has_gov'] = $emailHasGov;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ee_has_gov']->equalTo(true),
        );
    }
}
