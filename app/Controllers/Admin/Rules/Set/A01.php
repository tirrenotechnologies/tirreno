<?php

namespace Controllers\Admin\Rules\Set;

class A01 extends BaseRule {
    public const NAME = 'Multiple login fail';
    public const DESCRIPTION = 'User failed to login multiple times in a short term, which can be a sign of account takeover.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $maximumAttempts = \Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_LOGIN_ATTEMPTS');
        $loginFail = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL');
        $windowSize = \Utils\Constants::get('RULE_LOGIN_ATTEMPTS_WINDOW');
        $tooManyLoginAttempts = false;
        $cnt = 0;
        $start = 0;
        $iters = count($params['event_type']);

        for ($end = 0; $end < $iters; ++$end) {
            if ($params['event_type'][$end] === $loginFail) {
                ++$cnt;
            }
            if ($end >= $windowSize - 1) {
                if ($cnt > $maximumAttempts) {
                    $tooManyLoginAttempts = true;
                    break;
                }
                if ($params['event_type'][$start] === $loginFail) {
                    --$cnt;
                }
                ++$start;
            }
        }

        $params['event_many_failed_login_attempts'] = $tooManyLoginAttempts;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_many_failed_login_attempts']->equalTo(true),
        );
    }
}
