<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Models\Context;

class Data {
    public function getContext(array $accountIds, int $apiKey): array {
        $model = new User();
        $userDetails = $model->getContext($accountIds, $apiKey);

        $model = new Ip();
        $ipDetails = $model->getContext($accountIds, $apiKey);

        $model = new Device();
        $deviceDetails = $model->getContext($accountIds, $apiKey);

        $model = new Email();
        $emailDetails = $model->getContext($accountIds, $apiKey);

        $model = new Phone();
        $phoneDetails = $model->getContext($accountIds, $apiKey);

        $model = new Event();
        $eventDetails = $model->getContext($accountIds, $apiKey);

        //$model = new Domain();
        //$domainDetails = $model->getContext($accountIds, $apiKey);

        $model = new \Models\ApiKeys();
        $timezoneName = $model->getTimezoneByKeyId($apiKey);

        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $timezone = new \DateTimeZone($timezoneName);
        $offsetInSeconds = $timezone->getOffset($utcTime);

        // get only suspicious sessions
        $model = new Session();
        $sessionDetails = $model->getContext($offsetInSeconds, $accountIds, $apiKey);

        //Extend user details context
        foreach ($userDetails as $userId => $user) {
            $user['le_exists']      = ($user['le_email'] ?? null) !== null;
            $user['le_email']       = $user['le_email'] ?? '';
            $user['le_local_part']  = explode('@', $user['le_email'])[0] ?? '';
            $user['le_domain_part'] = explode('@', $user['le_email'])[1] ?? '';

            $userId     = $user['ea_id'];
            $ip         = $ipDetails[$userId] ?? [];
            $device     = $deviceDetails[$userId] ?? [];
            $email      = $emailDetails[$userId] ?? [];
            $phone      = $phoneDetails[$userId] ?? [];
            $events     = $eventDetails[$userId] ?? [];
            $session    = $sessionDetails[$userId] ?? [];

            $user['eip_ip_id']              = $ip['eip_ip_id'] ?? [];
            $user['eip_ip']                 = $ip['eip_ip'] ?? [];
            $user['eip_cidr']               = $ip['eip_cidr'] ?? [];
            $user['eip_country_serial']     = $ip['eip_country_serial'] ?? [];
            $user['eip_data_center']        = $ip['eip_data_center'] ?? [];
            $user['eip_tor']                = $ip['eip_tor'] ?? [];
            $user['eip_vpn']                = $ip['eip_vpn'] ?? [];
            $user['eip_relay']              = $ip['eip_relay'] ?? [];
            $user['eip_starlink']           = $ip['eip_starlink'] ?? [];
            $user['eip_total_visit']        = $ip['eip_total_visit'] ?? [];
            $user['eip_blocklist']          = $ip['eip_blocklist'] ?? [];
            $user['eip_shared']             = $ip['eip_shared'] ?? [];
            //$user['eip_domains']          = $ip['eip_domains'] ?? [];
            $user['eip_country_id']         = $ip['eip_country_id'] ?? [];
            $user['eip_fraud_detected']     = $ip['eip_fraud_detected'] ?? [];
            $user['eip_alert_list']         = $ip['eip_alert_list'] ?? [];
            $user['eip_domains_count_len']  = $ip['eip_domains_count_len'] ?? [];

            $user['eup_device']             = $device['eup_device'] ?? [];
            $user['eup_device_id']          = $device['eup_device_id'] ?? [];
            $user['eup_browser_name']       = $device['eup_browser_name'] ?? [];
            $user['eup_browser_version']    = $device['eup_browser_version'] ?? [];
            $user['eup_os_name']            = $device['eup_os_name'] ?? [];
            $user['eup_lang']               = $device['eup_lang'] ?? [];
            $user['eup_ua']                 = $device['eup_ua'] ?? [];
            // $user['eup_lastseen']        = $device['eup_lastseen'] ?? [];
            // $user['eup_created']         = $device['eup_created'] ?? [];

            $user['ee_email']               = $email['ee_email'] ?? [];
            $user['ee_earliest_breach']     = $email['ee_earliest_breach'] ?? [];

            $user['ep_phone_number']        = $phone['ep_phone_number'] ?? [];
            $user['ep_shared']              = $phone['ep_shared'] ?? [];
            $user['ep_type']                = $phone['ep_type'] ?? [];

            $user['event_ip']               = $events['event_ip'] ?? [];
            $user['event_url_string']       = $events['event_url_string'] ?? [];
            $user['event_device']           = $events['event_device'] ?? [];
            $user['event_type']             = $events['event_type'] ?? [];
            $user['event_http_method']      = $events['event_http_method'] ?? [];
            $user['event_http_code']        = $events['event_http_code'] ?? [];
            $user['event_device_created']   = $events['event_device_created'] ?? [];
            $user['event_device_lastseen']  = $events['event_device_lastseen'] ?? [];

            $user['event_session_multiple_country'] = $session[0]['event_session_multiple_country'] ?? false;
            $user['event_session_multiple_ip']      = $session[0]['event_session_multiple_ip'] ?? false;
            $user['event_session_multiple_device']  = $session[0]['event_session_multiple_device'] ?? false;
            $user['event_session_night_time']       = $session[0]['event_session_night_time'] ?? false;

            //Extra params for rules
            $user = $this->extendParams($user);

            $userDetails[$userId] = $this->extendEventParams($user);
        }

        return $userDetails;
    }

