<?php

namespace Controllers\Admin\Rules\Set;

class I06 extends BaseRule {
    public const NAME = 'IP belongs to datacenter';
    public const DESCRIPTION = 'The user is utilizing an ISP datacenter, which highly suggests the use of a VPN, script, or privacy software.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_data_center'] = in_array(true, $params['eip_data_center']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_data_center']->equalTo(true),
        );
    }
}
