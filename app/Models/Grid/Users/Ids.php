<?php

/**
 * tirreno ~ open security analytics
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

namespace Models\Grid\Users;

class Ids extends \Models\Grid\Base\Ids {
    public function getUsersIdsByIpId(): string {
        return (
            'SELECT DISTINCT
                event.account AS itemid
            FROM event
            WHERE
                event.ip = :ip_id AND
                event.key = :api_key'
        );
    }

    public function getUsersIdsByIspId(): string {
        return (
            'SELECT DISTINCT
                event.account AS itemid
            FROM event_ip
            INNER JOIN event
            ON (event_ip.id = event.ip)
            WHERE
                event_ip.isp = :isp_id AND
                event_ip.key = :api_key'
        );
    }

    public function getUsersIdsByDomainId(): string {
        return (
            'SELECT DISTINCT
                event_email.account_id AS itemid
            FROM event_domain
            INNER JOIN event_email
            ON event_domain.id = event_email.domain
            WHERE
                event_domain.id = :domain_id AND
                event_domain.key = :api_key'
        );
    }

    public function getUsersIdsByCountryId(): string {
        return (
            'SELECT DISTINCT
                event.account AS itemid
            FROM event_ip
            INNER JOIN event
            ON (event_ip.id = event.ip)
            WHERE
                event_ip.country = :country_id AND
                event_ip.key = :api_key'
        );
    }

    public function getUsersIdsByDeviceId(): string {
        return (
            'SELECT DISTINCT
                event.account AS itemid
            FROM event
            INNER JOIN event_device
            ON (event_device.id = event.device)
            WHERE
                event_device.user_agent = :device_id AND
                event.key = :api_key'
        );
    }

    public function getUsersIdsByResourceId(): string {
        return (
            'SELECT DISTINCT
                event.account AS itemid
            FROM event
            WHERE
                event.url = :resource_id AND
                event.key = :api_key'
        );
    }

    public function getUsersIdsByFieldId(): string {
        return (
            'SELECT DISTINCT
                event_field_audit_trail.account_id AS itemid
            FROM event_field_audit_trail
            WHERE
                event_field_audit_trail.field_id = :field_id AND
                event_field_audit_trail.key = :api_key'
        );
    }
}
