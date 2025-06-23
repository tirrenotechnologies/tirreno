<?php

namespace Controllers\Admin\Rules\Set;

class I05 extends BaseRule {
    public const NAME = 'IP belongs to commercial VPN';
    public const DESCRIPTION = 'User tries to hide their real location or bypass regional blocking.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_vpn'] = in_array(true, $params['eip_vpn']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_vpn']->equalTo(true),
        );
    }
}
