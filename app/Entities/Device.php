<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

namespace Tirreno\Entities;

class Device extends \Tirreno\Entities\Single {
    protected int $id;
    protected int $userId;
    protected ?string $lang;
    protected ?int $totalVisit;
    protected string $lastseen;
    protected string $created;
    protected string $updated;

    protected int $userAgentId;
    protected ?string $device;
    protected ?string $browserName;
    protected ?string $browserVersion;
    protected ?string $osName;
    protected ?string $osVersion;
    protected ?string $userAgent;
    protected ?bool $modified;
    protected bool $checked;
    protected string $userAgentCreated;

    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen', 'updated', 'userAgentCreated'];

    public function __construct(
        int $id,
        int $userId,
        ?string $lang,
        ?int $totalVisit,
        string $lastseen,
        string $created,
        string $updated,
        int $userAgentId,
        ?string $device,
        ?string $browserName,
        ?string $browserVersion,
        ?string $osName,
        ?string $osVersion,
        ?string $userAgent,
        ?bool $modified,
        bool $checked,
        string $userAgentCreated,
        int $key,
    ) {
        $this->id               = $id;
        $this->userId           = $userId;
        $this->lang             = $lang;
        $this->totalVisit       = $totalVisit;
        $this->lastseen         = $lastseen;
        $this->created          = $created;
        $this->updated          = $updated;
        $this->userAgentId      = $userAgentId;
        $this->device           = $device;
        $this->browserName      = $browserName;
        $this->browserVersion   = $browserVersion;
        $this->osName           = $osName;
        $this->osVersion        = $osVersion;
        $this->userAgent        = $userAgent;
        $this->modified         = $modified;
        $this->checked          = $checked;
        $this->userAgentCreated = $userAgentCreated;
        $this->key              = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Devices($key);

        return $model->where('device_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['device_id'],
            $data['device_account_id'],
            $data['device_lang'],
            $data['device_total_visit'],
            $data['device_lastseen'],
            $data['device_created'],
            $data['device_updated'],
            $data['user_agent_id'],
            $data['user_agent_device'],
            $data['user_agent_browser_name'],
            $data['user_agent_browser_version'],
            $data['user_agent_os_name'],
            $data['user_agent_os_version'],
            $data['user_agent_user_agent'],
            $data['user_agent_modified'],
            $data['user_agent_checked'],
            $data['user_agent_created'],
            $key,
        );
    }
}
