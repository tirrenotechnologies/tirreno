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

return [
    'AdminDomain_page_title' => 'Domain',
    'AdminDomain_breadcrumb_title' => 'Domain',
    'AdminDomain_table_title' => 'Domains',

    'AdminDomain_registration_date' => 'Creation date',
    'AdminDomain_registration_date_tooltip' => 'The date of the domain creation.',
    'AdminDomain_expiration_date' => 'Expiration date',
    'AdminDomain_expiration_date_tooltip' => 'The date of the domain expiration.',
    'AdminDomain_counters_accounts' => 'User count',
    'AdminDomain_counters_accounts_tooltip' => 'The number of users with email addresses belonging to the domain.',
    'AdminDomain_counters_free_email_provider' => 'Free provider',
    'AdminDomain_counters_free_email_provider_tooltip' => 'Whether the domain belongs to a provider that offers free accounts.',
    'AdminDomain_counters_tranco_rank' => 'Web ranking',
    'AdminDomain_counters_tranco_rank_tooltip' => 'Popularity of the domain name.',
    'AdminDomain_counters_unavailable' => 'Unavailable',
    'AdminDomain_counters_unavailable_tooltip' => 'Whether the domain’s website is inactive. This can be a sign of a fake mailbox.',
    'AdminDomain_counters_disposable' => 'Disposable',
    'AdminDomain_counters_disposable_tooltip' => 'Whether a domain is identified as associated with throwaway email addresses.',
    'AdminDomain_counters_fraud' => 'Blacklisted',
    'AdminDomain_counters_fraud_tooltip' => 'The number of blacklisted users with email at this domain.',
    'AdminDomains_table_same_ip' => 'Domain neighbours',
    'AdminDomains_table_same_ip_tooltip' => 'Domain hosting on the same IP address as current domain.',
];
