<?php

namespace Tirreno\Rules\Core;

class A04 extends \Tirreno\Assets\Rule {
    public const NAME = 'New device and new subnet';
    public const DESCRIPTION = 'User logged in with new device from new subnet, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $eventNewDeviceNewCidr = false;
        if ($params['eup_device_count'] > 1 && $params['eip_unique_cidrs'] > 1) {
            foreach (array_keys($params['event_device']) as $idx) {
                if (\Tirreno\Utils\Rules::eventDeviceIsNew($params, $idx) && \Tirreno\Utils\Rules::cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $eventNewDeviceNewCidr = true;
                    break;
                }
            }
        }

        $params['event_new_device_and_new_cidr'] = $eventNewDeviceNewCidr;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_new_device_and_new_cidr']->equalTo(true),
        );
    }
}
