<?php

namespace CoreRules;

class D08 extends \Assets\Rule {
    public const NAME = 'Two or more phone devices';
    public const DESCRIPTION = 'User accesses the account using numerous phone devices, which is not standard behaviour for regular users. Account may be shared between different people.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $smartphoneCount = 0;
        foreach ($params['eup_device'] as $device) {
            if ($device === 'smartphone') {
                ++$smartphoneCount;
            }
        }

        $params['eup_phone_devices_count'] = $smartphoneCount;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_phone_devices_count']->greaterThan(1),
        );
    }
}
