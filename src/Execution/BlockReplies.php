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

namespace Whoa\Validation\Execution;

use Whoa\Validation\Contracts\Execution\ContextInterface;

use function assert;
use function count;
use function is_array;

/**
 * @package Whoa\Validation
 */
final class BlockReplies
{
    /**
     * Rule reply key.
     */
    private const REPLY_SUCCESS_VALUE = 0;

    /**
     * Rule reply key.
     */
    private const REPLY_ERRORS_INFO = self::REPLY_SUCCESS_VALUE + 1;

    /**
     * Error info key.
     */
    public const ERROR_INFO_BLOCK_INDEX = 0;

    /**
     * Error info key.
     */
    public const ERROR_INFO_VALUE = self::ERROR_INFO_BLOCK_INDEX + 1;

    /**
     * Error info key.
     */
    public const ERROR_INFO_CODE = self::ERROR_INFO_VALUE + 1;

    /**
     * Error info key.
     */
    public const ERROR_INFO_MESSAGE_TEMPLATE = self::ERROR_INFO_CODE + 1;

    /**
     * Error info key.
     */
    public const ERROR_INFO_MESSAGE_PARAMETERS = self::ERROR_INFO_MESSAGE_TEMPLATE + 1;

    /**
     * @param mixed $result
     * @return array
     */
    public static function createSuccessReply($result): array
    {
        return [
            BlockReplies::REPLY_SUCCESS_VALUE => $result,
            BlockReplies::REPLY_ERRORS_INFO => null,
        ];
    }

    /**
     * @return array
     */
    public static function createStartSuccessReply(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function createEndSuccessReply(): array
    {
        return [];
    }

    /**
     * @param ContextInterface $context
     * @param mixed $errorValue
     * @param int $errorCode
     * @param string $messageTemplate
     * @param array $messageParams
     * @return array
     */
    public static function createErrorReply(
        ContextInterface $context,
        $errorValue,
        int $errorCode,
        string $messageTemplate,
        array $messageParams
    ): array {
        return [
            BlockReplies::REPLY_SUCCESS_VALUE => null,
            BlockReplies::REPLY_ERRORS_INFO => [
                BlockReplies::createErrorInfoEntry(
                    $context->getCurrentBlockId(),
                    $errorValue,
                    $errorCode,
                    $messageTemplate,
                    $messageParams
                ),
            ],
        ];
    }

    /**
     * @param ContextInterface $context
     * @param int $errorCode
     * @param string $messageTemplate
     * @param array $messageParams
     * @return array
     */
    public static function createStartErrorReply(
        ContextInterface $context,
        int $errorCode,
        string $messageTemplate,
        array $messageParams
    ): array {
        $value = null;

        return [
            BlockReplies::createErrorInfoEntry(
                $context->getCurrentBlockId(),
                $value,
                $errorCode,
                $messageTemplate,
                $messageParams
            ),
        ];
    }

    /**
     * @param ContextInterface $context
     * @param int $errorCode
     * @param string $messageTemplate
     * @param array $messageParams
     * @return array
     */
    public static function createEndErrorReply(
        ContextInterface $context,
        int $errorCode,
        string $messageTemplate,
        array $messageParams
    ): array {
        $value = null;

        return [
            BlockReplies::createErrorInfoEntry(
                $context->getCurrentBlockId(),
                $value,
                $errorCode,
                $messageTemplate,
                $messageParams
            ),
        ];
    }

    /**
     * @param int $blockId
     * @param mixed $value
     * @param int $code
     * @param string $messageTemplate
     * @param array $messageParams
     * @return array
     */
    protected static function createErrorInfoEntry(
        int $blockId,
        $value,
        int $code,
        string $messageTemplate,
        array $messageParams
    ): array {
        return [
            BlockReplies::ERROR_INFO_BLOCK_INDEX => $blockId,
            BlockReplies::ERROR_INFO_VALUE => $value,
            BlockReplies::ERROR_INFO_CODE => $code,
            BlockReplies::ERROR_INFO_MESSAGE_TEMPLATE => $messageTemplate,
            BlockReplies::ERROR_INFO_MESSAGE_PARAMETERS => $messageParams,
        ];
    }

    /**
     * @param array $result
     * @return bool
     */
    public static function isResultSuccessful(array $result): bool
    {
        assert(
            count($result) === 2 &&
            ($result[BlockReplies::REPLY_ERRORS_INFO] === null || is_array(
                    $result[BlockReplies::REPLY_ERRORS_INFO]
                ) === true)
        );

        // if error code is `null`
        return $result[BlockReplies::REPLY_ERRORS_INFO] === null;
    }

    /**
     * @param array $result
     * @return mixed
     */
    public static function extractResultOutput(array $result)
    {
        // extracting result only make sense when error is `null`.
        assert(BlockReplies::isResultSuccessful($result) === true && $result[BlockReplies::REPLY_ERRORS_INFO] === null);

        return $result[BlockReplies::REPLY_SUCCESS_VALUE];
    }

    /**
     * @param array $result
     * @return array
     */
    public static function extractResultErrorsInfo(array $result): array
    {
        assert(
            count($result) === 2 &&
            $result[BlockReplies::REPLY_SUCCESS_VALUE] === null &&
            is_array($result[BlockReplies::REPLY_ERRORS_INFO]) === true &&
            empty($result[BlockReplies::REPLY_ERRORS_INFO]) === false
        );

        return $result[BlockReplies::REPLY_ERRORS_INFO];
    }
}
