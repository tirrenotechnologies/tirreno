<?php

namespace Controllers\Admin\Rules\Set;

class E26 extends BaseRule {
    public const NAME = 'iCloud mailbox';
    public const DESCRIPTION = 'Email belongs to Apple domains icloud.com, me.com or mac.com.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $emailHasApple = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '@icloud.com') || str_ends_with($email, '@me.com') || str_ends_with($email, '@mac.com')) {
                $emailHasApple = true;
                break;
            }
        }

        $params['ee_has_apple'] = $emailHasApple;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ee_has_apple']->equalTo(true),
        );
    }
}
