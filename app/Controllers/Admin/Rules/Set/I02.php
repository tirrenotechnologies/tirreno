<?php

namespace Controllers\Admin\Rules\Set;

class I02 extends BaseRule {
    public const NAME = 'IP hosting domain';
    public const DESCRIPTION = 'Higher risk of crawler bot. Such IP addresses are used only for hosting and are not provided to regular users by ISP.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $arrWithPositiveDomainsCounts = array_filter($params['eip_domains_count_len'], static function ($value): bool {
            return $value > 0;
        });

        $params['eip_domains_count_len'] = count($arrWithPositiveDomainsCounts) > 0;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_domains_count_len']->greaterThan(0),
        );
    }
}