    private function extendParams(array $record): array {
        //$record['timezone']

        $localPartLen   = strlen($record['le_local_part']);
        $domainPartLen  = strlen($record['le_domain_part']);
        $fullName       = $this->getUserFullName($record);

        $record['le_local_part_len']                = $localPartLen;
        $record['ea_fullnameHasNumbers']            = preg_match('~[0-9]+~', $fullName) > 0;
        $record['ea_days_since_account_creation']   = $this->getDaysSinceAccountCreation($record);
        $record['ea_days_since_last_visit']         = $this->getDaysSinceLastVisit($record);

        //$record['le_has_no_profiles']               = $record['le_profiles'] === 0;
        $record['le_has_no_data_breaches']          = $record['le_data_breach'] === false;
        $record['le_has_suspicious_str']            = $this->checkEmailForSuspiciousString($record);
        $record['le_has_numeric_only_local_part']   = preg_match('/^[0-9]+$/', $record['le_local_part']) > 0;
        $record['le_email_has_consec_s_chars']      = preg_match('/[^a-zA-Z0-9]{2,}/', $record['le_local_part']) > 0;
        $record['le_email_has_consec_nums']         = preg_match('/\d{2}/', $record['le_local_part']) > 0;
        $record['le_email_has_no_digits']           = !preg_match('/\d/', $record['le_local_part']);
        $record['le_email_has_vowels']              = preg_match('/[aeoui]/i', $record['le_local_part']) > 0;
        $record['le_email_has_consonants']          = preg_match('/[bcdfghjklmnpqrstvwxyz]/i', $record['le_local_part']) > 0;

        $record['le_with_long_local_part_length']   = $localPartLen > \Utils\Constants::get('RULE_EMAIL_MAXIMUM_LOCAL_PART_LENGTH');
        $record['le_with_long_domain_length']       = $domainPartLen > \Utils\Constants::get('RULE_EMAIL_MAXIMUM_DOMAIN_LENGTH');
        $record['le_email_in_blockemails']          = $record['le_blockemails'] ?? false;
        $record['le_is_invalid']                    = $record['le_exists'] && filter_var($record['le_email'], FILTER_VALIDATE_EMAIL) === false;

        $record['le_appears_on_alert_list']         = $record['le_alert_list'] ?? false;

        $record['ld_is_disposable']                 = $record['ld_disposable_domains'] ?? false;
        $record['ld_days_since_domain_creation']    = $this->getDaysSinceDomainCreation($record);
        $record['ld_domain_free_email_provider']    = $record['ld_free_email_provider'] ?? false;
        $record['ld_from_blockdomains']             = $record['ld_blockdomains'] ?? false;
        $record['ld_domain_without_mx_record']      = $record['ld_mx_record'] === false;
        $record['ld_website_is_disabled']           = $record['ld_disabled'] ?? false;
        $record['ld_tranco_rank']                   = $record['ld_tranco_rank'] ?? -1;

        $record['lp_invalid_phone'] = $record['lp_invalid'] === true;
        $record['ep_shared_phone']  = (bool) count(array_filter($record['ep_shared'], static function ($item) {
            return $item !== null && $item > 1;
        }));

        $daysSinceBreaches = array_map(function ($item) {
            return $this->getDaysTillToday($item);
        }, $record['ee_earliest_breach']);

        $record['ee_days_since_first_breach'] = count($daysSinceBreaches) ? max($daysSinceBreaches) : -1;

        $onlyNonResidentialParams = !(bool) count(array_filter(array_merge(
            $record['eip_fraud_detected'],
            $record['eip_blocklist'],
            $record['eip_tor'],
            $record['eip_starlink'],
            $record['eip_relay'],
            $record['eip_vpn'],
            $record['eip_data_center'],
        ), static function ($value): bool {
            return $value === true;
        }));
        $record['eip_only_residential'] = $onlyNonResidentialParams && !in_array(0, $record['eip_country_serial']);
        $record['eip_has_fraud']        = in_array(true, $record['eip_fraud_detected']);
        $record['eip_unique_cidrs']     = count(array_unique($record['eip_cidr']));
        $record['lp_fraud_detected']    = $record['lp_fraud_detected'] ?? false;
        $record['le_fraud_detected']    = $record['le_fraud_detected'] ?? false;

        $record['eup_has_rare_browser'] = (bool) count(array_diff($record['eup_browser_name'], array_keys(\Utils\Constants::get('RULE_REGULAR_BROWSER_NAMES'))));
        $record['eup_has_rare_os']      = (bool) count(array_diff($record['eup_os_name'], \Utils\Constants::get('RULE_REGULAR_OS_NAMES')));
        $record['eup_device_count']     = count($record['eup_device']);

        $record['eup_vulnerable_ua']    = false;

        $suspiciousSqlWords = \Utils\SuspiciousSqlWords::getWords();
        if (count($suspiciousSqlWords)) {
            foreach ($record['eup_ua'] as $url) {
                foreach ($suspiciousSqlWords as $sub) {
                    if (stripos($url, $sub) !== false) {
                        $record['eup_vulnerable_ua'] = true;
                        break 2;
                    }
                }
            }
        }

        return $record;
    }

