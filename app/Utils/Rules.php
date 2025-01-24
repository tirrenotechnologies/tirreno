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

namespace Utils;

class Rules {
    private const rules = [
        // account takeover
        16  => ['uid' => 'A01', 'name' => 'Multiple login fail', 'description' => 'User failed to login multiple times in a short term, which can be a sign of account takeover.', 'attributes' => []],
        66  => ['uid' => 'A02', 'name' => 'Login failed on new device', 'description' => 'User failed to login with new device, which can be a sign of account takeover.', 'attributes' => []],
        67  => ['uid' => 'A03', 'name' => 'New device and new country', 'description' => 'User logged in with new device from new location, which can be a sign of account takeover.', 'attributes' => ['ip']],
        68  => ['uid' => 'A04', 'name' => 'New device and new subnet', 'description' => 'User logged in with new device from new subnet, which can be a sign of account takeover.', 'attributes' => ['ip']],
        69  => ['uid' => 'A05', 'name' => 'Password change on new device', 'description' => 'User changed their password on new device, which can be a sign of account takeover.', 'attributes' => []],
        70  => ['uid' => 'A06', 'name' => 'Password change in new country', 'description' => 'User changed their password in new country, which can be a sign of account takeover.', 'attributes' => ['ip']],
        71  => ['uid' => 'A07', 'name' => 'Password change in new subnet', 'description' => 'User changed their password in new subnet, which can be a sign of account takeover.', 'attributes' => ['ip']],
        72  => ['uid' => 'A08', 'name' => 'Browser language changed', 'description' => 'User accessed the account with new browser language, which can be a sign of account takeover.', 'attributes' => []],
        // behaviour
        11  => ['uid' => 'B01', 'name' => 'Multiple countries', 'description' => 'IP addresses are located in diverse countries, which is a rare behaviour for regular users.', 'attributes' => ['ip']],
        17  => ['uid' => 'B02', 'name' => 'User has changed a password', 'description' => 'The user has changed their password.', 'attributes' => []],
        18  => ['uid' => 'B03', 'name' => 'User has changed an email', 'description' => 'The user has changed their email.', 'attributes' => []],
        19  => ['uid' => 'B04', 'name' => 'Multiple 5xx errors', 'description' => 'The user made multiple requests which evoked internal server error.', 'attributes' => []],
        20  => ['uid' => 'B05', 'name' => 'Multiple 4xx errors', 'description' => 'The user made multiple requests which cannot be fulfilled.', 'attributes' => []],
        22  => ['uid' => 'B06', 'name' => 'Potentially vulnerable URL', 'description' => 'The user made a request to suspicious URL.', 'attributes' => []],
        4   => ['uid' => 'B07', 'name' => 'User\'s full name contains digits', 'description' => 'Full name contains digits, which is a rare behaviour for regular users.', 'attributes' => []],
        87  => ['uid' => 'B08', 'name' => 'Dormant account (30 days)', 'description' => 'The account has been inactive for 30 days.', 'attributes' => []],
        88  => ['uid' => 'B09', 'name' => 'Dormant account (90 days)', 'description' => 'The account has been inactive for 90 days.', 'attributes' => []],
        89  => ['uid' => 'B10', 'name' => 'Dormant account (1 year)', 'description' => 'The account has been inactive for a year.', 'attributes' => []],
        76  => ['uid' => 'B11', 'name' => 'New account (1 day)', 'description' => 'The account has been created today.', 'attributes' => []],
        77  => ['uid' => 'B12', 'name' => 'New account (1 week)', 'description' => 'The account has been created this week.', 'attributes' => []],
        95  => ['uid' => 'B13', 'name' => 'New account (1 month)', 'description' => 'The account has been created this month.', 'attributes' => []],
        80  => ['uid' => 'B14', 'name' => 'Aged account (>30 days)', 'description' => 'The account has been created over 30 days ago.', 'attributes' => []],
        93  => ['uid' => 'B15', 'name' => 'Aged account (>90 days)', 'description' => 'The account has been created over 90 days ago.', 'attributes' => []],
        94  => ['uid' => 'B16', 'name' => 'Aged account (>180 days)', 'description' => 'The account has been created over 180 days ago.', 'attributes' => []],
        42  => ['uid' => 'B17', 'name' => 'Single country', 'description' => 'IP addresses are located in a single country.', 'attributes' => ['ip']],
        40  => ['uid' => 'B18', 'name' => 'HEAD request', 'description' => 'HTTP request HEAD method is oftenly used by bots.', 'attributes' => []],
        24  => ['uid' => 'B19', 'name' => 'Night time requests', 'description' => 'User was active from midnight till 5 a. m.', 'attributes' => []],
        41  => ['uid' => 'B20', 'name' => 'Multiple countries in one session', 'description' => 'User\'s country was changed in less than 30 minutes.', 'attributes' => ['ip']],
        58  => ['uid' => 'B21', 'name' => 'Multiple devices in one session', 'description' => 'User\'s device was changed in less than 30 minutes.', 'attributes' => []],
        107 => ['uid' => 'B22', 'name' => 'Multiple IP addresses in one session', 'description' => 'User\'s IP address was changed in less than 30 minutes.', 'attributes' => []],
        // country
        46  => ['uid' => 'C01', 'name' => 'Nigeria IP address', 'description' => 'IP address located in Nigeria. This region is associated with a higher risk.', 'attributes' => ['ip']],
        47  => ['uid' => 'C02', 'name' => 'India IP address', 'description' => 'IP address located in India. This region is associated with a higher risk.', 'attributes' => ['ip']],
        49  => ['uid' => 'C03', 'name' => 'China IP address', 'description' => 'IP address located in China. This region is associated with a higher risk.', 'attributes' => ['ip']],
        50  => ['uid' => 'C04', 'name' => 'Brazil IP address', 'description' => 'IP address located in Brazil. This region is associated with a higher risk.', 'attributes' => ['ip']],
        51  => ['uid' => 'C05', 'name' => 'Pakistan IP address', 'description' => 'IP address located in Pakistan. This region is associated with a higher risk.', 'attributes' => ['ip']],
        52  => ['uid' => 'C06', 'name' => 'Indonesia IP address', 'description' => 'IP address located in Indonesia. This region is associated with a higher risk.', 'attributes' => ['ip']],
        53  => ['uid' => 'C07', 'name' => 'Venezuela IP address', 'description' => 'IP address located in Venezuela. This region is associated with a higher risk.', 'attributes' => ['ip']],
        54  => ['uid' => 'C08', 'name' => 'South Africa IP address', 'description' => 'IP address located in South Africa. This region is associated with a higher risk.', 'attributes' => ['ip']],
        55  => ['uid' => 'C09', 'name' => 'Philippines IP address', 'description' => 'IP address located in Philippines. This region is associated with a higher risk.', 'attributes' => ['ip']],
        56  => ['uid' => 'C10', 'name' => 'Romania IP address', 'description' => 'IP address located in Romania. This region is associated with a higher risk.', 'attributes' => ['ip']],
        21  => ['uid' => 'C11', 'name' => 'Russia IP address', 'description' => 'IP address located in Russia. This region is associated with a higher risk.', 'attributes' => ['ip']],
        36  => ['uid' => 'C12', 'name' => 'European IP address', 'description' => 'IP address located in Europe Union.', 'attributes' => ['ip']],
        32  => ['uid' => 'C13', 'name' => 'North America IP address', 'description' => 'IP address located in Canada or USA.', 'attributes' => ['ip']],
        33  => ['uid' => 'C14', 'name' => 'Australia IP address', 'description' => 'IP address located in Australia.', 'attributes' => ['ip']],
        34  => ['uid' => 'C15', 'name' => 'UAE IP address', 'description' => 'IP address located in United Arab Emirates.', 'attributes' => ['ip']],
        35  => ['uid' => 'C16', 'name' => 'Japan IP address', 'description' => 'IP address located in Japan.', 'attributes' => ['ip']],
        // device   TODO ('ua' attribute?)
        62  => ['uid' => 'D01', 'name' => 'Device is unknown', 'description' => 'User has manipulated the device information, so it is not recognized.', 'attributes' => []],
        63  => ['uid' => 'D02', 'name' => 'Device is Linux', 'description' => 'Linux OS is not used by avarage users, increased risk of crawler bot.', 'attributes' => []],
        65  => ['uid' => 'D03', 'name' => 'Device is bot', 'description' => 'The user may be using a device with a user agent that is identified as a bot.', 'attributes' => []],
        26  => ['uid' => 'D04', 'name' => 'Rare browser device', 'description' => 'User operates device with uncommon browser.', 'attributes' => []],
        27  => ['uid' => 'D05', 'name' => 'Rare OS device', 'description' => 'User operates device with uncommon OS.', 'attributes' => []],
        10  => ['uid' => 'D06', 'name' => 'Multiple devices per user', 'description' => 'User accesses the account using multiple devices. Account may be used by different people.', 'attributes' => []],
        25  => ['uid' => 'D07', 'name' => 'Several desktop devices', 'description' => 'User accesses the account using different OS desktop devices. Account may be shared between different people.', 'attributes' => []],
        81  => ['uid' => 'D08', 'name' => 'Two or more phone devices', 'description' => 'User accesses the account using numerous phone devices, which is not standard behaviour for regular users. Account may be shared between different people.', 'attributes' => []],
        78  => ['uid' => 'D09', 'name' => 'Old browser', 'description' => 'User accesses the account using an old versioned browser.', 'attributes' => []],
        39  => ['uid' => 'D10', 'name' => 'Potentially vulnerable User-Agent', 'description' => 'The user made a request with potentially vulnerable User-Agent.', 'attributes' => []],
        // email
        3   => ['uid' => 'E01', 'name' => 'Invalid email format', 'description' => 'Invalid email format. Should be \'username@domain.com\'.', 'attributes' => []],
        //6   => ['uid' => 'E02', 'name' => 'New domain, no profiles, no breaches', 'description' => 'Email belongs to recently created domain name, no online profiles found for email and it doesn\'t appear in data breaches. Increased risk due to lack of authenticity.'],
        6   => ['uid' => 'E02', 'name' => 'New domain and no breaches', 'description' => 'Email belongs to recently created domain name and it doesn\'t appear in data breaches. Increased risk due to lack of authenticity.', 'attributes' => ['email', 'domain']],
        97  => ['uid' => 'E03', 'name' => 'Suspicious words in email', 'description' => 'Email contains word parts that usually found in automatically generated mailboxes.', 'attributes' => []],
        98  => ['uid' => 'E04', 'name' => 'Numeric email name', 'description' => 'The email\'s username consists entirely of numbers, which is uncommon for typical email addresses.', 'attributes' => []],
        99  => ['uid' => 'E05', 'name' => 'Special characters in email', 'description' => 'The email address features an unusually high number of special characters, which is atypical for standard email addresses.', 'attributes' => []],
        100 => ['uid' => 'E06', 'name' => 'Consecutive digits in email', 'description' => 'The email address includes at least two consecutive digits, which is a characteristic sometimes associated with temporary or fake email accounts.', 'attributes' => []],
        101 => ['uid' => 'E07', 'name' => 'Long email username', 'description' => 'The email\'s username exceeds the average length, which could suggest it\'s a temporary email address.', 'attributes' => []],
        102 => ['uid' => 'E08', 'name' => 'Long domain name', 'description' => 'Email\'s domain name is too long. Long domain names are cheaply registered and rarely used for email addresses by regular users.', 'attributes' => []],
        103 => ['uid' => 'E09', 'name' => 'Free email provider', 'description' => 'Email belongs to free provider. These mailboxes are the easiest to create.', 'attributes' => ['domain']],
        82  => ['uid' => 'E10', 'name' => 'The website is unavailable', 'description' => 'Domain\'s website seems to be inactive, which could be a sign of fake mailbox.', 'attributes' => ['domain']],
        14  => ['uid' => 'E11', 'name' => 'Disposable email', 'description' => 'Disposable email addresses are temporary email addresses that users can create and use for a short period. They might use create fake accounts.', 'attributes' => ['email']],
        //7   => ['uid' => 'E12', 'name' => 'Free email, no profiles, no breaches', 'description' => 'Email belongs to free provider, no online profiles found for email and it doesn\'t appear in data breaches. It may be a sign of a throwaway mailbox.', 'attributes' => ['email']],
        7   => ['uid' => 'E12', 'name' => 'Free email and no breaches', 'description' => 'Email belongs to free provider and it doesn\'t appear in data breaches. It may be a sign of a throwaway mailbox.', 'attributes' => ['email']],
        15  => ['uid' => 'E13', 'name' => 'New domain', 'description' => 'Domain name was registered recently, which is rare for average users.', 'attributes' => ['domain']],
        83  => ['uid' => 'E14', 'name' => 'No MX record', 'description' => 'Email\'s domain name has no MX record, so domain is not able to have any mailboxes. It is a sign of fake mailbox.', 'attributes' => ['domain']],
        86  => ['uid' => 'E15', 'name' => 'No breaches for email', 'description' => 'The email was not involved in any data breaches, which could suggest it\'s a newly created or less frequently used mailbox.', 'attributes' => ['email']],
        8   => ['uid' => 'E16', 'name' => 'Domain appears in spam lists', 'description' => 'Email appears in spam lists, so the user may have spammed before.', 'attributes' => ['domain']],
        5   => ['uid' => 'E17', 'name' => 'Free email and spam', 'description' => 'Email appears in spam lists and registered by free provider. Increased risk of spamming.', 'attributes' => ['email', 'domain']],
        //13  => ['uid' => 'E18', 'name' => 'No online profiles', 'description' => 'No online profiles were found associated with this email, suggesting it could be newly created and potentially a temporary mailbox.', 'attributes' => ['email']],
        9   => ['uid' => 'E19', 'name' => 'Multiple emails changed', 'description' => 'User has changed their email.', 'attributes' => []],
        29  => ['uid' => 'E20', 'name' => 'Established domain (> 3 year old)', 'description' => 'Email belongs to long-established domain name registered at least 3 years ago.', 'attributes' => ['domain']],
        30  => ['uid' => 'E21', 'name' => 'No vowels in email', 'description' => 'Email username does not contain any vowels.', 'attributes' => []],
        31  => ['uid' => 'E22', 'name' => 'No consonants in email', 'description' => 'Email username does not contain any consonants.', 'attributes' => []],
        73  => ['uid' => 'E23', 'name' => 'Educational domain (.edu)', 'description' => 'Email belongs to educational domain.', 'attributes' => []],
        74  => ['uid' => 'E24', 'name' => 'Government domain (.gov)', 'description' => 'Email belongs to government domain.', 'attributes' => []],
        75  => ['uid' => 'E25', 'name' => 'Military domain (.mil)', 'description' => 'Email belongs to military domain.', 'attributes' => []],
        79  => ['uid' => 'E26', 'name' => 'iCloud mailbox', 'description' => 'Email belongs to Apple domains icloud.com, me.com or mac.com.', 'attributes' => []],
        //44  => ['uid' => 'E27', 'name' => 'Profiles and breaches', 'description' => 'Several online profiles found for this email and it appears in data breaches.', 'attributes' => ['email']],
        44  => ['uid' => 'E27', 'name' => 'Breaches', 'description' => 'Email appears in data breaches.', 'attributes' => ['email']],
        45  => ['uid' => 'E28', 'name' => 'No digits in email', 'description' => 'The email address does not include digits.', 'attributes' => []],
        37  => ['uid' => 'E29', 'name' => 'Old breach (>3 years)', 'description' => 'The earliest data breach associated with the email appeared more than 3 years ago. Can be used as sign of aged email.', 'attributes' => ['email']],
        38  => ['uid' => 'E30', 'name' => 'Domain with average rank', 'description' => 'Email domain has tranco rank between 100.000 and 4.000.000', 'attributes' => ['domain']],
        // IP
        1   => ['uid' => 'I01', 'name' => 'IP belongs to TOR', 'description' => 'IP address is assigned to The Onion Router network. Very few people use TOR, mainly used for anonymization and accessing censored resources.', 'attributes' => ['ip']],
        60  => ['uid' => 'I02', 'name' => 'IP hosting domain', 'description' => 'Higher risk of crawler bot. Such IP addresses are used only for hosting and are not provided to regular users by ISP.', 'attributes' => ['ip']],
        57  => ['uid' => 'I03', 'name' => 'IP appears in spam list', 'description' => 'User may have exhibited unwanted activity before at other web services.', 'attributes' => ['ip']],
        64  => ['uid' => 'I04', 'name' => 'Shared IP', 'description' => 'Multiple users detected on the same IP address. High risk of multi-accounting.', 'attributes' => []],
        59  => ['uid' => 'I05', 'name' => 'IP belongs to commercial VPN', 'description' => 'User tries to hide their real location or bypass regional blocking.', 'attributes' => ['ip']],
        2   => ['uid' => 'I06', 'name' => 'IP belongs to datacenter', 'description' => 'The user is utilizing an ISP datacenter, which highly suggests the use of a VPN, script, or privacy software.', 'attributes' => ['ip']],
        61  => ['uid' => 'I07', 'name' => 'IP belongs to Apple Relay', 'description' => 'IP address belongs to iCloud Private Relay, part of an iCloud+ subscription.', 'attributes' => ['ip']],
        23  => ['uid' => 'I08', 'name' => 'IP belongs to Starlink', 'description' => 'IP address belongs to SpaceX satellite network.', 'attributes' => ['ip']],
        12  => ['uid' => 'I09', 'name' => 'Numerous IPs', 'description' => 'User accesses the account with numerous IP addresses. This behavior occurs in less than one percent of desktop users.', 'attributes' => []],
        28  => ['uid' => 'I10', 'name' => 'Only residential IPs', 'description' => 'User uses only residential IP addresses.', 'attributes' => ['ip']],
        43  => ['uid' => 'I11', 'name' => 'Single network', 'description' => 'IP addresses belong to one network.', 'attributes' => ['ip']],
        108 => ['uid' => 'I12', 'name' => 'IP belongs to LAN', 'description' => 'IP address belongs to local access network.', 'attributes' => []],
        // reuse
        104 => ['uid' => 'R01', 'name' => 'IP in blacklist', 'description' => 'This IP address appears in the blacklist.', 'attributes' => []],
        105 => ['uid' => 'R02', 'name' => 'Email in blacklist', 'description' => 'This email address appears in the blacklist.', 'attributes' => []],
        106 => ['uid' => 'R03', 'name' => 'Phone in blacklist', 'description' => ' This phone number appears in the blacklist.', 'attributes' => []],
        //  TODO: return alert_list back in next release
        // 90 => ['uid' => 'R04', 'name' => 'IP in global alert list', 'description' => 'IP address hash was found in system alert list. Other participants in the system infrastructure considered this IP address harmful.', 'attributes' => []],
        // 91 => ['uid' => 'R05', 'name' => 'Email in global alert list', 'description' => 'Mailbox hash was found in system alert list. Other participants in the system infrastructure considered this email address harmful.', 'attributes' => []],
        // 92 => ['uid' => 'R06', 'name' => 'Phone in global alert list', 'description' => 'Phone number hash was found in system alert list. Other participants in the system infrastructure considered this phone number harmful.', 'attributes' => []],
        // phone
        85  => ['uid' => 'P01', 'name' => 'Invalid phone format', 'description' => 'User provided incorrect phone number.', 'attributes' => ['phone']],
        84  => ['uid' => 'P02', 'name' => 'Phone country mismatch', 'description' => 'Phone number country is not among the countries from which user has logged in. May be a sign of invalid phone number.', 'attributes' => ['phone']],
        96  => ['uid' => 'P03', 'name' => 'Shared phone number', 'description' => 'User provided a phone number shared with another user.', 'attributes' => []],
        48  => ['uid' => 'P04', 'name' => 'Valid phone', 'description' => 'User provided correct phone number.', 'attributes' => ['phone']],
    ];

