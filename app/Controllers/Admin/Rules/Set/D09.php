<?php

namespace Controllers\Admin\Rules\Set;

class D09 extends BaseRule {
    public const NAME = 'Old browser';
    public const DESCRIPTION = 'User accesses the account using an old versioned browser.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $minVersion = null;
        $browserVersion = '';
        $oldBrowser = false;
        $iters = count($params['eup_browser_name']);

        for ($i = 0; $i < $iters; ++$i) {
            $minVersion = \Utils\Constants::get('RULE_REGULAR_BROWSER_NAMES')[$params['eup_browser_name'][$i]] ?? null;
            if ($minVersion !== null) {
                $browserVersion = explode('.', $params['eup_browser_version'][$i] ?? '')[0];
                if (ctype_digit($browserVersion) && intval($browserVersion) < $minVersion) {
                    $oldBrowser = true;
                    break;
                }
            }
        }

        $params['eup_old_browser'] = $oldBrowser;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_old_browser']->equalTo(true),
        );
    }
}
