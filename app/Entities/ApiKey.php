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

class ApiKey {
    public int $id;
    public string $key;
    //public int $quote;
    public int $creator;
    public string $createdAt;
    public array $skipEnrichingAttr;
    public int $retentionPolicy;
    public bool $skipBlacklistSync;
    public ?string $token;
    public ?bool $lastCallReached;
    public ?int $blacklistThreshold;
    public ?int $reviewQueueThreshold;

    public function __construct(
        int $id,
        string $key,
        //int $quote,
        int $creator,
        string $createdAt,
        string $skipEnrichingAttr,
        int $retentionPolicy,
        bool $skipBlacklistSync,
        ?string $token,
        ?bool $lastCallReached,
        ?int $blacklistThreshold,
        ?int $reviewQueueThreshold,
    ) {
        $this->id                   = $id;
        $this->key                  = $key;
        //$this->quote              = $quote;
        $this->creator              = $creator;
        $this->createdAt            = $createdAt;
        $this->skipEnrichingAttr    = json_decode($skipEnrichingAttr);
        $this->retentionPolicy      = $retentionPolicy;
        $this->skipBlacklistSync    = $skipBlacklistSync;
        $this->token                = $token;
        $this->lastCallReached      = $lastCallReached;
        $this->blacklistThreshold   = $blacklistThreshold;
        $this->reviewQueueThreshold = $reviewQueueThreshold;
    }

    public static function getById(int $apiKey): ?self {
        $model = new \Tirreno\Models\ApiKeys();
        $key = $model->getKeyById($apiKey);

        if (!$key) {
            return null;
        }

        return new self(
            $key['id'],
            $key['key'],
            //$key['quote'],
            $key['creator'],
            $key['created_at'],
            $key['skip_enriching_attributes'],
            $key['retention_policy'],
            $key['skip_blacklist_sync'],
            $key['token'],
            $key['last_call_reached'],
            $key['blacklist_threshold'],
            $key['review_queue_threshold'],
        );
    }
}
