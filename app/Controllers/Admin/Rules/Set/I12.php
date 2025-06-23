<?php

namespace Controllers\Admin\Rules\Set;

class I12 extends BaseRule {
    public const NAME = 'IP belongs to LAN';
    public const DESCRIPTION = 'IP address belongs to local access network.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $isLan = false;
        $iters = count($params['eip_ip_id']);

        for ($i = 0; $i < $iters; ++$i) {
            // invalid ip or N/A isp should have `eip_data_center` === null
            if ($params['eip_cidr'][$i] === null && $params['eip_data_center'][$i] === false) {
                $isLan = true;
                break;
            }
        }

        $params['eip_lan'] = $isLan;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_lan']->equalTo(true),
        );
    }
}
