<?php

namespace Controllers\Admin\Rules\Set;

class D03 extends BaseRule {
    public const NAME = 'Device is bot';
    public const DESCRIPTION = 'The user may be using a device with a user agent that is identified as a bot.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $params['eup_has_bot_devices'] = in_array('bot', $params['eup_device']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_has_bot_devices']->equalTo(true),
        );
    }
}
