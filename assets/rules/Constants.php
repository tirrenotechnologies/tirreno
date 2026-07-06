<?php

declare(strict_types=1);

namespace Tirreno\Rules;

class Constants extends \Tirreno\Assets\Constants {
    public const NIGHT_RANGE_SECONDS_START  = 0;        // midnight
    public const NIGHT_RANGE_SECONDS_END    = 18000;    // 5 AM

    public const RULE_EVENT_CONTEXT_LIMIT               = 25;
    public const RULE_CHECK_USERS_PASSED_TO_CLIENT      = 25;
    public const RULE_USERS_BATCH_SIZE                  = 3500;
    public const RULE_EMAIL_MAXIMUM_LOCAL_PART_LENGTH   = 17;
    public const RULE_EMAIL_MAXIMUM_DOMAIN_LENGTH       = 22;
    public const RULE_MAXIMUM_NUMBER_OF_404_CODES       = 4;
    public const RULE_MAXIMUM_NUMBER_OF_500_CODES       = 4;
    public const RULE_MAXIMUM_NUMBER_OF_LOGIN_ATTEMPTS  = 3;
    public const RULE_LOGIN_ATTEMPTS_WINDOW             = 8;
    public const RULE_NEW_DEVICE_MAX_AGE_IN_SECONDS     = 60 * 60 * 3;
}
