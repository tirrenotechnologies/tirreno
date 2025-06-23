<?php

namespace Controllers\Admin\Rules\Set;

class I04 extends BaseRule {
    public const NAME = 'Shared IP';
    public const DESCRIPTION = 'Multiple users detected on the same IP address. High risk of multi-accounting.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $arrWithPositiveSharedIps = array_filter($params['eip_shared'], function ($item) {
                    return $item > 1;
        });

        $params['eip_shared'] = count($arrWithPositiveSharedIps) > 0;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_shared']->equalTo(true),
        );
    }
}
