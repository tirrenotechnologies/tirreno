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

namespace Controllers\Admin\Rules;

class Ruler extends \Controllers\Base {
    private $ruleBuilder;

    public function __construct() {
        $this->ruleBuilder = new \Ruler\RuleBuilder();
    }

    public function calculate($rule, $params) {
        $uid = $rule['uid'];
        $handler = sprintf('rule%s', $uid);

        return method_exists($this, $handler) && $this->$handler($params) ? $rule['value'] : -1;
    }

    private function evaluateRule(array $cond, array $params): int {
        $context = new \Ruler\Context($params);
        $rule = $this->ruleBuilder->create($this->ruleBuilder->logicalAnd(...$cond));

        return $rule->evaluate($context);
    }

    private function ruleE02(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->ruleBuilder['ld_days_since_domain_creation']->lessThan(30),
            //$this->ruleBuilder['le_has_no_profiles']->equalTo(true),
            $this->ruleBuilder['le_has_no_data_breaches']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE03(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_has_suspicious_str']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE04(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_has_numeric_only_local_part']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE05(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_email_has_consec_s_chars']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE06(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_email_has_consec_nums']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE07(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_with_long_local_part_length']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE08(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_with_long_domain_length']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD06(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_total_device']->greaterThan(4),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB01(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_total_country']->greaterThan(3),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB17(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_total_country']->equalTo(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB18(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_http_method_head']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB19(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_session_night_time']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB20(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_session_multiple_country']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB21(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_session_multiple_device']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB22(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_session_multiple_ip']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI09(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_total_ip']->greaterThan(9),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE11(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_is_disposable']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE13(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->ruleBuilder['ld_days_since_domain_creation']->lessThan(90),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE18(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_has_no_profiles']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB07(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_fullnameHasNumbers']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE17(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_domain_free_email_provider']->equalTo(true),
            $this->ruleBuilder['le_email_in_blockemails']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE20(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_days_since_domain_creation']->notEqualTo(-1),
            $this->ruleBuilder['ld_days_since_domain_creation']->greaterThan(365 * 3),
            $this->ruleBuilder['ld_disposable_domains']->notEqualTo(true),
            $this->ruleBuilder['ld_free_email_provider']->notEqualTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE21(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_email_has_vowels']->equalTo(false),
            $this->ruleBuilder['le_local_part_len']->greaterThan(0),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE22(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_email_has_consonants']->equalTo(false),
            $this->ruleBuilder['le_local_part_len']->greaterThan(0),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE12(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_domain_free_email_provider']->equalTo(true),
            //$this->ruleBuilder['le_has_no_profiles']->equalTo(true),
            $this->ruleBuilder['le_has_no_data_breaches']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE16(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_from_blockdomains']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI01(array $params): bool {
        $params['eip_tor'] = in_array(true, $params['eip_tor']);
        $cond = [
            $this->ruleBuilder['eip_tor']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI06(array $params): bool {
        $params['eip_data_center'] = in_array(true, $params['eip_data_center']);
        $cond = [
            $this->ruleBuilder['eip_data_center']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI03(array $params): bool {
        $params['eip_blocklist'] = in_array(true, $params['eip_blocklist']);
        $cond = [
            $this->ruleBuilder['eip_blocklist']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI04(array $params): bool {
        $arrWithPositiveSharedIps = array_filter($params['eip_shared'], function ($item) {
            return $item > 1;
        });
        $params['eip_shared'] = count($arrWithPositiveSharedIps) > 0;
        $cond = [
            $this->ruleBuilder['eip_shared']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI05(array $params): bool {
        $params['eip_vpn'] = in_array(true, $params['eip_vpn']);
        $cond = [
            $this->ruleBuilder['eip_vpn']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI02(array $params): bool {
        $arrWithPositiveDomainsCounts = array_filter($params['eip_domains_count_len'], static function ($value): bool {
            return $value > 0;
        });
        $params['eip_domains_count_len'] = count($arrWithPositiveDomainsCounts) > 0;
        $cond = [
            $this->ruleBuilder['eip_domains_count_len']->greaterThan(0),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI07(array $params): bool {
        $params['eip_relay'] = in_array(true, $params['eip_relay']);
        $cond = [
            $this->ruleBuilder['eip_relay']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI08(array $params): bool {
        $params['eip_starlink'] = in_array(true, $params['eip_starlink']);
        $cond = [
            $this->ruleBuilder['eip_starlink']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI10(array $params): bool {
        $cond = [
            $this->ruleBuilder['eip_only_residential']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI11(array $params): bool {
        $cond = [
            $this->ruleBuilder['eip_unique_cidrs']->equalTo(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD01(array $params): bool {
        $params['eup_has_unknown_devices'] = in_array(null, $params['eup_device']);
        $cond = [
            $this->ruleBuilder['eup_has_unknown_devices']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD02(array $params): bool {
        $params['eup_has_linux_system'] = in_array('GNU/Linux', $params['eup_os_name']);
        $cond = [
            $this->ruleBuilder['eup_has_linux_system']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE09(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_domain_free_email_provider']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleR01(array $params): bool {
        $cond = [
            $this->ruleBuilder['eip_has_fraud']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleR02(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_fraud_detected']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleR03(array $params): bool {
        $cond = [
            $this->ruleBuilder['lp_fraud_detected']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    //Nigeria
    private function ruleC01(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_NIGERIA'));
    }

    //India
    private function ruleC02(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_INDIA'));
    }

    //China
    private function ruleC03(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_CHINA'));
    }

    //Brazil
    private function ruleC04(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_BRAZIL'));
    }

    //Pakistan
    private function ruleC05(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_PAKISTAN'));
    }

    //Indonesia
    private function ruleC06(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_INDONESIA'));
    }

    //Venezuela
    private function ruleC07(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_VENEZUELA'));
    }

    //South Africa
    private function ruleC08(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_SOUTH_AFRICA'));
    }

    //Philippines
    private function ruleC09(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_PHILIPPINES'));
    }

    //Romania
    private function ruleC10(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_ROMANIA'));
    }

    //Russia
    private function ruleC11(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_RUSSIA'));
    }

    //Europe
    private function ruleC12(array $params): bool {
        return $this->scoreCountryMatchArray($params, \Utils\Constants::get('COUNTRY_CODES_EUROPE'));
    }

    //North America
    private function ruleC13(array $params): bool {
        return $this->scoreCountryMatchArray($params, \Utils\Constants::get('COUNTRY_CODES_NORTH_AMERICA'));
    }

    //Australia
    private function ruleC14(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_AUSTRALIA'));
    }

    //Emirates
    private function ruleC15(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_UAE'));
    }

    //Japan
    private function ruleC16(array $params): bool {
        return $this->scoreCountryMatch($params, \Utils\Constants::get('COUNTRY_CODE_JAPAN'));
    }

    private function scoreCountryMatchArray(array $params, array $countries): bool {
        $common = array_intersect($countries, $params['eip_country_id']);
        $params['eip_has_specific_country'] = (bool) count($common);

        $cond = [
            $this->ruleBuilder['eip_has_specific_country']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function scoreCountryMatch(array $params, int $countryCodeId): bool {
        $params['eip_has_specific_country'] = in_array($countryCodeId, $params['eip_country_id']);
        $cond = [
            $this->ruleBuilder['eip_has_specific_country']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD03(array $params): bool {
        $params['eup_has_bot_devices'] = in_array('bot', $params['eup_device']);
        $cond = [
            $this->ruleBuilder['eup_has_bot_devices']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD04(array $params): bool {
        $cond = [
            $this->ruleBuilder['eup_has_rare_browser']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD05(array $params): bool {
        $cond = [
            $this->ruleBuilder['eup_has_rare_os']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE14(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_domain_without_mx_record']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE15(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_has_no_data_breaches']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD08(array $params): bool {
        $smartphoneCount = 0;
        foreach ($params['eup_device'] as $device) {
            if ($device === 'smartphone') {
                ++$smartphoneCount;
            }
        }
        $params['eup_phone_devices_count'] = $smartphoneCount;

        $cond = [
            $this->ruleBuilder['eup_phone_devices_count']->greaterThan(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE01(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_is_invalid']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE10(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_website_is_disabled']->equalTo(true),
            $this->ruleBuilder['ld_domain_free_email_provider']->notEqualTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleP02(array $params): bool {
        $params['lp_country_code_in_eip_country_id'] = $this->checkPhoneCountryMatchIp($params);
        $cond = [
            $this->ruleBuilder['lp_country_code_in_eip_country_id']->equalTo(false),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleP01(array $params): bool {
        $cond = [
            $this->ruleBuilder['lp_invalid_phone']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleP04(array $params): bool {
        $cond = [
            $this->ruleBuilder['lp_invalid_phone']->equalTo(false),
        ];

        return $this->evaluateRule($cond, $params);
    }

    //  TODO: return alert_list back in next release
    /*
    private function ruleR04($params) {
        $params['eip_appears_on_alert_list'] = in_array(true, $params['eip_alert_list']);
        $cond = [
            $this->ruleBuilder['eip_appears_on_alert_list']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleR05($params) {
        $cond = [
            $this->ruleBuilder['le_appears_on_alert_list']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleR06($params) {
        $cond = [
            $this->ruleBuilder['lp_appears_on_alert_list']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }*/

    private function ruleE19(array $params): bool {
        $params['ee_email_count'] = count($params['ee_email']);
        $cond = [
            $this->ruleBuilder['ee_email_count']->greaterThan(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE23(array $params): bool {
        $emailHasEdu = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '.edu')) {
                $emailHasEdu = true;
                break;
            }
        }

        $params['ee_has_edu'] = $emailHasEdu;
        $cond = [
            $this->ruleBuilder['ee_has_edu']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE24(array $params): bool {
        $emailHasGov = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '.gov')) {
                $emailHasGov = true;
                break;
            }
        }

        $params['ee_has_gov'] = $emailHasGov;
        $cond = [
            $this->ruleBuilder['ee_has_gov']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE25(array $params): bool {
        $emailHasMil = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '.mil')) {
                $emailHasMil = true;
                break;
            }
        }

        $params['ee_has_mil'] = $emailHasMil;
        $cond = [
            $this->ruleBuilder['ee_has_mil']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE26(array $params): bool {
        $emailHasApple = false;
        foreach ($params['ee_email'] as $email) {
            if (str_ends_with($email, '@icloud.com') || str_ends_with($email, '@me.com') || str_ends_with($email, '@mac.com')) {
                $emailHasApple = true;
                break;
            }
        }

        $params['ee_has_apple'] = $emailHasApple;
        $cond = [
            $this->ruleBuilder['ee_has_apple']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE27(array $params): bool {
        $cond = [
            //$this->ruleBuilder['le_has_no_profiles']->equalTo(false),
            // do not trigger if le_data_breach is null
            $this->ruleBuilder['le_data_breach']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE28(array $params): bool {
        $cond = [
            $this->ruleBuilder['le_email_has_no_digits']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE29(array $params): bool {
        $cond = [
            $this->ruleBuilder['ee_days_since_first_breach']->greaterThan(365 * 3),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleE30(array $params): bool {
        $cond = [
            $this->ruleBuilder['ld_tranco_rank']->greaterThan(100000),
            $this->ruleBuilder['ld_tranco_rank']->lessThan(4000000),
            $this->ruleBuilder['ld_domain_free_email_provider']->notEqualTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD07(array $params): bool {
        $desktopDevicesWithDifferentOS = false;
        $firstDesktopOS = '';
        for ($i = 0; $i < $params['eup_device_count']; ++$i) {
            if ($params['eup_device'][$i] === 'desktop') {
                if ($firstDesktopOS === '') {
                    $firstDesktopOS = $params['eup_os_name'][$i];
                } elseif ($firstDesktopOS !== $params['eup_os_name'][$i]) {
                    $desktopDevicesWithDifferentOS = true;
                    break;
                }
            }
        }
        $params['eup_desktop_devices_with_different_os'] = $desktopDevicesWithDifferentOS;
        $cond = [
            $this->ruleBuilder['eup_desktop_devices_with_different_os']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB08(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_last_visit']->greaterThan(30),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB09(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_last_visit']->greaterThan(90),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB10(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_last_visit']->greaterThan(365),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB11(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->lessThan(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB12(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->lessThan(7),
            $this->ruleBuilder['ea_days_since_account_creation']->greaterThanOrEqualTo(1),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB13(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->lessThan(30),
            $this->ruleBuilder['ea_days_since_account_creation']->greaterThanOrEqualTo(7),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB14(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->lessThan(90),
            $this->ruleBuilder['ea_days_since_account_creation']->greaterThanOrEqualTo(30),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB15(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->lessThan(180),
            $this->ruleBuilder['ea_days_since_account_creation']->greaterThanOrEqualTo(90),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB16(array $params): bool {
        $cond = [
            $this->ruleBuilder['ea_days_since_account_creation']->notEqualTo(-1),
            $this->ruleBuilder['ea_days_since_account_creation']->greaterThanOrEqualTo(180),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA01(array $params): bool {
        $maximumAttempts = \Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_LOGIN_ATTEMPTS');
        $loginFail = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL');
        $windowSize = \Utils\Constants::get('RULE_LOGIN_ATTEMPTS_WINDOW');
        $tooManyLoginAttempts = false;
        $cnt = 0;
        $start = 0;

        for ($end = 0; $end < count($params['event_type']); ++$end) {
            if ($params['event_type'][$end] === $loginFail) {
                ++$cnt;
            }
            if ($end >= $windowSize - 1) {
                if ($cnt > $maximumAttempts) {
                    $tooManyLoginAttempts = true;
                    break;
                }
                if ($params['event_type'][$start] === $loginFail) {
                    --$cnt;
                }
                ++$start;
            }
        }

        $params['event_many_failed_login_attempts'] = $tooManyLoginAttempts;
        $cond = [
            $this->ruleBuilder['event_many_failed_login_attempts']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA02(array $params): bool {
        $suspiciousLoginFailed = false;
        $loginFail = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL');

        foreach ($params['event_type'] as $idx => $event) {
            if ($event === $loginFail && $this->eventDeviceIsNew($params, $idx)) {
                $suspiciousLoginFailed = true;
                break;
            }
        }

        $params['event_failed_login_on_new_device'] = $suspiciousLoginFailed;
        $cond = [
            $this->ruleBuilder['event_failed_login_on_new_device']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA03(array $params) {
        $eventNewDeviceNewCountry = false;
        if ($params['eup_device_count'] > 1 && count(array_unique($params['eip_country_id'])) > 1) {
            foreach ($params['event_device'] as $idx => $device) {
                if ($this->eventDeviceIsNew($params, $idx) && $this->countryIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $eventNewDeviceNewCountry = true;
                    break;
                }
            }
        }

        $params['event_new_device_and_new_country'] = $eventNewDeviceNewCountry;
        $cond = [
            $this->ruleBuilder['event_new_device_and_new_country']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA04(array $params): bool {
        $eventNewDeviceNewCidr = false;
        if ($params['eup_device_count'] > 1 && $params['eip_unique_cidrs'] > 1) {
            foreach ($params['event_device'] as $idx => $device) {
                if ($this->eventDeviceIsNew($params, $idx) && $this->cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $eventNewDeviceNewCidr = true;
                    break;
                }
            }
        }

        $params['event_new_device_and_new_cidr'] = $eventNewDeviceNewCidr;
        $cond = [
            $this->ruleBuilder['event_new_device_and_new_cidr']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA05(array $params): bool {
        $passwordChangeOnNewDevice = false;
        $passwordChange = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        if ($params['eup_device_count'] > 1) {
            foreach ($params['event_device'] as $idx => $device) {
                if ($params['event_type'][$idx] === $passwordChange && $this->eventDeviceIsNew($params, $idx)) {
                    $passwordChangeOnNewDevice = true;
                    break;
                }
            }
        }

        $params['event_password_change_on_new_device'] = $passwordChangeOnNewDevice;
        $cond = [
            $this->ruleBuilder['event_password_change_on_new_device']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA06(array $params): bool {
        $passwordChangeInNewCountry = false;
        $passwordChange = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        if (count(array_unique($params['eip_country_id'])) > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $passwordChange) {
                    if ($this->countryIsNewByIpId($params, $params['event_ip'][$idx])) {
                        $passwordChangeInNewCountry = true;
                        break;
                    }
                }
            }
        }

        $params['event_password_change_in_new_country'] = $passwordChangeInNewCountry;
        $cond = [
            $this->ruleBuilder['event_password_change_in_new_country']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA07(array $params): bool {
        $passwordChangeInNewCidr = false;
        $passwordChange = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        if ($params['eip_unique_cidrs'] > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $passwordChange && $this->cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $passwordChangeInNewCidr = true;
                    break;
                }
            }
        }

        $params['event_password_change_in_new_cidr'] = $passwordChangeInNewCidr;
        $cond = [
            $this->ruleBuilder['event_password_change_in_new_cidr']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleA08(array $params): bool {
        $newBrowserLanguage = false;
        // $item ?? '' because `lang` can be null, which we should process as an empty string
        $langs = array_map(function ($item) {
            return strtoupper(explode('-', preg_replace('/;.*$/', '', trim(explode(',', $item ?? '')[0])))[0]);
        }, $params['eup_lang']);

        $langCount = array_count_values($langs);

        if ($params['eup_device_count'] > 1 && count($langCount) > 1) {
            foreach ($params['event_device'] as $idx => $deviceId) {
                if ($this->eventDeviceIsNew($params, $idx)) {
                    $innerId = array_search($deviceId, $params['eup_device_id']);
                    $lang = strtoupper(explode('-', preg_replace('/;.*$/', '', trim(explode(',', $params['eup_lang'][$innerId] ?? '')[0])))[0]);
                    if ($langCount[$lang] === 1) {
                        $newBrowserLanguage = true;
                        break;
                    }
                }
            }
        }

        $params['event_new_browser_language'] = $newBrowserLanguage;
        $cond = [
            $this->ruleBuilder['event_new_browser_language']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD09(array $params): bool {
        $minVersion = null;
        $browserVersion = '';
        $oldBrowser = false;
        for ($i = 0; $i < count($params['eup_browser_name']); ++$i) {
            $minVersion = \Utils\Constants::get('RULE_REGULAR_BROWSER_NAMES')[$params['eup_browser_name'][$i]] ?? null;
            if ($minVersion !== null) {
                $browserVersion = explode('.', $params['eup_browser_version'][$i] ?? '')[0];
                if (ctype_digit($browserVersion) && intval($browserVersion) < $minVersion) {
                    $oldBrowser = true;
                    break;
                }
            }
        }

        $params['eup_old_browser'] = $oldBrowser;
        $cond = [
            $this->ruleBuilder['eup_old_browser']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleI12(array $params): bool {
        $isLan = false;
        for ($i = 0; $i < count($params['eip_ip_id']); ++$i) {
            // invalid ip or N/A isp should have `eip_data_center` === null
            if ($params['eip_cidr'][$i] === null && $params['eip_data_center'][$i] === false) {
                $isLan = true;
                break;
            }
        }

        $params['eip_lan'] = $isLan;
        $cond = [
            $this->ruleBuilder['eip_lan']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB02(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_password_changed']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB03(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_email_changed']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB04(array $params): bool {
        $maximumErrors = \Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_500_CODES');
        $cond = [
            $this->ruleBuilder['event_multiple_5xx_http']->greaterThan($maximumErrors),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB05(array $params): bool {
        $maximumErrors = \Utils\Constants::get('RULE_MAXIMUM_NUMBER_OF_404_CODES');
        $cond = [
            $this->ruleBuilder['event_multiple_4xx_http']->greaterThanOrEqualTo($maximumErrors),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleB06(array $params): bool {
        $cond = [
            $this->ruleBuilder['event_vulnerable_url']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleD10(array $params): bool {
        $cond = [
            $this->ruleBuilder['eup_vulnerable_ua']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    private function ruleP03(array $params): bool {
        $cond = [
            $this->ruleBuilder['ep_shared_phone']->equalTo(true),
        ];

        return $this->evaluateRule($cond, $params);
    }

    //TODO: aditional functions. Move to separate class
    private function checkPhoneCountryMatchIp(array $params): bool {
        if (is_null($params['lp_country_code']) || $params['lp_country_code'] === 0) {
            return true;
        }

        return in_array($params['lp_country_code'], $params['eip_country_id']);
    }

    private function eventDeviceIsNew(array $params, int $idx): bool {
        $deviceCreated = new \DateTime($params['event_device_created'][$idx]);
        $deviceLastseen = new \DateTime($params['event_device_lastseen'][$idx]);
        $interval = $deviceCreated->diff($deviceLastseen);

        return abs($interval->days * 24 * 60 + $interval->h * 60 + $interval->i) < \Utils\Constants::get('RULE_NEW_DEVICE_MAX_AGE_IN_MINUTES');
    }

    private function countryIsNewByIpId(array $params, int $ipId): bool {
        $filtered = array_filter($params['eip_country_id'], function ($value) {
            return $value !== null;
        });
        $countryCounts = array_count_values($filtered);
        $ipIdx = array_search($ipId, $params['eip_ip_id']);
        $eventIpCountryId = $params['eip_country_id'][$ipIdx];
        $count = $countryCounts[$eventIpCountryId] ?? 0;

        return $count === 1;
    }

    private function cidrIsNewByIpId(array $params, int $ipId): bool {
        $filtered = array_filter($params['eip_cidr'], function ($value) {
            return $value !== null;
        });
        $cidrCounts = array_count_values($filtered);
        $ipIdx = array_search($ipId, $params['eip_ip_id']);
        $eventIpCidr = $params['eip_cidr'][$ipIdx];
        $count = $cidrCounts[$eventIpCidr] ?? 0;

        return $count === 1;
    }
}
