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

class User {
    public int $id;
    public string $userid;
    public string $lastseen;
    public string $created;
    public ?string $firstname;
    public ?string $lastname;
    public int $score;
    public ?array $scoreDetails;
    public ?string $scoreUpdatedAt;
    public bool $isImportant;
    public ?bool $fraud;
    public ?bool $reviewed;
    public ?string $latestDecision;
    public ?string $addedToReview;
    public ?string $email;

    public function __construct(
        int $id,
        string $userid,
        string $lastseen,
        string $created,
        ?string $firstname,
        ?string $lastname,
        int $score,
        ?string $scoreDetails,
        ?string $scoreUpdatedAt,
        bool $isImportant,
        ?bool $fraud,
        ?bool $reviewed,
        ?string $latestDecision,
        ?string $addedToReview,
        ?string $email,
    ) {
        $this->id               = $id;
        $this->userid           = $userid;
        $this->lastseen         = $lastseen;
        $this->created          = $created;
        $this->firstname        = $firstname;
        $this->lastname         = $lastname;
        $this->score            = $score;
        $this->scoreDetails     = $scoreDetails !== null ? json_decode($scoreDetails) : $scoreDetails;
        $this->scoreUpdatedAt   = $scoreUpdatedAt;
        $this->isImportant      = $isImportant;
        $this->fraud            = $fraud;
        $this->reviewed         = $reviewed;
        $this->latestDecision   = $latestDecision;
        $this->addedToReview    = $addedToReview;
        $this->email            = $email;
    }

    public static function getById(int $accountId, int $apiKey): ?self {
        $model = new \Tirreno\Models\User();
        $user = $model->getUserById($accountId, $apiKey);

        if (!$user) {
            return null;
        }

        return new self(
            $user['accountid'],
            $user['userid'],
            $user['lastseen'],
            $user['created'],
            $user['firstname'],
            $user['lastname'],
            $user['score'],
            $user['score_details'],
            $user['score_updated_at'],
            $user['is_important'],
            $user['fraud'],
            $user['reviewed'],
            $user['latest_decision'],
            $user['added_to_review'],
            $user['email'],
        );
    }
}
