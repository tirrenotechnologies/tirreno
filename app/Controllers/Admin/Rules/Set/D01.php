<?php

namespace Controllers\Admin\Rules\Set;

class D01 extends BaseRule {
    public const NAME = 'Device is unknown';
    public const DESCRIPTION = 'User has manipulated the device information, so it is not recognized.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $params['eup_has_unknown_devices'] = in_array(null, $params['eup_device']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_unknown_devices']->equalTo(true),
        );
    }
}
