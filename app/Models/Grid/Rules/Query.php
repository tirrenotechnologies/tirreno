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

namespace Tirreno\Models\Grid\Rules;

class Query extends \Tirreno\Models\Grid\Base\Query {
    protected ?string $defaultOrder = 'dshb_rules.validated DESC, (dshb_rules.missing IS TRUE) ASC, dshb_rules.uid ASC';
    protected string $dateRangeField = 'proportion_updated_at';

    protected array $allowedColumns = ['uid'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.missing,
                (NOT COALESCE(dshb_rules.validated, FALSE) OR COALESCE(dshb_rules.missing, FALSE)) AS broken,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
                dshb_operators_rules.set,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key %s)

            LEFT JOIN dshb_api
            ON (dshb_api.id = :api_key)

            WHERE
                NOT EXISTS (
                    SELECT 1
                    FROM jsonb_array_elements_text(dshb_rules.attributes) AS rules(attributes)
                    JOIN jsonb_array_elements_text(dshb_api.skip_enriching_attributes) AS key(skip_attributes)
                    ON rules.attributes = key.skip_attributes
                )'
        );

        $this->applySearch($query, $queryParams);
        $this->applyOrder($query);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT(dshb_rules.uid)

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key %s)

            LEFT JOIN dshb_api
            ON (dshb_api.id = :api_key)

            WHERE
                NOT EXISTS (
                    SELECT 1
                    FROM jsonb_array_elements_text(dshb_rules.attributes) AS rules(attributes)
                    JOIN jsonb_array_elements_text(dshb_api.skip_enriching_attributes) AS key(skip_attributes)
                    ON rules.attributes = key.skip_attributes
                )'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $setId = tirreno('utils')->conversion->getIntRequestParam('set');
        if (!$setId) {
            $query = sprintf($query, '');

            return;
        }

        $queryParams[':set_id'] = $setId;
        $query = sprintf($query, 'AND dshb_operators_rules.set = :set_id');
    }
}
