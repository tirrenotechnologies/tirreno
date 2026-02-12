<?php

namespace Tirreno\Rules\Core;

class A03 extends \Tirreno\Assets\Rule {
    public const NAME = 'New device and new country';
    public const DESCRIPTION = 'User logged in with new device from new location, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $eventNewDeviceNewCountry = false;
        if ($params['eup_device_count'] > 1 && count(array_unique($params['eip_country_id'])) > 1) {
            foreach (array_keys($params['event_device']) as $idx) {
                if (\Tirreno\Utils\Rules::eventDeviceIsNew($params, $idx) && \Tirreno\Utils\Rules::countryIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $eventNewDeviceNewCountry = true;
                    break;
                }
            }
        }

        $params['event_new_device_and_new_country'] = $eventNewDeviceNewCountry;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_new_device_and_new_country']->equalTo(true),
        );
    }
}
