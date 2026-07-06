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

class User extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $userid;
    protected ?string $fullname;
    protected ?string $firstname;
    protected ?string $middlename;
    protected ?string $lastname;

    protected ?int $score;
    protected ?array $scoreDetails;
    protected ?bool $reviewed;
    protected ?bool $fraud;

    protected ?bool $scoreRecalculate;
    protected ?bool $isImportant;
    protected ?string $lastIp;

    protected ?int $totalVisit;
    protected ?int $totalCountry;
    protected ?int $totalIp;
    protected ?int $totalDevice;
    protected ?int $totalSharedIp;
    protected ?int $totalSharedPhone;

    protected string $lastseen;
    protected string $created;
    protected string $updated;
    protected string $scoreUpdatedAt;
    protected ?string $latestDecision;
    protected ?string $addedToReview;

    protected \Tirreno\Entities\Email|\Tirreno\Entities\EmptyEmail $email;
    protected \Tirreno\Entities\Phone|\Tirreno\Entities\EmptyPhone $phone;

    protected int $key;

    protected array $nestedProps = ['email', 'phone'];
    protected array $tsFields = ['created', 'lastseen', 'scoreUpdatedAt', 'latestDecision', 'updated', 'addedToReview'];

    public function __construct(
        int $id,
        string $userid,
        ?string $fullname,
        ?string $firstname,
        ?string $middlename,
        ?string $lastname,
        ?int $score,
        ?string $scoreDetails,
        ?bool $reviewed,
        ?bool $fraud,
        ?bool $scoreRecalculate,
        ?bool $isImportant,
        ?string $lastIp,
        ?int $totalVisit,
        ?int $totalCountry,
        ?int $totalIp,
        ?int $totalDevice,
        ?int $totalSharedIp,
        ?int $totalSharedPhone,
        string $lastseen,
        string $created,
        string $updated,
        string $scoreUpdatedAt,
        ?string $latestDecision,
        ?string $addedToReview,
        \Tirreno\Entities\Email|\Tirreno\Entities\EmptyEmail $email,
        \Tirreno\Entities\Phone|\Tirreno\Entities\EmptyPhone $phone,
        int $key,
    ) {
        $this->id               = $id;
        $this->userid           = $userid;
        $this->fullname         = $fullname;
        $this->firstname        = $firstname;
        $this->middlename       = $middlename;
        $this->lastname         = $lastname;
        $this->score            = $score;
        $this->scoreDetails     = $scoreDetails ? json_decode($scoreDetails, true) : null;
        $this->reviewed         = $reviewed;
        $this->fraud            = $fraud;
        $this->scoreRecalculate = $scoreRecalculate;
        $this->isImportant      = $isImportant;
        $this->lastIp           = $lastIp;
        $this->totalVisit       = $totalVisit;
        $this->totalCountry     = $totalCountry;
        $this->totalIp          = $totalIp;
        $this->totalDevice      = $totalDevice;
        $this->totalSharedIp    = $totalSharedIp;
        $this->totalSharedPhone = $totalSharedPhone;
        $this->lastseen         = $lastseen;
        $this->created          = $created;
        $this->updated          = $updated;
        $this->scoreUpdatedAt   = $scoreUpdatedAt;
        $this->latestDecision   = $latestDecision;
        $this->addedToReview    = $addedToReview;
        $this->email            = $email;
        $this->phone            = $phone;
        $this->key              = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Users($key);

        return $model->where('user_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['user_id'],
            $data['user_userid'],
            $data['user_fullname'],
            $data['user_firstname'],
            $data['user_middlename'],
            $data['user_lastname'],
            $data['user_score'],
            $data['user_score_details'],
            $data['user_reviewed'],
            $data['user_fraud'],
            $data['user_score_recalculate'],
            $data['user_is_important'],
            $data['user_last_ip'],
            $data['user_total_visit'],
            $data['user_total_country'],
            $data['user_total_ip'],
            $data['user_total_device'],
            $data['user_total_shared_ip'],
            $data['user_total_shared_phone'],
            $data['user_lastseen'],
            $data['user_created'],
            $data['user_updated'],
            $data['user_score_updated_at'],
            $data['user_latest_decision'],
            $data['user_added_to_review'],
            isset($data['email_id']) ? tirreno('entities')->email->getFromQuery($data, $key) : tirreno('entities')->emptyEmail->get(),
            isset($data['phone_id']) ? tirreno('entities')->phone->getFromQuery($data, $key) : tirreno('entities')->emptyPhone->get(),
            $key,
        );
    }

    public function setWhitelist(): void {
        $this->fraud = false;
        tirreno('models')->user->updateFraudFlag([$this->id], $this->key, false);
    }

    public function setBlacklist(): void {
        $this->fraud = true;
        tirreno('models')->user->updateFraudFlag([$this->id], $this->key, true);
    }

    // immediately!
    public function delete(): void {
        tirreno('models')->user->deleteAllUserData($this->id, $this->key);
    }

    public function isScheduledForDeletion(): array {
        [$scheduled, $status] = tirreno('models')->queue->isInQueueStatus($this->id, tirreno('utils')->constants->DELETE_USER_QUEUE_ACTION_TYPE, $this->key);

        return [$scheduled, ($status === tirreno('utils')->constants->FAILED_QUEUE_STATUS_TYPE) ? tirreno('utils')->errorCodes->USER_DELETION_FAILED : null];
    }

    public function isScheduledForBlacklist(): array {
        [$scheduled, $status] = tirreno('models')->queue->isInQueueStatus($this->id, tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE, $this->key);

        return [$scheduled, ($status === tirreno('utils')->constants->FAILED_QUEUE_STATUS_TYPE) ? tirreno('utils')->errorCodes->USER_BLACKLISTING_FAILED : null];
    }

    public function extendScoreDetails(): void {
        $rules = tirreno('models')->rules->getAll();

        $details = [];
        $scoreDetails = $this->scoreDetails ?? [];

        foreach ($scoreDetails as $detail) {
            $score = $detail['score'] ?? null;
            $ruleUid = $detail['uid'] ?? null;
            if ($score !== 0 && isset($rules[$ruleUid])) {
                $item = $rules[$ruleUid];
                $item['score'] = $score;
                $details[] = $item;
            }
        }

        usort($details, [\Tirreno\Utils\Sort::class, 'cmpScore']);
        $this->scoreDetails = $details;
    }
}
