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

namespace Models\Grid\Isps;

class Ids extends \Models\Grid\Base\Ids {
    public function getIspsIdsByUserId(): string {
        return (
            'SELECT DISTINCT
                event_ip.isp AS itemid
            FROM event_ip
            INNER JOIN event
            ON (event_ip.id = event.ip)
            WHERE
                event_ip.key = :api_key AND
                event.account = :account_id'
        );
    }

    public function getIspsIdsByDomainId(): string {
        return (
            'SELECT DISTINCT
                event_ip.isp AS itemid
            FROM event
            INNER JOIN event_ip
            ON (event.ip = event_ip.id)
            LEFT JOIN event_email
            ON (event.email = event_email.id)
            WHERE
                event_email.key = :api_key AND
                event_email.domain = :domain_id'
        );
    }

    public function getIspsIdsByCountryId(): string {
        return (
            'SELECT DISTINCT
                event_ip.isp AS itemid
            FROM event_ip
            WHERE
                event_ip.key = :api_key AND
                event_ip.country = :country_id'
        );
    }

    public function getIspsIdsByResourceId(): string {
        return (
            'SELECT DISTINCT
                event_ip.isp AS itemid
            FROM event
            INNER JOIN event_ip
            ON (event.ip = event_ip.id)
            WHERE
                event.url = :resource_id AND
                event.key = :api_key'
        );
    }

    public function getIspsIdsByFieldId(): string {
        return (
            'SELECT DISTINCT
                event_ip.isp AS itemid
            FROM event
            INNER JOIN event_ip
            ON (event.ip = event_ip.id)
            INNER JOIN event_field_audit_trail
            ON (event.id = event_field_audit_trail.event_id)
            WHERE 
                event_field_audit_trail.field_id = :field_id AND
                event_field_audit_trail.key = :api_key'
        );
    }
}
