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

namespace Whoa\Validation\Rules\Converters;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\ExecuteRule;

use function is_bool;
use function is_string;
use function strtolower;

/**
 * @package Whoa\Validation
 */
final class StringToBool extends ExecuteRule
{
    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        if (is_string($value) === true) {
            $lcValue = strtolower($value);
            if ($lcValue === 'true' || $lcValue === '1' || $lcValue === 'on' || $lcValue === 'yes') {
                $reply = StringToBool::createSuccessReply(true);
            } elseif ($lcValue === 'false' || $lcValue === '0' || $lcValue === 'off' || $lcValue === 'no') {
                $reply = StringToBool::createSuccessReply(false);
            } else {
                $reply = StringToBool::createErrorReply($context, $value, ErrorCodes::IS_BOOL, Messages::IS_BOOL, []);
            }
        } elseif (is_bool($value) === true) {
            $reply = StringToBool::createSuccessReply($value);
        } else {
            $reply = StringToBool::createErrorReply($context, $value, ErrorCodes::IS_BOOL, Messages::IS_BOOL, []);
        }

        return $reply;
    }
}
