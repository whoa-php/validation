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

use Whoa\Validation\Contracts\Captures\CaptureAggregatorInterface;
use Whoa\Validation\Contracts\Errors\ErrorAggregatorInterface;
use Whoa\Validation\Contracts\Execution\BlockSerializerInterface;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\Contracts\Execution\ContextStorageInterface;
use Whoa\Validation\Errors\Error;
use Whoa\Validation\Rules\BaseRule;

use function array_key_exists;
use function assert;
use function call_user_func;
use function is_array;
use function is_bool;
use function is_callable;
use function is_int;
use function is_iterable;

/**
 * @package Whoa\Validation
 */
final class BlockInterpreter
{
    /**
     * @param mixed $input
     * @param array $serializedBlocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param ErrorAggregatorInterface $errors
     * @return bool
     */
    public static function execute(
        $input,
        array $serializedBlocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        ErrorAggregatorInterface $errors
    ): bool {
        $blockIndex = BlockSerializer::FIRST_BLOCK_INDEX;

        $blocks = BlockInterpreter::getBlocks($serializedBlocks);
        $startsOk = BlockInterpreter::executeStarts(
            BlockInterpreter::getBlocksWithStart($serializedBlocks),
            $blocks,
            $context,
            $errors
        );
        $blockOk = BlockInterpreter::executeBlock($input, $blockIndex, $blocks, $context, $captures, $errors);
        $endsOk = BlockInterpreter::executeEnds(
            BlockInterpreter::getBlocksWithEnd($serializedBlocks),
            $blocks,
            $context,
            $errors
        );

        return $startsOk && $blockOk && $endsOk;
    }

    /**
     * @param iterable|int[] $indexes
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param ErrorAggregatorInterface $errors
     * @return bool
     */
    public static function executeStarts(
        iterable $indexes,
        array $blocks,
        ContextStorageInterface $context,
        ErrorAggregatorInterface $errors
    ): bool {
        $allOk = true;

        foreach ($indexes as $index) {
            $context->setCurrentBlockId($index);
            $block = $blocks[$index];
            $errorsInfo = BlockInterpreter::executeProcedureStart($block, $context);
            if (empty($errorsInfo) === false) {
                BlockInterpreter::addBlockErrors($errorsInfo, $context, $errors);
                $allOk = false;
            }
        }

        return $allOk;
    }

