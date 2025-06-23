<?php

namespace Controllers\Admin\Rules\Set;

class I08 extends BaseRule {
    public const NAME = 'IP belongs to Starlink';
    public const DESCRIPTION = 'IP address belongs to SpaceX satellite network.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_starlink'] = in_array(true, $params['eip_starlink']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_starlink']->equalTo(true),
        );
    }
}
