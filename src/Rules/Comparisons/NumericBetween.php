<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Validation\Rules\Comparisons;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;

use function assert;
use function is_numeric;

/**
 * @package Whoa\Validation
 */
final class NumericBetween extends BaseTwoValueComparison
{
    /**
     * @param mixed $lowerValue
     * @param mixed $upperValue
     */
    public function __construct($lowerValue, $upperValue)
    {
        assert(is_numeric($lowerValue) === true && is_numeric($upperValue) === true && $lowerValue <= $upperValue);
        parent::__construct(
            $lowerValue,
            $upperValue,
            ErrorCodes::NUMERIC_BETWEEN,
            Messages::NUMERIC_BETWEEN,
            [$lowerValue, $upperValue]
        );
    }

    /**
     * @inheritdoc
     */
    public static function compare($value, ContextInterface $context): bool
    {
        assert(is_numeric($value) === true);
        return is_numeric($value) === true &&
            NumericBetween::readLowerValue($context) <= $value && $value <= NumericBetween::readUpperValue($context);
    }
}
