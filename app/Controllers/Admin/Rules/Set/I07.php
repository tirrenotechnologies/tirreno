<?php

namespace Controllers\Admin\Rules\Set;

class I07 extends BaseRule {
    public const NAME = 'IP belongs to Apple Relay';
    public const DESCRIPTION = 'IP address belongs to iCloud Private Relay, part of an iCloud+ subscription.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_relay'] = in_array(true, $params['eip_relay']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_relay']->equalTo(true),
        );
    }
}
