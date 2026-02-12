<?php

namespace Tirreno\Rules\Core;

class E23 extends \Tirreno\Assets\Rule {
    public const NAME = 'Educational domain (.edu)';
    public const DESCRIPTION = 'Email belongs to educational domain.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $emailHasEdu = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '.edu')) {
                $emailHasEdu = true;
                break;
            }
        }

        $params['ee_has_edu'] = $emailHasEdu;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['ee_has_edu']->equalTo(true),
        );
    }
}