    private const rulesWeight = [
        -20 =>  'positive',
        10 =>   'medium',
        20 =>   'high',
        70 =>   'extreme',
        0 =>    'none',
    ];

    private const rulesTypes = [
        'A' => 'Account takeover',
        'B' => 'Behaviour',
        'C' => 'Country',
        'D' => 'Device',
        'E' => 'Email',
        'I' => 'IP',
        'R' => 'Reuse',
        'P' => 'Phone',
    ];

    public static function getRuleClass(?int $value): string {
        return self::rulesWeight[$value ?? 0] ?? 'none';
    }

    public static function getRuleTypeByUid(string $uid): string {
        return self::rulesTypes[$uid[0]] ?? $uid[0];
    }

    public static function getUserScoreClass(?int $score): array {
        $cls = 'empty';
        if ($score === null) {
            return ['&minus;', $cls];
        }

        if ($score >= \Utils\Constants::USER_LOW_SCORE_INF && $score < \Utils\Constants::USER_LOW_SCORE_SUP) {
            $cls = 'low';
        }

        if ($score >= \Utils\Constants::USER_MEDIUM_SCORE_INF && $score < \Utils\Constants::USER_MEDIUM_SCORE_SUP) {
            $cls = 'medium';
        }

        if ($score >= \Utils\Constants::USER_HIGH_SCORE_INF) {
            $cls = 'high';
        }

        return [$score, $cls];
    }

    //  TODO: return alert_list back in next release
    //  in this way it should not return lines with R04 R05 R06
    public static function ruleInfoById(array $data): array {
        $results = [];

        foreach ($data as $row) {
            $ruleId = $row['id'];
            if (isset(self::rules[$ruleId])) {
                $results[] = array_merge($row, self::rules[$ruleId]);
            }
        }

        return $results;
    }

    // returns rules ids as keys of associative array
    public static function activeRulesIds(array $data): array {
        $results = [];

        foreach ($data as $row) {
            $ruleId = $row['id'];
            if (isset(self::rules[$ruleId])) {
                $results[$ruleId] = self::rules[$ruleId];
            }
        }

        return $results;
    }

    public static function filterRulesByAttributes(array $data, array $skipAttributes): array {
        $results = [];

        foreach ($data as $row) {
            $ruleId = $row['id'];
            if (isset(self::rules[$ruleId]) && !count(array_intersect(self::rules[$ruleId]['attributes'], $skipAttributes))) {
                $results[] = $row;
            }
        }

        return $results;
    }
}
