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

namespace Whoa\Validation\Rules\Generic;

use Whoa\Validation\Contracts\Errors\ErrorCodes;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\I18n\Messages;
use Whoa\Validation\Rules\ExecuteRule;

use function filter_var;

/**
 * @package Whoa\Validation
 */
final class Filter extends ExecuteRule
{
    /** @var int Property key */
    private const PROPERTY_FILTER_ID = self::PROPERTY_LAST + 1;

    /** @var int Property key */
    private const PROPERTY_FILTER_OPTIONS = self::PROPERTY_FILTER_ID + 1;

    /** @var int Property key */
    private const PROPERTY_FILTER_ERROR_CODE = self::PROPERTY_FILTER_OPTIONS + 1;

    /** @var int Property key */
    private const PROPERTY_MESSAGE_TEMPLATE = self::PROPERTY_FILTER_ERROR_CODE + 1;

    /**
     * For filter ID and options see @link http://php.net/manual/en/filter.filters.php
     *
     * @param int $filterId
     * @param mixed $options
     * @param int $errorCode
     * @param string $messageTemplate
     */
    public function __construct(
        int $filterId,
        $options = null,
        int $errorCode = ErrorCodes::INVALID_VALUE,
        string $messageTemplate = Messages::INVALID_VALUE
    ) {
        parent::__construct([
            Filter::PROPERTY_FILTER_ID => $filterId,
            Filter::PROPERTY_FILTER_OPTIONS => $options,
            Filter::PROPERTY_FILTER_ERROR_CODE => $errorCode,
            Filter::PROPERTY_MESSAGE_TEMPLATE => $messageTemplate,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function execute($value, ContextInterface $context, $extras = null): array
    {
        $properties = $context->getProperties();
        $filterId = $properties->getProperty(Filter::PROPERTY_FILTER_ID);
        $filterOptions = $properties->getProperty(Filter::PROPERTY_FILTER_OPTIONS);
        $errorCode = $properties->getProperty(Filter::PROPERTY_FILTER_ERROR_CODE);
        $messageTemplate = $properties->getProperty(Filter::PROPERTY_MESSAGE_TEMPLATE);

        $output = filter_var($value, $filterId, $filterOptions);

        return $output !== false ?
            Filter::createSuccessReply($output) :
            Filter::createErrorReply($context, $value, $errorCode, $messageTemplate, [$filterId, $filterOptions]);
    }
}
