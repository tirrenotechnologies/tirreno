<?php

namespace Controllers\Admin\Rules\Set;

class A04 extends BaseRule {
    public const NAME = 'New device and new subnet';
    public const DESCRIPTION = 'User logged in with new device from new subnet, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $eventNewDeviceNewCidr = false;
        if ($params['eup_device_count'] > 1 && $params['eip_unique_cidrs'] > 1) {
            foreach ($params['event_device'] as $idx => $device) {
                if (\Utils\Rules::eventDeviceIsNew($params, $idx) && \Utils\Rules::cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $eventNewDeviceNewCidr = true;
                    break;
                }
            }
        }

        $params['event_new_device_and_new_cidr'] = $eventNewDeviceNewCidr;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_new_device_and_new_cidr']->equalTo(true),
        );
    }
}
