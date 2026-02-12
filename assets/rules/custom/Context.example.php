<?php

declare(strict_types=1);

namespace Tirreno\Rules\Custom;

/**
 * @example This is a sample implementation for demonstration purposes.
 * @internal Do not use in production - copy and modify for your own rules.
 */
class Context extends \Tirreno\Assets\Context {
    /** @var string Database table to query for context data */
    protected ?string $DB_TABLE_NAME = 'event_account';

    /** @var bool Whether to return only unique values from context queries */
    protected ?bool $uniqueValues = false;

    /**
     * Enriches user data with custom context flags.
     *
     * Sets a boolean flag indicating whether the user's ID starts with '1'.
     * This can be used for rule-based fraud detection or user segmentation.
     *
     * @param array $extraData Context data retrieved from getDetails(), keyed by field name
     * @param array $user      User data array to be enriched (modified by reference)
     */
    public function expandContext(array &$extraData, array &$user): void {
        // Check if the first character of the first userid value is '1'
        // Falls back to a space character if no userid exists to ensure substr works safely
        $user['extra_one_digit_userid'] = substr(($extraData['extra_userid'][0][0] ?? ' '), 0, 1) === '1';
    }

    /**
     * Retrieves account details from the database for the given account IDs.
     *
     * Queries the event_account table to fetch the account ID and user ID
     * for all specified accounts belonging to the given API key.
     *
     * @param array $accountIds List of account IDs to look up
     * @param int   $apiKey     API key to filter results (ensures tenant isolation)
     *
     * @return array Query results containing 'id' and 'extra_userid' for each matching account
     */
    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_account.id                        AS id,
                event_account.userid                    AS extra_userid
            FROM
                event_account
            WHERE
                event_account.id IN ({$placeHolders}) AND
                event_account.key = :api_key"
        );

        return $this->execQuery($query, $params);
    }
}
