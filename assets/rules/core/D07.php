<?php

namespace CoreRules;

class D07 extends \Assets\Rule {
    public const NAME = 'Several desktop devices';
    public const DESCRIPTION = 'User accesses the account using different OS desktop devices. Account may be shared between different people.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $desktopDevicesWithDifferentOS = false;
        $firstDesktopOS = '';
        for ($i = 0; $i < $params['eup_device_count']; ++$i) {
            if ($params['eup_device'][$i] === 'desktop') {
                if ($firstDesktopOS === '') {
                    $firstDesktopOS = $params['eup_os_name'][$i];
                } elseif ($firstDesktopOS !== $params['eup_os_name'][$i]) {
                    $desktopDevicesWithDifferentOS = true;
                    break;
                }
            }
        }

        $params['eup_desktop_devices_with_different_os'] = $desktopDevicesWithDifferentOS;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eup_desktop_devices_with_different_os']->equalTo(true),
        );
    }
}
