<?php

/**
 * Copyright 2015-2019 info@neomerx.com
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

namespace Sample\Validation;

use DateTime;
use DateTimeInterface;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\Rules\ExecuteRule;

/**
 * @package Sample
 */
class IsDeliveryDateRule extends ExecuteRule
{
    /** @var string Message Template */
    public const MESSAGE_TEMPLATE = 'The value should be a valid delivery date.';

    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        $from = new DateTime('tomorrow');
        $to = new DateTime('+5 days');

        $isValidDeliveryDate = $value instanceof DateTimeInterface === true && $value >= $from && $value <= $to;

        return $isValidDeliveryDate === true ?
            static::createSuccessReply($value) :
            static::createErrorReply(
                $context,
                $value,
                Errors::IS_DELIVERY_DATE,
                static::MESSAGE_TEMPLATE,
                [$from->getTimestamp(), $to->getTimestamp()]
            );
    }
}
