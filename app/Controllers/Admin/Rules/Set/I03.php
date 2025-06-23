<?php

namespace Controllers\Admin\Rules\Set;

class I03 extends BaseRule {
    public const NAME = 'IP appears in spam list';
    public const DESCRIPTION = 'User may have exhibited unwanted activity before at other web services.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_blocklist'] = in_array(true, $params['eip_blocklist']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_blocklist']->equalTo(true),
        );
    }
}