    private function extendEventParams(array $record): array {
        // Remove null values specifically
        $eventTypeFiltered                  = $this->filterStringNum($record['event_type']);
        $eventHttpCodeFiltered              = $this->filterStringNum($record['event_http_code']);

        $eventTypeCount                     = array_count_values($eventTypeFiltered);

        //$accountLoginFailId = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL');
        $accountEmailChangeId               = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_EMAIL_CHANGE');
        $accountPasswordChangeId            = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        //$record['event_failed_login_attempts'] = $eventTypeCount[$accountLoginFailId] ?? 0;
        $record['event_email_changed']      = array_key_exists($accountEmailChangeId, $eventTypeCount);
        $record['event_password_changed']   = array_key_exists($accountPasswordChangeId, $eventTypeCount);

        $record['event_http_method_head']  = in_array(\Utils\Constants::get('EVENT_REQUEST_TYPE_HEAD'), $record['event_http_method']);

        $clientErrors = 0;
        $serverErrors = 0;
        foreach ($eventHttpCodeFiltered as $idx => $code) {
            if (is_int($code) && $code >= 400 && $code < 500) {
                ++$clientErrors;
            } elseif (is_int($code) && $code >= 500 && $code < 600) {
                ++$serverErrors;
            }
        }

        $record['event_multiple_5xx_http']  = $serverErrors;
        $record['event_multiple_4xx_http']  = $clientErrors;

        $record['event_vulnerable_url']     = false;

        $suspiciousUrlWords = \Utils\SuspiciousUrlWords::getWords();
        if (count($suspiciousUrlWords)) {
            foreach ($record['event_url_string'] as $url) {
                foreach ($suspiciousUrlWords as $sub) {
                    if (stripos($url, $sub) !== false) {
                        $record['event_vulnerable_url'] = true;
                        break 2;
                    }
                }
            }
        }

        return $record;
    }

    private function getDaysSinceDomainCreation(array $params): int {
        $dt1 = date('Y-m-d');
        $dt2 = $params['ld_creation_date'];

        return $this->getDaysDiff($dt1, $dt2);
    }

    private function getDaysSinceAccountCreation(array $params): int {
        $dt1 = date('Y-m-d');
        $dt2 = $params['ea_created'] ?? null;

        return $this->getDaysDiff($dt1, $dt2);
    }

    private function getDaysSinceLastVisit(array $params): int {
        $dt1 = date('Y-m-d');
        $dt2 = $params['ea_lastseen'] ?? null;

        return $this->getDaysDiff($dt1, $dt2);
    }

    private function getDaysTillToday(?string $dt2): int {
        $diff = -1;

        if ($dt2 !== null) {
            $dt1 = date('Y-m-d');
            $dt1 = new \DateTime($dt1);
            $dt2 = new \DateTime($dt2);
            $diff = $dt1->diff($dt2)->format('%a');
        }

        return $diff;
    }

    private function getDaysDiff(?string $dt1, ?string $dt2): int {
        $diff = -1;

        if ($dt2) {
            $dt1 = new \DateTime($dt1);
            $dt2 = new \DateTime($dt2);
            $diff = $dt1->diff($dt2)->format('%a');
        }

        return $diff;
    }

    private function getUserFullName(array $record): string {
        $name = [];
        $fName = $record['ea_firstname'] ?? '';
        if ($fName) {
            $name[] = $fName;
        }

        $lName = $record['ea_lastname'] ?? '';
        if ($lName) {
            $name[] = $lName;
        }

        return trim(join(' ', $name));
    }

    private function checkEmailForSuspiciousString(array $record): bool {
        foreach (\Utils\SuspiciousEmailWords::getWords() as $sub) {
            if (stripos($record['le_email'], $sub) !== false) {
                return true;
            }
        }

        return false;
    }

    private function filterStringNum(array $record): array {
        return array_filter($record, static function ($value): bool {
            return is_string($value) || is_int($value);
        });
    }
}
