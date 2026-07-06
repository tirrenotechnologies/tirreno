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

class Event {
    protected int $id;
    protected ?string $traceId;
    protected ?int $httpCode;
    protected ?int $httpMethodId;
    protected ?string $httpMethodValue;
    protected ?string $httpMethodName;
    protected ?int $typeId;
    protected ?string $typeValue;
    protected ?string $typeName;

    protected \Tirreno\Entities\User $user;
    protected \Tirreno\Entities\Email|\Tirreno\Entities\EmptyEmail $email;
    protected \Tirreno\Entities\Phone|\Tirreno\Entities\EmptyPhone $phone;
    protected \Tirreno\Entities\Device $device;
    protected \Tirreno\Entities\Ip $ip;
    protected \Tirreno\Entities\Session $session;
    protected \Tirreno\Entities\Resource $resource;
    protected \Tirreno\Entities\Query|\Tirreno\Entities\EmptyQuery $query;
    protected \Tirreno\Entities\Referer|\Tirreno\Entities\EmptyReferer $referer;
    protected \Tirreno\Entities\Payload $payload;

    protected string $time;

    protected int $key;

    protected array $nestedProps = ['user', 'email', 'phone', 'device', 'ip', 'session', 'resource', 'query', 'referer', 'payload'];
    protected array $tsFields = ['time'];

    public function __construct(
        int $id,
        ?string $traceId,
        ?int $httpCode,
        ?int $httpMethodId,
        ?string $httpMethodValue,
        ?string $httpMethodName,
        ?int $typeId,
        ?string $typeValue,
        ?string $typeName,
        \Tirreno\Entities\User $user,
        \Tirreno\Entities\Email|\Tirreno\Entities\EmptyEmail $email,
        \Tirreno\Entities\Phone|\Tirreno\Entities\EmptyPhone $phone,
        \Tirreno\Entities\Device $device,
        \Tirreno\Entities\Ip $ip,
        \Tirreno\Entities\Session $session,
        \Tirreno\Entities\Resource $resource,
        \Tirreno\Entities\Query|\Tirreno\Entities\EmptyQuery $query,
        \Tirreno\Entities\Referer|\Tirreno\Entities\EmptyReferer $referer,
        \Tirreno\Entities\Payload $payload,
        string $time,
        int $key,
    ) {
        $this->id               = $id;
        $this->traceId          = $traceId;
        $this->httpCode         = $httpCode;
        $this->httpMethodId     = $httpMethodId;
        $this->httpMethodValue  = $httpMethodValue;
        $this->httpMethodName   = $httpMethodName;
        $this->typeId           = $typeId;
        $this->typeValue        = $typeValue;
        $this->typeName         = $typeName;
        $this->user             = $user;
        $this->email            = $email;
        $this->phone            = $phone;
        $this->device           = $device;
        $this->ip               = $ip;
        $this->session          = $session;
        $this->resource         = $resource;
        $this->query            = $query;
        $this->referer          = $referer;
        $this->payload          = $payload;
        $this->time             = $time;
        $this->key              = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Events($key);

        return $model->where('event_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['event_id'],
            $data['event_traceid'],
            $data['event_http_code'],
            $data['event_http_method_id'],
            $data['event_http_method_value'],
            $data['event_http_method_name'],
            $data['event_type_id'],
            $data['event_type_value'],
            $data['event_type_name'],
            tirreno('entities')->user->getFromQuery($data, $key),
            isset($data['email_id']) ? tirreno('entities')->email->getFromQuery($data, $key) : tirreno('entities')->emptyEmail->get(),
            isset($data['phone_id']) ? tirreno('entities')->phone->getFromQuery($data, $key) : tirreno('entities')->emptyPhone->get(),
            tirreno('entities')->device->getFromQuery($data, $key),
            tirreno('entities')->ip->getFromQuery($data, $key),
            tirreno('entities')->session->getFromQuery($data, $key),
            tirreno('entities')->resource->getFromQuery($data, $key),
            isset($data['url_query_id']) ? tirreno('entities')->query->getFromQuery($data, $key) : tirreno('entities')->emptyQuery->get(),
            isset($data['referer_id']) ? tirreno('entities')->referer->getFromQuery($data, $key) : tirreno('entities')->emptyReferer->get(),
            tirreno('entities')->payload->getFromQuery($data, $key),
            $data['event_time'],
            $key,
        );
    }
}
