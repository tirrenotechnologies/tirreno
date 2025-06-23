<?php

namespace Controllers\Admin\Rules\Set;

class D02 extends BaseRule {
    public const NAME = 'Device is Linux';
    public const DESCRIPTION = 'Linux OS is not used by avarage users, increased risk of crawler bot.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $params['eup_has_linux_system'] = in_array('GNU/Linux', $params['eup_os_name']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_linux_system']->equalTo(true),
        );
    }
}
