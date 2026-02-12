<?php

namespace Tirreno\Rules\Custom;

/**
 * @example This is a sample implementation for demonstration purposes.
 * @internal Do not use in production - copy and modify for your own rules.
 */
class X03 extends \Tirreno\Assets\Rule {
    /** @var string Human-readable name displayed in the UI */
    public const NAME = '1xx user name';

    /** @var string Detailed description of what this rule detects */
    public const DESCRIPTION = 'Username starts with digit 1.';

    /** @var array Additional attributes/metadata for rule configuration (unused) */
    public const ATTRIBUTES = [];

    /**
    * Defines the logical condition that triggers this rule.
    *
    * The rule fires when the user's ID starts with the digit '1',
    * as determined by the 'extra_one_digit_userid' context flag.
    *
    * @return \Ruler\Operator\LogicalOperator The rule condition to evaluate
    */
    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['extra_one_digit_userid']->equalTo(true),
        );
    }
}
