<?php

namespace Controllers\Admin\Rules\Set;

class A08 extends BaseRule {
    public const NAME = 'Browser language changed';
    public const DESCRIPTION = 'User accessed the account with new browser language, which can be a sign of account takeover.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $newBrowserLanguage = false;
        // $item ?? '' because `lang` can be null, which we should process as an empty string
        $langs = array_map(function ($item) {
            return strtoupper(explode('-', preg_replace('/;.*$/', '', trim(explode(',', $item ?? '')[0])))[0]);
        }, $params['eup_lang']);

        $langCount = array_count_values($langs);

        if ($params['eup_device_count'] > 1 && count($langCount) > 1) {
            foreach ($params['event_device'] as $idx => $deviceId) {
                if (\Utils\Rules::eventDeviceIsNew($params, $idx)) {
                    $innerId = array_search($deviceId, $params['eup_device_id']);
                    $lang = strtoupper(explode('-', preg_replace('/;.*$/', '', trim(explode(',', $params['eup_lang'][$innerId] ?? '')[0])))[0]);
                    if ($langCount[$lang] === 1) {
                        $newBrowserLanguage = true;
                        break;
                    }
                }
            }
        }

        $params['event_new_browser_language'] = $newBrowserLanguage;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_new_browser_language']->equalTo(true),
        );
    }
}