    /**
     * @param iterable|int[] $indexes
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param ErrorAggregatorInterface $errors
     * @return bool
     */
    public static function executeEnds(
        iterable $indexes,
        array $blocks,
        ContextStorageInterface $context,
        ErrorAggregatorInterface $errors
    ): bool {
        $allOk = true;

        foreach ($indexes as $index) {
            $context->setCurrentBlockId($index);
            $block = $blocks[$index];
            $errorsInfo = BlockInterpreter::executeProcedureEnd($block, $context);
            if (empty($errorsInfo) === false) {
                BlockInterpreter::addBlockErrors($errorsInfo, $context, $errors);
                $allOk = false;
            }
        }

        return $allOk;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param ErrorAggregatorInterface $errors
     * @param null $extras
     * @return bool
     */
    public static function executeBlock(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        ErrorAggregatorInterface $errors,
        $extras = null
    ): bool {
        $result = BlockInterpreter::executeBlockImpl(
            $input,
            $blockIndex,
            $blocks,
            $context,
            $captures,
            $extras
        );
        if (BlockReplies::isResultSuccessful($result) === false) {
            $errorsInfo = BlockReplies::extractResultErrorsInfo($result);
            BlockInterpreter::addBlockErrors($errorsInfo, $context, $errors);

            return false;
        }

        return true;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param null $extras
     * @return array
     */
    private static function executeBlockImpl(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        $extras = null
    ): array {
        assert(array_key_exists($blockIndex, $blocks));

        $blockType = BlockInterpreter::getBlockType($blocks[$blockIndex]);
        $context->setCurrentBlockId($blockIndex);
        switch ($blockType) {
            case BlockSerializerInterface::TYPE__PROCEDURE:
                $result = BlockInterpreter::executeProcedureBlock(
                    $input,
                    $blockIndex,
                    $blocks,
                    $context,
                    $captures,
                    $extras
                );
                break;
            case BlockSerializerInterface::TYPE__IF_EXPRESSION:
                $result = BlockInterpreter::executeIfBlock($input, $blockIndex, $blocks, $context, $captures, $extras);
                break;
            case BlockSerializerInterface::TYPE__AND_EXPRESSION:
                $result = BlockInterpreter::executeAndBlock($input, $blockIndex, $blocks, $context, $captures, $extras);
                break;
            case BlockSerializerInterface::TYPE__OR_EXPRESSION:
            default:
                assert($blockType === BlockSerializerInterface::TYPE__OR_EXPRESSION);
                $result = BlockInterpreter::executeOrBlock($input, $blockIndex, $blocks, $context, $captures, $extras);
                break;
        }

        return $result;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param null $extras
     * @return array
     */
    private static function executeProcedureBlock(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        $extras = null
    ): array {
        $block = $blocks[$blockIndex];
        assert(BlockInterpreter::getBlockType($block) === BlockSerializerInterface::TYPE__PROCEDURE);

        $procedure = $block[BlockSerializerInterface::PROCEDURE_EXECUTE_CALLABLE];
        assert(is_callable($procedure));
        $result = call_user_func($procedure, $input, $context, $extras);

        BlockInterpreter::captureSuccessfulBlockResultIfEnabled($result, $block, $captures);

        return $result;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param null $extras
     * @return array
     */
    private static function executeIfBlock(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        $extras = null
    ): array {
        $block = $blocks[$blockIndex];
        assert(BlockInterpreter::getBlockType($block) === BlockSerializerInterface::TYPE__IF_EXPRESSION);

        $conditionCallable = $block[BlockSerializerInterface::IF_EXPRESSION_CONDITION_CALLABLE];
        assert(is_callable($conditionCallable));
        $conditionResult = call_user_func($conditionCallable, $input, $context, $extras);
        assert(is_bool($conditionResult));

        $index = $block[$conditionResult === true ?
            BlockSerializerInterface::IF_EXPRESSION_ON_TRUE_BLOCK : BlockSerializerInterface::IF_EXPRESSION_ON_FALSE_BLOCK];

        $result = BlockInterpreter::executeBlockImpl($input, $index, $blocks, $context, $captures, $extras);

        BlockInterpreter::captureSuccessfulBlockResultIfEnabled($result, $block, $captures);

        return $result;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param null $extras
     * @return array
     */
    private static function executeAndBlock(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        $extras = null
    ): array {
        $block = $blocks[$blockIndex];
        assert(BlockInterpreter::getBlockType($block) === BlockSerializerInterface::TYPE__AND_EXPRESSION);

        $primaryIndex = $block[BlockSerializerInterface::AND_EXPRESSION_PRIMARY];
        $result = BlockInterpreter::executeBlockImpl($input, $primaryIndex, $blocks, $context, $captures, $extras);
        if (BlockReplies::isResultSuccessful($result) === true) {
            $nextInput = BlockReplies::extractResultOutput($result);
            $secondaryIndex = $block[BlockSerializerInterface::AND_EXPRESSION_SECONDARY];
            $result = BlockInterpreter::executeBlockImpl(
                $nextInput,
                $secondaryIndex,
                $blocks,
                $context,
                $captures,
                $extras
            );
        }

        BlockInterpreter::captureSuccessfulBlockResultIfEnabled($result, $block, $captures);

        return $result;
    }

    /**
     * @param mixed $input
     * @param int $blockIndex
     * @param array $blocks
     * @param ContextStorageInterface $context
     * @param CaptureAggregatorInterface $captures
     * @param null $extras
     * @return array
     */
    private static function executeOrBlock(
        $input,
        int $blockIndex,
        array $blocks,
        ContextStorageInterface $context,
        CaptureAggregatorInterface $captures,
        $extras = null
    ): array {
        $block = $blocks[$blockIndex];
        assert(BlockInterpreter::getBlockType($block) === BlockSerializer::TYPE__OR_EXPRESSION);

        $primaryIndex = $block[BlockSerializerInterface::OR_EXPRESSION_PRIMARY];
        $resultFromPrimary = BlockInterpreter::executeBlockImpl(
            $input,
            $primaryIndex,
            $blocks,
            $context,
            $captures,
            $extras
        );
        if (BlockReplies::isResultSuccessful($resultFromPrimary) === true) {
            $result = $resultFromPrimary;
        } else {
            $secondaryIndex = $block[BlockSerializerInterface::OR_EXPRESSION_SECONDARY];
            $result = BlockInterpreter::executeBlockImpl(
                $input,
                $secondaryIndex,
                $blocks,
                $context,
                $captures,
                $extras
            );
        }

        BlockInterpreter::captureSuccessfulBlockResultIfEnabled($result, $block, $captures);

        return $result;
    }

    /**
     * @param array $serializedBlocks
     * @return array
     */
    private static function getBlocks(array $serializedBlocks): array
    {
        $blocks = BlockSerializer::unserializeBlocks($serializedBlocks);
        assert(BlockInterpreter::debugCheckLooksLikeBlocksArray($blocks));

        return $blocks;
    }

    /**
     * @param array $serializedBlocks
     * @return array
     */
    private static function getBlocksWithStart(array $serializedBlocks): array
    {
        $blocksWithStart = BlockSerializer::unserializeBlocksWithStart($serializedBlocks);

        // check result contain only block indexes and the blocks are procedures
        assert(
            is_array($blocks = BlockInterpreter::getBlocks($serializedBlocks)) &&
            BlockInterpreter::debugCheckBlocksExist(
                $blocksWithStart,
                $blocks,
                BlockSerializerInterface::TYPE__PROCEDURE
            )
        );

        return $blocksWithStart;
    }

    /**
     * @param array $serializedBlocks
     * @return array
     */
    private static function getBlocksWithEnd(array $serializedBlocks): array
    {
        $blocksWithEnd = BlockSerializer::unserializeBlocksWithEnd($serializedBlocks);

        // check result contain only block indexes and the blocks are procedures
        assert(
            is_array($blocks = BlockInterpreter::getBlocks($serializedBlocks)) &&
            BlockInterpreter::debugCheckBlocksExist($blocksWithEnd, $blocks, BlockSerializerInterface::TYPE__PROCEDURE)
        );

        return $blocksWithEnd;
    }

    /**
     * @param array $block
     * @return int
     */
    private static function getBlockType(array $block): int
    {
        assert(BlockInterpreter::debugHasKnownBlockType($block));

        return $block[BlockSerializerInterface::TYPE];
    }

    /**
     * @param array $result
     * @param array $block
     * @param CaptureAggregatorInterface $captures
     * @return void
     */
    private static function captureSuccessfulBlockResultIfEnabled(
        array $result,
        array $block,
        CaptureAggregatorInterface $captures
    ): void {
        if (BlockReplies::isResultSuccessful($result) === true) {
            $isCaptureEnabled = $block[BlockSerializerInterface::PROPERTIES][BaseRule::PROPERTY_IS_CAPTURE_ENABLED] ?? false;
            if ($isCaptureEnabled === true) {
                $name = $block[BlockSerializerInterface::PROPERTIES][BaseRule::PROPERTY_NAME];
                $value = BlockReplies::extractResultOutput($result);
                $captures->remember($name, $value);
            }
        }
    }

    /**
     * @param array $procedureBlock
     * @param ContextInterface $context
     * @return array
     */
    private static function executeProcedureStart(array $procedureBlock, ContextInterface $context): array
    {
        assert(BlockInterpreter::getBlockType($procedureBlock) === BlockSerializerInterface::TYPE__PROCEDURE);
        $callable = $procedureBlock[BlockSerializerInterface::PROCEDURE_START_CALLABLE];
        assert(is_callable($callable) === true);
        $errors = call_user_func($callable, $context);
        assert(is_array($errors));

        return $errors;
    }

    /**
     * @param array $procedureBlock
     * @param ContextInterface $context
     * @return array
     */
    private static function executeProcedureEnd(array $procedureBlock, ContextInterface $context): iterable
    {
        assert(BlockInterpreter::getBlockType($procedureBlock) === BlockSerializerInterface::TYPE__PROCEDURE);
        $callable = $procedureBlock[BlockSerializerInterface::PROCEDURE_END_CALLABLE];
        assert(is_callable($callable) === true);
        $errors = call_user_func($callable, $context);
        assert(is_iterable($errors));

        return $errors;
    }

    /**
     * @param iterable $errorsInfo
     * @param ContextStorageInterface $context
     * @param ErrorAggregatorInterface $errors
     * @return void
     */
    private static function addBlockErrors(
        iterable $errorsInfo,
        ContextStorageInterface $context,
        ErrorAggregatorInterface $errors
    ): void {
        foreach ($errorsInfo as $errorInfo) {
            $index = $errorInfo[BlockReplies::ERROR_INFO_BLOCK_INDEX];
            $value = $errorInfo[BlockReplies::ERROR_INFO_VALUE];
            $errorCode = $errorInfo[BlockReplies::ERROR_INFO_CODE];
            $messageTemplate = $errorInfo[BlockReplies::ERROR_INFO_MESSAGE_TEMPLATE];
            $messageParams = $errorInfo[BlockReplies::ERROR_INFO_MESSAGE_PARAMETERS];

            $name = $context->setCurrentBlockId($index)->getProperties()->getProperty(BaseRule::PROPERTY_NAME);

            $errors->add(new Error($name, $value, $errorCode, $messageTemplate, $messageParams));
        }
    }

    /**
     * @param iterable $blocks
     * @return bool
     */
    private static function debugCheckLooksLikeBlocksArray(iterable $blocks): bool
    {
        $result = true;

        foreach ($blocks as $index => $block) {
            $result = $result &&
                is_int($index) === true &&
                is_array($block) === true &&
                BlockInterpreter::debugHasKnownBlockType($block) === true;
        }

        return $result;
    }

    /**
     * @param iterable|int[] $blockIndexes
     * @param array $blockList
     * @param int $blockType
     * @return bool
     */
    private static function debugCheckBlocksExist(iterable $blockIndexes, array $blockList, int $blockType): bool
    {
        $result = true;

        foreach ($blockIndexes as $index) {
            $result = $result &&
                array_key_exists($index, $blockList) === true &&
                BlockInterpreter::getBlockType($blockList[$index]) === $blockType;
        }

        return $result;
    }

    /**
     * @param array $block
     * @return bool
     */
    private static function debugHasKnownBlockType(array $block): bool
    {
        $result = false;

        if (array_key_exists(BlockSerializerInterface::TYPE, $block) === true) {
            $type = $block[BlockSerializerInterface::TYPE];

            $result =
                $type === BlockSerializerInterface::TYPE__PROCEDURE ||
                $type === BlockSerializerInterface::TYPE__AND_EXPRESSION ||
                $type === BlockSerializerInterface::TYPE__OR_EXPRESSION ||
                $type === BlockSerializerInterface::TYPE__IF_EXPRESSION;
        }

        return $result;
    }
}
